<?php

/**
 * This class contains the data transformation rules.
 */
class CRM_MagnetXmlImport_Transformer
{
    const CRM_COMPLETED_STATUS_ID = 1;

    /**
     * Transform Magnet transaction data to civicrm contact data
     *
     * @param SimpleXMLElement $transaction Magnet transaction object
     * @param string $bankAccountNumberContactParamName
     *
     * @return array contactData
     */
    public static function magnetTransactionToContact(SimpleXMLElement $transaction, string $bankAccountNumberContactParamName): array
    {
        return [
            'contact_type' => 'Individual',
            'display_name' => (string)$transaction->Ellenpartner,
            $bankAccountNumberContactParamName => (string)$transaction->Ellenszamla,
        ];
    }

    /**
     * Transform Magnet transaction data to civicrm contribution data
     *
     * @param SimpleXMLElement $transaction Magnet transaction object
     * @param array $config the configuration for the contribution mapping
     *
     * @return array contributionData
     */
    public static function magnetTransactionToContribution(SimpleXMLElement $transaction, array $config): array
    {
        return [
            'trxn_id' => (string)$transaction->Tranzakcioszam,
            'total_amount' => (float)$transaction->Osszeg,
            'currency' => (string)$transaction->Osszeg->attributes()['Devizanem'],
            'receive_date' => str_replace('.', '', (string)$transaction->Esedekessegnap), // we need 20150131
            'source' => $config['source'],
            'financial_type_id' => $config['financialTypeId'],
            'payment_instrument_id' => $config['paymentInstrumentId'],
            'contribution_status_id' => self::CRM_COMPLETED_STATUS_ID,
        ];
    }
}
