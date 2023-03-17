<?php

use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\MagnetXmlImport\HeadlessTestCase;

/**
 * @group headless
 */
class CRM_MagnetXmlImport_Form_MagnetXMLImportTest extends HeadlessTestCase
{
    /**
     * @return void
     */
    public function testPreProcess()
    {
        $form = new CRM_MagnetXmlImport_Form_MagnetXMLImport();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
    }

    /**
     * @return void
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
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testBuildQuickForm()
    {
        $customGroup = CustomGroup::create(false)
            ->addValue('title', 'TestCustomGroup')
            ->addValue('extends', 'Contact')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        CustomField::create()
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
     * @return void
     */
    public function testAddRules()
    {
        $form = new CRM_MagnetXmlImport_Form_MagnetXMLImport();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->addRules());
    }

    /**
     * @return void
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

    /**
     * @return void
     */
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
        $customGroup = CustomGroup::create(false)
            ->addValue('title', 'TestCustomGroupPostProcess')
            ->addValue('extends', 'Contact')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        CustomField::create()
            ->addValue('custom_group_id', $customGroup['id'])
            ->addValue('label', 'bank account number')
            ->addValue('data_type', 'String')
            ->addValue('html_type', 'Text')
            ->addValue('text_length', 40)
            ->execute();
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
