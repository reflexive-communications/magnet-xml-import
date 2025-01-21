<?php

use Civi\MagnetXmlImport\HeadlessTestCase;
use CRM_MagnetXmlImport_ExtensionUtil as E;

/**
 * @group headless
 */
class CRM_MagnetXmlImport_Form_MagnetXMLImportTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testBuildForm()
    {
        $form = new CRM_MagnetXmlImport_Form_MagnetXMLImport();
        $form->controller = new CRM_Core_Controller();

        $form->buildQuickForm();
        $defaults = $form->setDefaultValues();

        // Check defaults
        self::assertSame('Magnet Bank', $defaults['source'], 'Wrong default value for "source"');
        self::assertTrue($defaults['only_income'], 'Wrong default value for "only_income"');

        // Check render
        $wrapper = new CRM_Utils_Wrapper();
        self::expectOutputRegex('/<div class="help">Import contributions from Magnet Bank XML reports/');
        $wrapper->run('CRM_MagnetXmlImport_Form_MagnetXMLImport');
    }

    /**
     * @return void
     */
    public function testValidateWithCorrectValues()
    {
        $form = new CRM_MagnetXmlImport_Form_MagnetXMLImport();
        $form->_submitFiles = [
            'import_file' => [
                'type' => 'application/xml',
                'tmp_name' => E::path('tests/phpunit/Civi/MagnetXmlImport/sampleData.xml'),
            ],
        ];
        $form->_flagSubmitted = true;
        $form->addRules();

        self::assertTrue($form->validate());
    }

    /**
     * @return void
     */
    public function testValidateWithInvalidFileExtension()
    {
        $form = new CRM_MagnetXmlImport_Form_MagnetXMLImport();
        $form->_submitFiles = [
            'import_file' => [
                'type' => 'text/csv',
                'tmp_name' => E::path('tests/phpunit/Civi/MagnetXmlImport/sampleData.csv'),
            ],
        ];
        $form->_flagSubmitted = true;
        $form->addRules();

        self::assertFalse($form->validate());
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testPostProcess()
    {
        $values = [
            'source' => 'Magnet Bank',
            'financial_type_id' => 1,
            'payment_instrument_id' => 5,
            'bank_account_custom_field' => 'bank.bank_account_number',
            'only_income' => 1,
        ];
        $files = [
            'import_file' => [
                'type' => 'application/xml',
                'tmp_name' => E::path('tests/phpunit/Civi/MagnetXmlImport/sampleData.xml'),
            ],
        ];

        $form = new CRM_MagnetXmlImport_Form_MagnetXMLImport();
        $form->controller = new CRM_Core_Controller();
        $form->buildQuickForm();

        $form->_submitValues = $values;
        $form->_submitFiles = $files;
        $form->postProcess();

        $session = CRM_Core_Session::singleton()->getStatus();
        self::assertCount(1, $session, 'Wrong number of messages');
        self::assertStringStartsWith('The import has been finished. Results:', $session[0]['text'], 'Wrong message');
    }
}
