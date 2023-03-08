<?php

use CRM_MagnetXmlImport_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * @group headless
 */
class CRM_MagnetXmlImport_ServiceTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
{
    public function setUpHeadless()
    {
        return \Civi\Test::headless()
            ->installMe(__DIR__)
            ->apply();
    }

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Process test case.
     * Create the custom group and field,
     */
    public function testProcess()
    {
        $customGroup = \Civi\Api4\CustomGroup::create(false)
            ->addValue('title', 'TestCustomGroupForServiceTests')
            ->addValue('extends', 'Contact')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        \Civi\Api4\CustomField::create()
            ->addValue('custom_group_id', $customGroup['id'])
            ->addValue('label', 'bank account number')
            ->addValue('data_type', 'String')
            ->addValue('html_type', 'Text')
            ->addValue('text_length', 40)
            ->execute();
        $config = [
            'source' => 'Magnet Bank',
            'financialTypeId' => 1,
            'paymentInstrumentId' => 5,
            'bankAccountNumberParameter' => 'custom_1',
            'onlyIncome' => 1,
        ];
        $path = __DIR__.'/Form/sampleData.xml';
        $service = new CRM_MagnetXmlImport_Service($config, $path);
        $stats = $service->process();
        $expectedStats = [
            'all' => 7,
            'imported' => 4,
            'skipped' => 1,
            'duplication' => 1,
            'errors' => ['Failed to get CRM contact to the transaction: DB Error: unknown error'],
        ];
        self::assertSame($expectedStats, $stats);
    }
}
