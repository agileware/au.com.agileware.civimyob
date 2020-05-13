<?php

class CRM_Myob_AuthCodes {

  /**
   * Get list of codes from settings.
   *
   * @return array
   */
  public static function get() {
    return array(
      'is_sandbox'    => Civi::settings()->get('myob_is_sandbox'),
      'api_key'       => Civi::settings()->get('myob_api_key'),
      'access_token'  => Civi::settings()->get('myob_access_token'),
      'refresh_token' => Civi::settings()->get('myob_refresh_token'),
      'expiry_date'   => Civi::settings()->get('myob_token_expiry'),
      'api_secret'    => Civi::settings()->get('myob_api_secret'),
      'login_type'    => Civi::settings()->get('myob_login_type'),
      'redirect_uri'  => CRM_Civimyob_Form_Settings::getRedirectURL(),
    );
  }

}
