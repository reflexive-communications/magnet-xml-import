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
     */
    public function testProcess()
    {
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
            'all' => 6,
            'imported' => 4,
            'skipped' => 1,
            'duplication' => 1,
            'errors' => [],
        ];
        self::assertSame($expectedStats, $stats);
    }
}
