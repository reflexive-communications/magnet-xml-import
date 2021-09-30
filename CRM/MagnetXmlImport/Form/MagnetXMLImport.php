<?php

use CRM_MagnetXmlImport_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_MagnetXmlImport_Form_MagnetXMLImport extends CRM_Core_Form
{
    /**
     * Preprocess form
     */
    public function preProcess()
    {
        parent::preProcess();
    }

    /**
     * Set default values
     *
     * @return array
     */
    public function setDefaultValues()
    {
        $this->_defaults['source'] = 'Magnet Bank';
        $this->_defaults['financialTypeId'] = 1;    // donation
        $this->_defaults['paymentInstrumentId'] = 5;    // electronic founds transfer
        $this->_defaults['bankAccountNumberParameter'] = 'custom_1';
        $this->_defaults['onlyIncome'] = 1; // skip negative transactions.

        return $this->_defaults;
    }

    /**
     * Build form
     *
     * The list of the contact parameters are based on this solution:
     * https://github.com/civicrm/civicrm-core/blob/master/CRM/UF/Form/Field.php#L237-L247
     */
    public function buildQuickForm()
    {
        $this->add('text', 'source', ts('Source'), [], true);
        $this->add('select', 'financialTypeId', ts('Financial Type'), [''=>ts('- select -')] + CRM_Contribute_BAO_Contribution::buildOptions('financial_type_id', 'search'), true);
        $this->add('select', 'paymentInstrumentId', ts('Payment method'), [''=>ts('- select -')] + CRM_Contribute_BAO_Contribution::buildOptions('payment_instrument_id', 'search'), true);
        $fields = CRM_Core_BAO_UFField::getAvailableFields($this->_gid, $defaults);
        $contactParamNames = ['Contact', 'Individual', 'Household', 'Organization'];
        $paramOptions = [];
        foreach($fields as $k => $value) {
            if (array_search($k, $contactParamNames) === false) {
                continue;
            }
            foreach ($value as $key1 => $value1) {
                // handle custom fields first.
                if ($customFieldId = CRM_Core_BAO_CustomField::getKeyID($key1)) {
                    $customGroupId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', $customFieldId, 'custom_group_id');
                    $customGroupName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', $customGroupId, 'title');
                    $paramOptions[$key1] = $value1['title'] . ' :: ' . $customGroupName;
                } else {
                    $paramOptions[$key1] = $value1['title'];
                }
            }
        }
        $this->add('select', 'bankAccountNumberParameter', ts('Bank Account'), [''=>ts('- select -')] + $paramOptions, true);
        $this->add('checkbox', 'onlyIncome', ts('Only income'), [], false);
        $this->add('file', 'importSource', ts('Magnet XML file'), [], true);
        $formParameterNames = ['source', 'financialTypeId', 'paymentInstrumentId', 'bankAccountNumberParameter', 'onlyIncome', 'importSource'];
        $this->assign('parameterNames', $formParameterNames);
        parent::buildQuickForm();
    }

    /**
     * Postprocess form
     */
    public function postProcess()
    {
        parent::postProcess();
    }
}
