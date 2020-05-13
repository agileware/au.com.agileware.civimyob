<?php
use CRM_Civimyob_ExtensionUtil as E;

class CRM_Civimyob_Page_FetchTaxCodes extends CRM_Core_Page {

  /**
   * CiviCRM function which is executed on page run.
   */
  public function run() {
    self::refreshTaxCodesInSettings();
    CRM_Core_Session::setStatus('TaxCodes list has been refreshed successfully.', '', 'success');
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/myob/settings'));
  }

  /**
   * Refresh tax codes in settings.
   *
   * @return array
   */
  public static function refreshTaxCodesInSettings() {
    $myobTaxCode = new CRM_Myob_TaxCode();
    $taxCodes = $myobTaxCode->all();
    Civi::settings()->set('myob_taxcodes_list_json', json_encode($taxCodes));
    return $taxCodes;
  }


  /**
   * Get tax codes from settings or cloud (Refreshed)
   *
   * @param bool $refresh (Refresh tax codes list in settings from cloud)
   * @return array|mixed
   */
  public static function getTaxCodes($refresh = FALSE) {
    $taxCodes = Civi::settings()->get('myob_taxcodes_list_json');

    if ($refresh || (!$taxCodes)) {
      $taxCodes = self::refreshTaxCodesInSettings();
    }
    else {
      $taxCodes = json_decode($taxCodes, TRUE);
    }

    return $taxCodes;
  }

}
