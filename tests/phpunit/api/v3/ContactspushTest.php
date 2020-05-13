<?php

use Civi\Test\HeadlessInterface;

/**
 * @group headless
 */
class api_v3_ContactspushTest extends api_v3_BaseTestCase {

  public function setUp() {
    parent::setUp();
  }
  public function tearDown() {
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_account_contact WHERE 1;");
  }

  public function setUpHeadless() {

  }

  /**
   * Test that a contact is added into queue on contact creation.
   */
  public function testContactAddedInQueue() {
    $contactId = $this->individualCreate();
    $this->callAPISuccess('account_contact', 'getsingle', array(
      'contact_id' => $contactId,
      'plugin'     => getAccountsyncPluginName(),
    ));
  }

  /**
   * Test that a contact is pushed to Myob after creation.
   */
  public function testContactPushedToMyob() {
    $this->createIndividualOnMyob();
  }

  /**
   * Test that a contact is not pushed to Myob if we don't have enough settings configured.
   */
  public function testContactPushFailToMyob() {
    $this->individualCreate();
    $this->assertFailPush();
  }

  /**
   * Test that a multiple contasts are pushed to Myob after creation in single call.
   */
  public function testMultipleContactPushedToMyob() {
    $this->individualCreate();
    $this->individualCreate();

    $response = $this->executeContactsPush();
    $this->assertEquals(TRUE, $response['status'], 'Status in response is not valid.');
    $this->assertEquals(2, $response['success'], 'Two contacts were not pushed to Myob.');
  }

  /**
   * Test that a contact is pushed to Myob after update.
   */
  public function testContactUpdatePushedToMyob() {
    $contactId = $this->createIndividualOnMyob();
    $this->individualCreate(array(
      'first_name' => 'Updated',
      'last_name'  => 'CiviCRM Contact',
      'id'         => $contactId,
    ));

    $response = $this->executeContactsPush();
    $this->assertSingleContactPush($response);
  }

  /**
   * Test that a contact is not pushed to Myob after update if we don't have settings configured.
   */
  public function testContactUpdatePushFailToMyob() {
    $contactId = $this->createIndividualOnMyob();
    $this->individualCreate(array(
      'first_name' => 'Updated',
      'last_name'  => 'CiviCRM Contact',
      'id'         => $contactId,
    ));

    Civi::settings()->set('myob_api_key', 'FAKE_API_KEY');
    $response = $this->executeContactsPush();

    $this->assertEquals(FALSE, $response['status'], 'Status in response is not valid.');
    $this->assertEquals(1, $response['failed'], 'Single contact was pushed to Myob.');
  }

  /**
   * Create individual on Myob
   * @return int (CiviCRM Contact ID)
   */
  private function createIndividualOnMyob() {
    $contactId = $this->individualCreate();
    $response = $this->executeContactsPush();

    $this->assertSingleContactPush($response);
    return $contactId;
  }

  /**
   * Assert only one contact is pushed in given response.
   * @param $response
   */
  private function assertSingleContactPush($response) {
    $this->assertEquals(TRUE, $response['status'], 'Status in response is not valid.');
    $this->assertEquals(1, $response['success'], 'Single contact was not pushed to Myob.');
  }

  /**
   * Assert fail push with API call.
   */
  private function assertFailPush() {
    Civi::settings()->set('myob_api_key', 'FAKE_API_KEY');
    $this->callAPIFailure('Civimyob', 'contactspush', array());
  }

}
