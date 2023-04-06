<?php

namespace Civi\MagnetXmlImport;

/**
 * @group headless
 */
class TransformerTest extends HeadlessTestCase
{
    /**
     * @return void
     */
    public function testMagnetTransactionToContact()
    {
        $contactDisplayName = 'Teszt Contact01';
        $bankAccountNumber = 'HU20 1111 2222 3333 4445 0000 0000';
        $xmlString = '<Tranzakcio><Tranzakcioszam>trxn003</Tranzakcioszam><Osszeg Devizanem="HUF">1000.00</Osszeg><Esedekessegnap>2016.01.15.</Esedekessegnap><Ellenpartner>'.$contactDisplayName
            .'</Ellenpartner><Ellenszamla>'.$bankAccountNumber.'</Ellenszamla></Tranzakcio>';
        $bankAccountName = 'custom_1';
        $xml = simplexml_load_string($xmlString);
        $transformed = Transformer::magnetTransactionToContact($xml, $bankAccountName);
        $expected = [
            'contact_type' => 'Individual',
            'display_name' => $contactDisplayName,
            $bankAccountName => $bankAccountNumber,
        ];
        self::assertSame($expected, $transformed);
    }

    /**
     * @return void
     */
    public function testMagnetTransactionToContribution()
    {
        $config = [
            'source' => 'test source',
            'financialTypeId' => '1',
            'paymentInstrumentId' => '1',
        ];
        $trxnId = 'trxn003';
        $amount = '1000.00';
        $currency = 'HUF';
        $xmlString = '<Tranzakcio><Tranzakcioszam>'.$trxnId.'</Tranzakcioszam><Osszeg Devizanem="'.$currency.'">'.$amount
            .'</Osszeg><Esedekessegnap>2016.01.15.</Esedekessegnap><Ellenpartner>Teszt Contact01</Ellenpartner><Ellenszamla>HU20 1111 2222 3333 4445 0000 0000</Ellenszamla></Tranzakcio>';
        $xml = simplexml_load_string($xmlString);
        $transformed = Transformer::magnetTransactionToContribution($xml, $config);
        $expected = [
            'trxn_id' => $trxnId,
            'total_amount' => (float)$amount,
            'currency' => $currency,
            'receive_date' => '20160115',
            'source' => $config['source'],
            'financial_type_id' => $config['financialTypeId'],
            'payment_instrument_id' => $config['paymentInstrumentId'],
            'contribution_status_id' => Transformer::CRM_COMPLETED_STATUS_ID,
        ];
        self::assertSame($expected, $transformed);
    }
}
