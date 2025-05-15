<?php

use Civi\Api4\Contact;
use Civi\MagnetXmlImport\Service;
use Civi\RcBase\ApiWrapper\Get;
use CRM_MagnetXmlImport_ExtensionUtil as E;

/**
 * Start import form
 */
class CRM_MagnetXmlImport_Form_MagnetXMLImport extends CRM_Core_Form
{
    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function buildQuickForm(): void
    {
        $this->setTitle(E::ts('Import Contributions (Magnet Bank)'));

        $this->add('text', 'source', E::ts('Contribution source'), [], true);
        $this->add(
            'select',
            'financial_type_id',
            E::ts('Financial Type'),
            ['' => E::ts('-- please select --')] + Get::entity('FinancialType', [], ['id' => 'name'])->getArrayCopy(),
            true,
            ['class' => 'crm-select2']
        );
        $this->add(
            'select',
            'payment_instrument_id',
            E::ts('Payment method'),
            ['' => E::ts('-- please select --')] + Get::entity(
                'OptionValue',
                ['where' => [['option_group_id:name', '=', 'payment_instrument']]],
                ['value' => 'label']
            )->getArrayCopy(),
            true,
            ['class' => 'crm-select2']
        );

        $custom_fields = [];
        $fields_all = Contact::getFields()->execute();
        foreach ($fields_all as $field) {
            if ($field['type'] != 'Custom' || $field['data_type'] != 'String' || $field['input_type'] != 'Text') {
                continue;
            }
            $custom_fields[$field['name']] = $field['label'];
        }
        uasort($custom_fields, 'strcmp');
        $this->add(
            'select',
            'bank_account_custom_field',
            E::ts('Bank Account Custom Field'),
            ['' => E::ts('-- please select --')] + $custom_fields,
            true,
            ['class' => 'crm-select2']
        );

        $this->add('checkbox', 'only_income', E::ts('Only income'));
        $this->add('file', 'import_file', E::ts('Magnet XML file'), [], true);

        $this->addButtons([
            [
                'type' => 'submit',
                'name' => E::ts('Submit'),
                'isDefault' => true,
            ],
        ]);
    }

    /**
     * @return array
     */
    public function setDefaultValues(): array
    {
        // Form now submitted --> no need to set defaults
        if ($this->isSubmitted()) {
            return [];
        }

        $defaults = [];
        $defaults['source'] = 'Magnet Bank';
        $defaults['only_income'] = true;

        return $defaults;
    }

    /**
     * @return void
     */
    public function addRules(): void
    {
        $this->addFormRule(['CRM_MagnetXmlImport_Form_MagnetXMLImport', 'fileExtension']);
    }

    /**
     * Accept only xml files.
     *
     * @param $values
     * @param $files
     *
     * @return array|true
     */
    public static function fileExtension($values, $files)
    {
        $errors = [];
        $validTypes = ['text/xml', 'application/xml'];
        if (!in_array($files['import_file']['type'], $validTypes)) {
            $errors['import_file'] = E::ts('Only XML files allowed');
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function postProcess(): void
    {
        $values = $this->exportValues();

        $stats = (new Service([
            'bank_account_custom_field' => $values['bank_account_custom_field'],
            'source' => $values['source'],
            'financial_type_id' => $values['financial_type_id'],
            'payment_instrument_id' => $values['payment_instrument_id'],
            'only_income' => $values['only_income'],
        ]))->process($this->_submitFiles['import_file']['tmp_name']);

        // Stats
        $msgHtml = '';
        foreach ($stats as $index => $value) {
            if ($index === 'errors') {
                continue;
            }
            $msgHtml .= "<li>{$index}: {$value}</li>";
        }
        foreach ($stats['errors'] as $e) {
            $msgHtml .= "<li>{$e}</li>";
        }

        CRM_Core_Session::setStatus(E::ts('The import has been finished. Results: </br>%1', [1 => "<ul>{$msgHtml}</ul>"]), 'Magnet XML Import', 'info');
    }
}
