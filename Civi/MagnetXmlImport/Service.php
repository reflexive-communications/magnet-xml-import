<?php

namespace Civi\MagnetXmlImport;

use Civi;
use Civi\RcBase\ApiWrapper\Create;
use Civi\RcBase\ApiWrapper\Get;
use Civi\RcBase\ApiWrapper\Update;
use Exception;

/**
 * Service class for the Magnet XML import.
 */
class Service
{
    /**
     * The configuration array.
     *
     * @var array
     */
    private array $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * This function starts the xml parsing process
     *
     * @param string $file Path to the XML file
     *
     * @return array Stats about the process
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function process(string $file): array
    {
        $xml = Civi::service('IOProcessor.XML')->decode(file_get_contents($file), false);
        $stats = [
            'all' => count($xml->Tranzakcio),
            'skipped' => 0,
            'imported' => 0,
            'updated' => 0,
            'errors' => [],
        ];

        foreach ($xml->Tranzakcio as $transaction) {
            $contact_data = [
                'contact_type' => 'Individual',
                'display_name' => (string)$transaction->Ellenpartner,
                $this->config['bankAccountNumberParameter'] => (string)$transaction->Ellenszamla,
            ];
            $contribution_data = [
                'trxn_id' => (string)$transaction->Tranzakcioszam,
                'total_amount' => (float)$transaction->Osszeg,
                'currency' => (string)$transaction->Osszeg->attributes()['Devizanem'],
                'receive_date' => str_replace('.', '', (string)$transaction->Esedekessegnap), // we need 20150131
                'source' => $this->config['source'],
                'financial_type_id' => $this->config['financialTypeId'],
                'payment_instrument_id' => $this->config['paymentInstrumentId'],
                'contribution_status_id:name' => 'Completed',
            ];

            // Skip negative transactions (if only incomes are required)
            if ($this->config['onlyIncome'] && $contribution_data['total_amount'] < 0.01) {
                $stats['skipped']++;
                continue;
            }

            try {
                $contact_id = $this->contact($contact_data);

                // Create the contribution.
                $contribution_id = Get::contributionIDByTransactionID($contribution_data['trxn_id']);
                if (is_null($contribution_id)) {
                    Create::contribution($contact_id, $contribution_data);
                    $stats['imported']++;
                } else {
                    Update::contribution($contribution_id, $contribution_data);
                    $stats['updated']++;
                }
            } catch (Exception $e) {
                Civi::log()->error('Magnet XML import | Failed to import transaction: '.$e->getMessage());
                $stats['errors'][] = $e->getMessage();
                continue;
            }
        }

        return $stats;
    }

    /**
     * This function returns the contact id of the contributor contact.
     * First it tries to find it based on the bank account number parameter.
     * If not found it formats the parameter and tries to find it again.
     * If not found, it creates a new contact.
     *
     * @param array $contact_data
     *
     * @return int $contactId
     * @throws \Civi\RcBase\Exception\APIException
     */
    private function contact(array $contact_data): int
    {
        $contact = Get::entitySingle('Contact', [
            'select' => ['id'],
            'where' => [
                [$this->config['bankAccountNumberParameter'], '=', $contact_data[$this->config['bankAccountNumberParameter']]],
                ['is_deleted', '=', false],
            ],
            'limit' => 1,
        ]);
        if (!is_null($contact)) {
            return $contact['id'];
        }

        // Not found. Some bank account nos are in IBAN, others are in hungarian format, let's try to convert
        if (preg_match('/\d{8}-\d{8}-\d{8}/', $contact_data[$this->config['bankAccountNumberParameter']])) {
            $accountNumber = str_replace('-', '', $contact_data[$this->config['bankAccountNumberParameter']]);
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
        return Create::contact($contact_data);
    }
}
