<?php

use CRM_MagnetXmlImport_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Testcases for the Admin form functionalities.
 *
 * @group headless
 */
class CRM_MagnetXmlImport_Form_MagnetXMLImportTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
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
     * PreProcess test case pre process does nothing.
     */
    public function testPreProcess()
    {
        $form = new CRM_MagnetXmlImport_Form_MagnetXMLImport();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
    }

    /**
     * setDefaultValues test case.
     */
    public function testSetDefaultValues()
    {
        $form = new CRM_MagnetXmlImport_Form_MagnetXMLImport();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $expectedConfig = [
            'source' => 'Magnet Bank',
            'financialTypeId' => 1,
            'paymentInstrumentId' => 5,
            'bankAccountNumberParameter' => 'custom_1',
            'onlyIncome' => 1,
        ];
        self::assertSame($expectedConfig, $form->setDefaultValues());
    }

    /**
     * Build quick form test case.
     * One custom contact param is created
     * to be able to test that branch.
     */
    public function testBuildQuickForm()
    {
        $customGroup = \Civi\Api4\CustomGroup::create(false)
            ->addValue('title', 'TestCustomGroup')
            ->addValue('extends', 'Contact')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        \Civi\Api4\CustomField::create()
            ->addValue('custom_group_id', $customGroup['id'])
            ->addValue('label', 'bank account number')
            ->addValue('data_type', 'String')
            ->addValue('html_type', 'Text')
            ->execute();
        $form = new CRM_MagnetXmlImport_Form_MagnetXMLImport();
        $form->setVar('_gid', null);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->buildQuickForm());
    }

    /**
     * addRules test case.
     */
    public function testAddRules()
    {
        $form = new CRM_MagnetXmlImport_Form_MagnetXMLImport();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->addRules());
    }

    /**
     * fileExtension test cases.
     */
    public function testFileExtensionValidFile()
    {
        $fileData = [
            'importSource' => [
                'type' => 'text/xml',
            ],
        ];
        $result = CRM_MagnetXmlImport_Form_MagnetXMLImport::fileExtension([], $fileData);
        self::assertTrue($result);
    }
    public function testFileExtensionNotValidFile()
    {
        $fileData = [
            'importSource' => [
                'type' => 'text/notxml',
            ],
        ];
        $result = CRM_MagnetXmlImport_Form_MagnetXMLImport::fileExtension([], $fileData);
        self::assertTrue(array_key_exists('importSource', $result));
    }

    /**
     * PostProcess test case.
     */
    public function testPostProcess()
    {
        $submitValues = [
            'source' => 'Magnet Bank',
            'financialTypeId' => 1,
            'paymentInstrumentId' => 5,
            'bankAccountNumberParameter' => 'custom_1',
            'onlyIncome' => 1,
        ];
        $submitFiles = [
            'importSource' => [
                'type' => 'text/notxml',
                'tmp_name' => __DIR__.'/sampleData.xml',
            ],
        ];
        $form = new CRM_MagnetXmlImport_Form_MagnetXMLImport();
        $form->setVar('_submitValues', $submitValues);
        $form->setVar('_submitFiles', $submitFiles);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'PostProcess supposed to be empty.');
    }
}
