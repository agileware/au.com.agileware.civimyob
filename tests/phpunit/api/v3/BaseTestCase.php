<?php

use Civi\Test\HeadlessInterface;

/**
 * @group headless
 */
class api_v3_BaseTestCase extends CiviCaseTestCase implements HeadlessInterface {

  public function setUp() {
    $this->setDefaultSettings();
  }
  public function tearDown() {

  }

  public function setUpHeadless() {

  }

  /**
   * Set default settings to be used in Myob APIs.
   * @throws CiviCRM_API3_Exception
   */
  private function setDefaultSettings() {
    $settings = civicrm_api3('Setting', 'get', [
      'sequential' => 1,
    ]);
    $settings = $settings['values'][0];
    $settings['userFrameworkResourceURL'] = 'http://testframework.com/';

    $settings['account_sync_queue_contacts'] = array(
      'Contribution',
      'Contact',
    );
    $this->setMyobAPICredentials($settings);
    $this->callAPISuccess('Setting', 'create', $settings);
  }

  /**
   * Set Sandbox details of Myob for Accessing APIs.
   * @param $settings
   */
  private function setMyobAPICredentials(&$settings) {
    $settings['myob_is_sandbox'] = 1;

    // Set below Myob credentials and configuration before executing the tests.
    /* -------------------------------------------------------------------- */
    $settings['myob_username'] = "";
    $settings['myob_password'] = "";
    $settings['myob_api_key'] = "";
    $settings['myob_api_secret'] = "";
    $settings['myob_redirect_uri'] = "";

    $settings['myob_selected_taxcode_id'] = "";
    $settings['myob_selected_freighttaxcode_id'] = "";
    $settings['myob_selected_company_id'] = "";
    $settings['myob_selected_account_id'] = "";
    $settings['myob_selected_account_id'] = "";

    $settings['myob_access_token'] = "";
    $settings['myob_refresh_token'] = "";
    /* -------------------------------------------------------------------- */

    $dateTime = new DateTime();
    $settings['myob_token_expiry'] = $dateTime->modify("-1 day")->format("Y-m-d H:i:s");
  }

  /**
   * Execute contact push using API and return the response.
   * @return array|int
   */
  protected function executeContactsPush() {
    $response = $this->callAPISuccess('Civimyob', 'contactspush', array());
    $response = $response['values'];
    return $response;
  }

}
