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
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testProcess()
    {
        $service = new Service([
            'source' => 'Magnet Bank',
            'financialTypeId' => 1,
            'paymentInstrumentId' => 5,
            'bankAccountNumberParameter' => 'bank.bank_account_number',
            'onlyIncome' => 1,
        ], E::path('tests/phpunit/Civi/MagnetXmlImport/sampleData.xml'));

        self::assertSame([
            'all' => 7,
            'imported' => 4,
            'skipped' => 1,
            'duplication' => 1,
            'errors' => ['Failed to get CRM contact to the transaction: Failed to execute API: Contact.create Reason: DB Error: unknown error'],
        ], $service->process(), 'Wrong stats');
    }
}
