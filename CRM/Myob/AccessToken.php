<?php

class CRM_Myob_AccessToken {

  /**
   * Get the access token to access MYOB APIs.
   *
   * @return mixed
   * @throws CRM_Extension_Exception
   */
  public static function get() {
    $codes = CRM_Myob_AuthCodes::get();
    $accessToken = $codes['access_token'];
    if (self::isAccessTokenExpired($codes['expiry_date'])) {
      $auth = new CRM_Myob_Auth($codes['api_key'], $codes['api_secret'], CRM_Civimyob_Form_Settings::getFormattedRedirectUriForRequest(CRM_Civimyob_Form_Settings::getRedirectURL()));
      $data = $auth->getRefreshedAccessToken($codes['refresh_token']);
      if (!$data['status']) {
        throw new CRM_Extension_Exception(implode("\n", $data['errors']));
      }
      else {
        $currentDateTime = new DateTime();
        $currentDateTime->modify("+" . $data['response']['expires_in'] . " seconds");

        Civi::settings()->set('myob_access_token', $data['response']['access_token']);
        Civi::settings()->set('myob_token_expiry', $currentDateTime->format("Y-m-d H:i:s"));

        $accessToken = $data['response']['access_token'];
      }
    }
    return $accessToken;
  }

  /**
   * Check if MYOB access token is expired or not by given expiry date.
   * @param $expiryDate
   * @return bool
   */
  private static function isAccessTokenExpired($expiryDate) {
    $expiryDate = new DateTime(date("Y-m-d H:i:s", strtotime($expiryDate)));
    $now = new DateTime();
    $now->modify("-2 minutes");
    return ($expiryDate <= $now);
  }

}
