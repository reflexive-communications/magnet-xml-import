<?php

namespace Civi\MagnetXmlImport;

use CRM_MagnetXmlImport_ExtensionUtil as E;

/**
 * @group headless
 */
class ServiceTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testProcess()
    {
        $service = new Service([
            'source' => 'Magnet Bank',
            'financial_type_id' => 1,
            'payment_instrument_id' => 5,
            'bank_account_custom_field' => 'bank.bank_account_number',
            'only_income' => 1,
        ]);

        self::assertSame([
            'all' => 7,
            'skipped' => 1,
            'imported' => 4,
            'updated' => 1,
            'errors' => ['Failed to execute API: Contact.create Reason: DB Error: unknown error'],
        ], $service->process(E::path('tests/phpunit/Civi/MagnetXmlImport/sampleData.xml')), 'Wrong stats');
    }
}
