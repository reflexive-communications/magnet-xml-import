<?php

require_once 'magnet_xml_import.civix.php';

use CRM_MagnetXmlImport_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function magnet_xml_import_civicrm_config(&$config): void
{
    _magnet_xml_import_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function magnet_xml_import_civicrm_navigationMenu(&$menu): void
{
    _magnet_xml_import_civix_insert_navigation_menu($menu, 'Contributions', [
        'label' => E::ts('Magnet XML Import'),
        'name' => 'magnet_xml_import',
        'url' => 'civicrm/contribute/magnet-xml',
        'permission' => 'administer CiviCRM,access CiviContribute,edit contributions',
        'operator' => 'AND',
        'separator' => 0,
    ]);
    _magnet_xml_import_civix_navigationMenu($menu);
}
