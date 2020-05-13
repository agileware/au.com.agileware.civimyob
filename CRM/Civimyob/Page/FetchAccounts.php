<?php
use CRM_Civimyob_ExtensionUtil as E;

class CRM_Civimyob_Page_FetchAccounts extends CRM_Core_Page {

  /**
   * CiviCRM function which is executed on page run.
   */
  public function run() {
    self::refreshAccountsInSettings();
    CRM_Core_Session::setStatus('Accounts list has been refreshed successfully.', '', 'success');
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/myob/settings'));
  }

  /**
   * Refresh accounts in settings.
   *
   * @return array
   */
  public static function refreshAccountsInSettings() {
    $myobAccount = new CRM_Myob_Account();
    $accounts = $myobAccount->all();
    if (!isset($accounts['status'])) {
      $accounts = self::getMinimalAccountsArray($accounts);
    }

    Civi::settings()->set('myob_accounts_list_json', json_encode($accounts));
    return $accounts;
  }

  private static function getMinimalAccountsArray($accounts) {
    $toSaveAccounts = array(
      'Items' => array(),
    );
    foreach ($accounts['Items'] as $accountItem) {
      $toSaveAccounts['Items'][] = array(
        'UID'  => $accountItem['UID'],
        'Name' => $accountItem['Name'],
      );
    }
    return $toSaveAccounts;
  }


  /**
   * Get accounts from settings or cloud (Refreshed)
   *
   * @param bool $refresh (Refresh accounts list in settings from cloud)
   * @return array|mixed
   */
  public static function getAccounts($refresh = FALSE) {
    $accounts = Civi::settings()->get('myob_accounts_list_json');

    if ($refresh || (!$accounts)) {
      $accounts = self::refreshAccountsInSettings();
    }
    else {
      $accounts = json_decode($accounts, TRUE);
    }

    return $accounts;
  }

}
