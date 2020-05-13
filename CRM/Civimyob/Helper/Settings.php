<?php

class CRM_Civimyob_Helper_Settings {

  /**
   * Get selected company id from settings.
   *
   * @return mixed
   */
  public static function getSelectedCompanyId() {
    return Civi::settings()->get('myob_selected_company_id');
  }

  /**
   * Get selected account id from settings.
   *
   * @return mixed
   */
  public static function getSelectedAccountId() {
    return Civi::settings()->get('myob_selected_account_id');
  }

  /**
   * Get selected taxcode id from settings.
   *
   * @return mixed
   */
  public static function getSelectedTaxCodeId() {
    return Civi::settings()->get('myob_selected_taxcode_id');
  }

  /**
   * Get selected freight taxcode id from settings.
   *
   * @return mixed
   */
  public static function getSelectedFreightTaxCodeId() {
    return Civi::settings()->get('myob_selected_freighttaxcode_id');
  }

}
