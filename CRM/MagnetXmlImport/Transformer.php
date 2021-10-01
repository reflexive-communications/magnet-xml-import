<?php

use CRM_MagnetXmlImport_ExtensionUtil as E;

/*
 * This class contains the data transformation rules.
 */
class CRM_MagnetXmlImport_Transformer
{
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
            'display_name' => (string) $transaction->Ellenpartner,
            $bankAccountNumberContactParamName => (string) $transaction->Ellenszamla,
        ];
    }
}
