<?php
use CRM_Civimyob_ExtensionUtil as E;

class CRM_Civimyob_Page_Inline_InvoiceSyncErrors extends CRM_Core_Page {

  public function run() {
    $contactId = CRM_Utils_Request::retrieveValue('cid', 'Positive');
    if (!$contactId) {
      return;
    }
    self::addInvoiceSyncErrorsBlock($this, $contactId);
    parent::run();
  }

  /**
   * @param CRM_Core_Page $page
   * @param int $contactID
   */
  public static function addInvoiceSyncErrorsBlock(&$page, $contactID) {

    $hasInvoiceErrors = FALSE;

    try{
      $account_contact = civicrm_api3('account_contact', 'getsingle', array(
        'contact_id' => $contactID,
        'return'     => 'accounts_contact_id, accounts_needs_update, connector_id, error_data, id, contact_id',
        'plugin'     => getAccountsyncPluginName(),
      ));

      $page->assign('accountContactId', $account_contact['id']);

      $contributions = _civimyob_getContactContributions($account_contact["contact_id"]);
      if (count($contributions)) {
        $invoices = _civimyob_getErroredInvoicesOfContributions($contributions);
        if ($invoices["count"]) {
          $hasInvoiceErrors = TRUE;
          $page->assign('erroredInvoices', $invoices["count"]);
        }
      }

    }
    catch(Exception $e) {

    }

    $page->assign('hasInvoiceErrors', $hasInvoiceErrors);
    $page->assign('contactID', $contactID);

  }

}
