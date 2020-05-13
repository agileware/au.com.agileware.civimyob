<?php
use CRM_Civimyob_ExtensionUtil as E;

class CRM_Civimyob_Page_AJAX extends CRM_Core_Page {

  /**
   * Function to get contact sync errors by id
   */
  public static function contactSyncErrors() {
    $syncerrors = array();
    if (CRM_Utils_Array::value('myoberrorid', $_REQUEST)) {
      $myoberrorid = CRM_Utils_Type::escape($_REQUEST['myoberrorid'], 'Integer');
      $accountcontact = civicrm_api3("AccountContact", "get", array(
        "id"          => $myoberrorid ,
        "sequential" => TRUE,
      ));
      if ($accountcontact["count"]) {
        $accountcontact = $accountcontact["values"][0];
        $syncerrors = $accountcontact["error_data"];
        $syncerrors = json_decode($syncerrors, TRUE);
      }
    }
    CRM_Utils_JSON::output($syncerrors);
  }

  /**
   * Function to get invoice sync errors by id
   *
   */
  public static function invoiceSyncErrors() {
    $syncerrors = array();
    if (CRM_Utils_Array::value('myoberrorid', $_REQUEST)) {
      $contactid = CRM_Utils_Type::escape($_REQUEST['myoberrorid'], 'Integer');
      $contributions = _civimyob_getContactContributions($contactid);
      $invoices = _civimyob_getErroredInvoicesOfContributions($contributions);
      foreach ($invoices["values"] as $invoice) {
        $syncerrors = array_merge($syncerrors, json_decode($invoice["error_data"], TRUE));
      }
    }
    CRM_Utils_JSON::output($syncerrors);
  }

}
