<?php
use CRM_Civimyob_ExtensionUtil as E;

class CRM_Civimyob_Page_FetchCompanies extends CRM_Core_Page {

  /**
   * CiviCRM function which is executed on page run.
   */
  public function run() {
    self::refreshCompaniesInSettings();
    CRM_Core_Session::setStatus('Companies list has been refreshed successfully.', '', 'success');
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/myob/settings'));
  }

  /**
   * Refresh companies list in settings.
   *
   * @return array
   */
  public static function refreshCompaniesInSettings() {
    $myobCompany = new CRM_Myob_Company();
    $companyFiles = $myobCompany->all();
    Civi::settings()->set('myob_companies_list_json', json_encode($companyFiles));
    return $companyFiles;
  }

  /**
   * Get companies list either from settings or cloud (Refreshed).
   * @param bool $refresh (Refresh the list in settings by fetching from cloud)
   * @return array|mixed
   */
  public static function getCompanies($refresh = FALSE) {
    $companyFiles = Civi::settings()->get('myob_companies_list_json');

    if ($refresh || (!$companyFiles)) {
      $companyFiles = self::refreshCompaniesInSettings();
    }
    else {
      $companyFiles = json_decode($companyFiles, TRUE);
    }

    return $companyFiles;
  }

}
