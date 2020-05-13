<?php

/**
 * @group headless
 */
class api_v3_InvoicespushTest extends api_v3_BaseTestCase {

  public function setUp() {
    parent::setUp();
  }
  public function tearDown() {
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_account_contact WHERE 1;");
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_account_invoice WHERE 1;");
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_contribution WHERE 1;");
  }

  public function setUpHeadless() {

  }

  /**
   * Test that a invoice is added into queue on contribution creation.
   */
  public function testInvoiceAddedInQueue() {
    $response = $this->createContactAndContribution();

    $this->callAPISuccess('account_contact', 'getsingle', array(
      'contact_id' => $response['contact_id'],
      'plugin'     => getAccountsyncPluginName(),
    ));

    $this->callAPISuccess('account_invoice', 'getsingle', array(
      'contact_id' => $response['contribution_id'],
      'plugin'     => getAccountsyncPluginName(),
    ));
  }

  /**
   * Test that a invoice is pushed to Myob after creation.
   */
  public function testInvoicePushedToMyob() {
    $this->createContactAndContribution();
    $this->assertSingleInvoicePush();
  }

  /**
   * Test that a invoice is not pushed to Myob if the contact is not synced.
   */
  public function testInvoicePushFailForNonContactToMyob() {
    $this->createContactAndContribution();
    $response = $this->executeInvoicesPush();
    $this->assertInvoicePushFail($response);
  }

  /**
   * Test that a invoice is not pushed to Myob if we don't have enough settings configured.
   */
  public function testInvoicePushFailToMyob() {
    $this->createContactAndContribution();
    $this->executeContactsPush();
    Civi::settings()->set('myob_api_key', 'FAKE_API_KEY');
    $response = $this->executeInvoicesPush();
    $this->assertInvoicePushFail($response);
  }

  /**
   * Test that a multiple invoices are pushed to Myob after creation in single call.
   */
  public function testMultipleInvoicesPushedToMyob() {
    $response = $this->createContactAndContribution();

    $this->contributionCreate(array(
      'contact_id'   => $response['contact_id'],
      'trxn_id'      => rand(0, 10000),
      'invoice_id'   => rand(0, 10000),
    ));

    $this->executeContactsPush();
    $response = $this->executeInvoicesPush();

    $this->assertEquals(TRUE, $response['status'], 'Status in response is not valid.');
    $this->assertEquals(2, $response['success'], 'Two invoices were not pushed to Myob.');
  }

  /**
   * Test that a invoice is pushed to Myob after update.
   */
  public function testInvoiceUpdatePushedToMyob() {
    $contributionResponse = $this->createContactAndContribution();
    $this->assertSingleInvoicePush();

    $this->contributionCreate(array(
      'id'                     => $contributionResponse['contribution_id'],
      'total_amount'           => 200.00,
    ));

    $this->assertSingleInvoicePush();
  }

  /**
   * Test that a invoice is not pushed to Myob after update if we don't have settings configured.
   */
  public function testInvoiceUpdatePushFailToMyob() {
    $contributionResponse = $this->createContactAndContribution();
    $this->assertSingleInvoicePush();
    $this->contributionCreate(array(
      'id'                     => $contributionResponse['contribution_id'],
      'total_amount'           => 200.00,
    ));

    Civi::settings()->set('myob_api_key', 'FAKE_API_KEY');
    $response = $this->executeInvoicesPush();
    $this->assertInvoicePushFail($response);
  }

  /**
   * Assert single invoice push
   */
  private function assertSingleInvoicePush() {
    $this->executeContactsPush();
    $response = $this->executeInvoicesPush();

    $this->assertEquals(TRUE, $response['status'], 'Status in response is not valid.');
    $this->assertEquals(1, $response['success'], 'Single invoice was not pushed to Myob.');
  }

  /**
   * Execute invoice push using API and return the response.
   * @return array|int
   */
  private function executeInvoicesPush() {
    $response = $this->callAPISuccess('Civimyob', 'invoicespush', array());
    $response = $response['values'];
    return $response;
  }

  /**
   * Create contact and contribution.
   * @return array of contact_id and contribution_id
   */
  private function createContactAndContribution() {
    $contactId = $this->individualCreate();
    $contributionId = $this->contributionCreate(array('contact_id' => $contactId));

    return array(
      'contact_id' => $contactId,
      'contribution_id' => $contributionId,
    );
  }

  /**
   * Assert given number of invoices failed.
   * @param $response
   * @param int $invoiceCount
   */
  private function assertInvoicePushFail($response, $invoiceCount = 1) {
    $this->assertEquals(FALSE, $response['status'], 'Status in response is not valid.');
    $this->assertEquals(0, $response['success'], $invoiceCount . ' invoice was pushed to Myob.');
    $this->assertEquals($invoiceCount, $response['failed'], $invoiceCount . ' invoice was pushed to Myob.');
  }

}
