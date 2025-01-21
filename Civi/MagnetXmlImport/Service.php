<?php

namespace Civi\MagnetXmlImport;

use Civi;
use Civi\Api4\Contribution;
use Civi\RcBase\ApiWrapper\Create;
use Civi\RcBase\ApiWrapper\Get;
use Exception;

/**
 * Service class for the Magnet XML import.
 */
class Service
{
    /**
     * The path of the XML file.
     *
     * @var string
     */
    private string $filePath;

    /**
     * The configuration array.
     *
     * @var array
     */
    private array $config;

    /**
     * The stats array.
     *
     * @var array
     */
    private array $stats;

    /**
     * @param array $config
     * @param string $path
     */
    public function __construct(array $config, string $path)
    {
        $this->filePath = $path;
        $this->config = $config;
        $this->stats = [
            'all' => 0,
            'imported' => 0,
            'skipped' => 0,
            'duplication' => 0,
            'errors' => [],
        ];
    }

    /**
     * This function starts the xml parsing process.
     *
     * @return array the stats about the process.
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function process(): array
    {
        $xml = Civi::service('IOProcessor.XML')->decode(file_get_contents($this->filePath), false);
        $this->stats['all'] = count($xml->Tranzakcio);

        foreach ($xml->Tranzakcio as $transaction) {
            $contactData = [
                'contact_type' => 'Individual',
                'display_name' => (string)$transaction->Ellenpartner,
                $this->config['bankAccountNumberParameter'] => (string)$transaction->Ellenszamla,
            ];
            $contributionData = [
                'trxn_id' => (string)$transaction->Tranzakcioszam,
                'total_amount' => (float)$transaction->Osszeg,
                'currency' => (string)$transaction->Osszeg->attributes()['Devizanem'],
                'receive_date' => str_replace('.', '', (string)$transaction->Esedekessegnap), // we need 20150131
                'source' => $this->config['source'],
                'financial_type_id' => $this->config['financialTypeId'],
                'payment_instrument_id' => $this->config['paymentInstrumentId'],
                'contribution_status_id:name' => 'Completed',
            ];

            // When the skip negative transaction is set, and the amount is not a positive number, we can continue with the next item.
            if ($this->config['onlyIncome'] && $contributionData['total_amount'] < 0.01) {
                $this->stats['skipped']++;
                continue;
            }

            // Duplicate detection. skip the import when the transaction id already exists.
            if ($this->duplicatedTransaction($contributionData['trxn_id'])) {
                $this->stats['duplication']++;
                continue;
            }

            // First get the contact id that is connected to the bank account number.
            // If it fails, log to file and continue with the next transaction.
            try {
                $contactId = $this->contact($contactData);
            } catch (Exception $e) {
                $this->raiseError('Failed to get CRM contact to the transaction: '.$e->getMessage());
                continue;
            }

            // Create the contribution.
            $contributionData['contact_id'] = $contactId;
            if ($this->contribution($contributionData)) {
                $this->stats['imported']++;
            }
        }

        return $this->stats;
    }

    /**
     * This function returns the contact id of the contributor contact.
     * First it tries to find it base on the bank account number parameter.
     * If not found it formats the parameter and tries to find it again.
     * If not found, it creates a new contact. It uses the v3 API as it
     * allows the search by custom_1 variable name format.
     *
     * @param array $contactData
     *
     * @return int $contactId
     * @throws \CiviCRM_API3_Exception
     */
    private function contact(array $contactData): int
    {
        $contact = Get::entitySingle('Contact', [
            'select' => ['id'],
            'where' => [
                [$this->config['bankAccountNumberParameter'], '=', $contactData[$this->config['bankAccountNumberParameter']]],
                ['is_deleted', '=', false],
            ],
            'limit' => 1,
        ]);
        if (!is_null($contact)) {
            return $contact['id'];
        }

        // Not found. Some bank account nos are in IBAN, others are in hungarian format, let's try to convert
        if (preg_match('/\d{8}-\d{8}-\d{8}/', $contactData[$this->config['bankAccountNumberParameter']])) {
            $accountNumber = str_replace('-', '', $contactData[$this->config['bankAccountNumberParameter']]);
            // format '1111 2222 3333 4444 5555 6666'
            $partialIban = trim(chunk_split($accountNumber, 4, ' '));

            $contact = Get::entitySingle('Contact', [
                'select' => ['id'],
                'where' => [
                    [$this->config['bankAccountNumberParameter'], 'LIKE', "%{$partialIban}%"],
                    ['is_deleted', '=', false],
                ],
                'limit' => 1,
            ]);
            if (!is_null($contact)) {
                return $contact['id'];
            }
        }

        // Still not found. Create a brand new contact based on $contactData
        return Create::contact($contactData);
    }

    /**
     * This function handles the error cases.
     * It creates a log entry with a prefix.
     * Also updates the error stats.
     *
     * @param string $message
     */
    private function raiseError(string $message): void
    {
        Civi::log()->error('Magnet XML import | '.$message);
        $this->stats['errors'][] = $message;
    }

    /**
     * This function checks for duplicated transaction.
     * If there is a transaction with the same trxn id in
     * the CRM database, it returns true, otherwise false.
     *
     * @param string $trxnId transaction identifier
     *
     * @return bool
     */
    private function duplicatedTransaction(string $trxnId): bool
    {
        // Duplicate detection. skip the import when the transaction id already exists.
        try {
            $contributions = Contribution::get(false)
                ->addWhere('trxn_id', '=', $trxnId)
                ->setLimit(1)
                ->execute();
            if (count($contributions) > 0) {
                return true;
            }
        } catch (Exception $e) {
            $this->raiseError('Failed to detect duplication for the following transaction: '.$trxnId.' Details: '.$e->getMessage());
        }

        return false;
    }

    /**
     * This function creates a contribution based on the given params.
     * On case of the contribution process fails, it returns false
     * otherwise it returns true.
     *
     * @param array $params the contribution params
     *
     * @return bool
     */
    private function contribution(array $params): bool
    {
        try {
            $contribution = Contribution::create(false);
            foreach ($params as $key => $value) {
                $contribution = $contribution->addValue($key, $value);
            }
            $contribution->execute();

            return true;
        } catch (Exception $e) {
            $this->raiseError('Failed to create CRM contribution for the following transaction: '.$params['trxn_id'].' Details: '.$e->getMessage());
        }

        return false;
    }
}
