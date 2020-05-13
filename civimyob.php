<?php

require_once 'civimyob.civix.php';
use CRM_Civimyob_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civimyob_civicrm_config(&$config) {
  _civimyob_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function civimyob_civicrm_xmlMenu(&$files) {
  _civimyob_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civimyob_civicrm_install() {
  _civimyob_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function civimyob_civicrm_postInstall() {
  _civimyob_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civimyob_civicrm_uninstall() {
  _civimyob_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civimyob_civicrm_enable() {
  _civimyob_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civimyob_civicrm_disable() {
  _civimyob_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function civimyob_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civimyob_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civimyob_civicrm_managed(&$entities) {
  _civimyob_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civimyob_civicrm_caseTypes(&$caseTypes) {
  _civimyob_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function civimyob_civicrm_angularModules(&$angularModules) {
  _civimyob_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civimyob_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civimyob_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function civimyob_civicrm_entityTypes(&$entityTypes) {
  _civimyob_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
 */
function civimyob_civicrm_navigationMenu(&$menu) {
  $maxID = CRM_Core_DAO::singleValueQuery("SELECT max(id) FROM civicrm_navigation");
  $navId = $maxID + 1;

  $administerMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Administer', 'id', 'name');
  $parentID = !empty($administerMenuId) ? $administerMenuId : NULL;

  $navigationMenu = array(
    'attributes' => array(
      'label' => 'MYOB',
      'name' => 'MYOB',
      'url' => NULL,
      'permission' => 'administer CiviCRM',
      'operator' => NULL,
      'separator' => NULL,
      'parentID' => $parentID,
      'active' => 1,
      'navID' => $navId,
    ),
    'child' => array(
      $navId + 1 => array(
        'attributes' => array(
          'label' => 'MYOB Settings',
          'name' => 'MYOB Settings',
          'url' => 'civicrm/myob/settings',
          'permission' => 'administer CiviCRM',
          'operator' => NULL,
          'separator' => NULL,
          'active' => 1,
          'parentID' => $navId,
          'navID' => $navId + 1,
        ),
      ),
      $navId + 2 => array(
        'attributes' => array(
          'label' => 'Synchronize contacts',
          'name' => 'Contact Sync',
          'url' => 'civicrm/a/#/accounts/contact/sync/myob',
          'permission' => 'administer CiviCRM',
          'operator' => NULL,
          'separator' => NULL,
          'active' => 1,
          'parentID'   => $navId,
          'navID' => $navId + 2,
        ),
      ),
    ),
  );
  if ($parentID) {
    $menu[$parentID]['child'][$navId] = $navigationMenu;
  }
  else {
    $menu[$navId] = $navigationMenu;
  }
}

/**
 * Implements hook_civicrm_accountsync_plugins().
 */
function civimyob_civicrm_accountsync_plugins(&$plugins) {
  $plugins[] = getAccountsyncPluginName();
}

/**
 * Method to return AccountSync plugin name
 * @return string
 */
function getAccountsyncPluginName() {
  return 'myob';
}

/**
 * Implements hook pageRun().
 *
 * Add Myob links to contact summary
 *
 * @param $page
 */
function civimyob_civicrm_pageRun(&$page) {
  $pageName = get_class($page);
  if ($pageName != 'CRM_Contact_Page_View_Summary' || !CRM_Core_Permission::check('view all contacts')) {
    return;
  }

  if (($contactID = $page->getVar('_contactId')) != FALSE) {

    CRM_Core_Resources::singleton()->addScriptFile('au.com.agileware.civimyob', 'js/myob_errors.js');

    CRM_Civimyob_Page_Inline_ContactSyncStatus::addContactSyncStatusBlock($page, $contactID);
    CRM_Civimyob_Page_Inline_ContactSyncErrors::addContactSyncErrorsBlock($page, $contactID);
    CRM_Civimyob_Page_Inline_InvoiceSyncErrors::addInvoiceSyncErrorsBlock($page, $contactID);

    CRM_Core_Region::instance('contact-basic-info-left')->add(array(
      'template' => "CRM/Civimyob/ContactSyncBlock.tpl",
    ));
  }
}


/**
 * Gettings contributions of sinlge contact
 *
 * @param $contactid
 */
function _civimyob_getContactContributions($contactid) {
  $contributions = civicrm_api3("Contribution", "get", array(
    "contact_id" => $contactid,
    "return"     => array("contribution_id"),
    "sequential" => TRUE,
  ));
  $contributions = array_column($contributions["values"], "id");
  return $contributions;
}

/**
 * Gettings errored invoices of given contributions
 *
 * @param $contributions
 */
function _civimyob_getErroredInvoicesOfContributions($contributions) {
  $invoices = civicrm_api3("AccountInvoice", "get", array(
    "plugin"          => getAccountsyncPluginName(),
    "sequential"      => TRUE,
    "contribution_id" => array("IN" => $contributions),
    "error_data"      => array("<>" => ""),
  ));
  return $invoices;
}

/**
 * @param $objectName
 * @param array $headers
 * @param $values
 * @param $selector
 */
function civimyob_civicrm_searchColumns($objectName, &$headers, &$values, &$selector) {
  if ($objectName == 'contribution') {
    foreach ($values as &$value) {
      try {
        $invoiceID = civicrm_api3('AccountInvoice', 'getsingle', array(
          'plugin'          => getAccountsyncPluginName(),
          'contribution_id' => $value['contribution_id'],
          'return'          => 'accounts_invoice_id',
        ));
        if (isset($invoiceID['accounts_invoice_id']) && $invoiceID['accounts_invoice_id'] != '') {
          $value['contribution_status'] .= "<br><br><p>Synced with Myob</p>";
        }
        else {
          $value['contribution_status'] .= "<br><br><p>In Queue to Sync with Myob</p>";
        }
      }
      catch (Exception $e) {
        continue;
      }
    }
  }
}

/**
 * Implements hook_civicrm_contactSummaryBlocks().
 *
 * @link https://github.com/civicrm/org.civicrm.contactlayout
 */
function civimyob_civicrm_contactSummaryBlocks(&$blocks) {
  $blocks += [
    'civimyobblock' => [
      'title' => ts('Civi Myob'),
      'blocks' => [],
    ]
  ];
  $blocks['civimyobblock']['blocks']['contactsyncstatus'] = [
    'title' => ts('Contact Sync Status'),
    'tpl_file' => 'CRM/Civimyob/Page/Inline/ContactSyncStatus.tpl',
    'edit' => FALSE,
  ];
  $blocks['civimyobblock']['blocks']['contactsyncerrors'] = [
    'title' => ts('Contact Sync Errors'),
    'tpl_file' => 'CRM/Civimyob/Page/Inline/ContactSyncErrors.tpl',
    'edit' => FALSE,
  ];
  $blocks['civimyobblock']['blocks']['invoicesyncerrors'] = [
    'title' => ts('Invoice Sync Errors'),
    'tpl_file' => 'CRM/Civimyob/Page/Inline/InvoiceSyncErrors.tpl',
    'edit' => FALSE,
  ];

}
