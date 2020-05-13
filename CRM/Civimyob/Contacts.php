<?php

class CRM_Civimyob_Contacts extends CRM_Civimyob_Base {

  /**
   * Pull the updates from MYOB and store in AccountSync contact table.
   * Pull the contacts which has been updated on or after given date & time if no date & time is given
   * then consider the current date & time.
   *
   * @param $params
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  public function pull($params) {
    $pullDateTime = $this->getPullDateTimeFromParams($params);
    $contactService = new CRM_Myob_Contact();
    $response = $contactService->pullUpdates($pullDateTime);
    if (isset($response['status']) && !$response['status']) {
      return $response;
    }

    $updatedContacts = $response;
    $contactPullErrors = array();

    foreach ($updatedContacts as $updatedContact) {
      $UID = $updatedContact['UID'];
      $updatedContact['location'] = $updatedContact['URI'];

      $accountSyncContactParams = array(
        'plugin'                => getAccountsyncPluginName(),
        'accounts_contact_id'   => $UID,
        'accounts_needs_update' => 0,
        'accounts_data'          => json_encode($updatedContact),
        'error_data'            => NULL,
      );

      $accountSyncContact = civicrm_api3("account_contact", "get", array(
        'accounts_contact_id'   => $UID,
        'sequential'            => 1,
        'plugin'                => getAccountsyncPluginName(),
      ));
      if ($accountSyncContact["count"]) {
        $accountSyncContact = $accountSyncContact['values'][0];
        $save = TRUE;
        CRM_Accountsync_Hook::accountPullPreSave('contact', $updatedContact, $save, $accountSyncContactParams);
        try {
          $modifiedDate = new DateTime();
          $accountSyncContact['accounts_modified_date'] = $modifiedDate->format("Y-m-d H:i:s");
          $accountSyncContactParams['id'] = $accountSyncContact['id'];

          civicrm_api3('account_contact', 'create', $accountSyncContactParams);
        }
        catch (CiviCRM_API3_Exception $e) {
          $contactPullErrors[] = ts('Failed to store ') . ' CiviCRM ID : ' . $accountSyncContact['contact_id'] . ' MYOB Id : (' . $accountSyncContact['accounts_contact_id'] . ' )'
              . ts(' with error ') . $e->getMessage()
              . ts('Contact Push failed');
        }
      }
    }

    if (count($contactPullErrors)) {
      return array(
        'message' => 'Not all contacts were saved',
        'errors'  => $contactPullErrors,
      );
    }
    else {
      return array(
        'message' => (count($updatedContacts)) ? 'All contacts were saved.' : 'No contacts to save.',
      );
    }
  }

  /**
   * Push all non-sync (ready to sync) contacts from Account sync table to MYOB.
   *
   * @param int $limit (Default to 25 and can be configured using API)
   * @return array (Message if errors if any)
   * @throws CiviCRM_API3_Exception
   */
  public function push($limit = 25) {
    if (!$this->hasAllRequirementsConfigured()) {
      return $this->returnInvalidResponse("MYOB extension is not configured properly.");
    }
    $contacts = civicrm_api3('account_contact', 'get', array(
      'accounts_needs_update' => 1,
      'api.contact.get'       => 1,
      'plugin'                => getAccountsyncPluginName(),
      'contact_id'            => array('IS NOT NULL' => 1),
      'connector_id'          => 0,
      'options'               => array(
        'limit' => $limit,
      ),
    ));

    $contacts = $contacts['values'];

    $contactService = new CRM_Myob_Contact();
    $contactPushErrors = array();

    $failedPush = 0;
    $successPush = 0;

    foreach ($contacts as $contact) {
      try {
        $myobContact = self::getMyobContactFromAccountsync($contact);
        if ($myobContact === FALSE) {
          $contactPushErrors[] = "Failed to fetch exisiting contact from MYOB . CiviCRM ID : " . $contact['contact_id'] . ' MYOB Id : (' . $contact['accounts_contact_id'] . ' )';
          $this->addErrorDataInAccountContact(array("Failed to fetch exisiting contact from MYOB"), $contact);
          $failedPush++;
        }
        else {
          $response = $contactService->push($myobContact);
          $modifyResponse = $this->modifyAccountContactBasedOnResponse($response, $contact, $myobContact);
          if ($modifyResponse !== TRUE) {
            $contactPushErrors[] = $modifyResponse;
            $failedPush++;
          }
          else {
            $successPush++;
          }
        }

        civicrm_api3('account_contact', 'create', $contact);
      }
      catch (CiviCRM_API3_Exception $e) {
        $contactPushErrors[] = ts('Failed to push ') . ' CiviCRM ID : ' . $contact['contact_id'] . ' MYOB Id : (' . $contact['accounts_contact_id'] . ' )'
            . ts(' with error ') . $e->getMessage() . print_r($response['errors'], TRUE)
            . ts('Contact Push failed');
        $failedPush++;
      }
    }

    if (count($contactPushErrors)) {
      return array(
        'message' => 'Not all contacts were saved',
        'errors'  => $contactPushErrors,
        'status'  => FALSE,
        'failed'  => $failedPush,
        'success' => $successPush,
      );
    }
    else {
      return array(
        'status'  => TRUE,
        'failed'  => $failedPush,
        'success' => $successPush,
        'message' => (count($contacts)) ? 'All contacts were saved.' : 'No contacts to save.',
      );
    }
  }

  /**
   * Modify the values in AccountContact record according to the received response from MYOB.
   * @param $response
   * @param $contact
   */
  private function modifyAccountContactBasedOnResponse($response, &$contact, $myobContact) {
    $hasErrors = FALSE;
    if ($response['status']) {
      $contact['error_data'] = 'NULL';
      $contact['accounts_contact_id'] = $response['contactUID'];
      $modifiedDate = new DateTime();
      $myobContact['location'] = $response['location'];
      $myobContact['UID'] = $response['contactUID'];
      $contact['accounts_data'] = json_encode($myobContact);
      $contact['accounts_modified_date'] = $modifiedDate->format("Y-m-d H:i:s");
      $contact['accounts_needs_update'] = 0;
    }
    else {
      $this->addErrorDataInAccountContact($response['errors'], $contact);
      $hasErrors = TRUE;
    }
    unset($contact['last_sync_date']);

    if ($hasErrors) {
      return $response['errors'];
    }

    return TRUE;
  }

  /**
   * Add given errors data in AccountContact record.
   * @param $errors
   * @param $contact
   */
  private function addErrorDataInAccountContact($errors, &$contact) {
    $contact['error_data'] = json_encode($errors);
    if (gettype($contact['accounts_data']) == 'array') {
      $contact['accounts_data']  = json_encode($contact['accounts_data']);
    }
  }

  /**
   * Map MYOB contact details to CiviCRM contact details array.
   * @param $myobContact
   * @return array
   */
  public static function getCiviCRMContactFromMYOB($myobContact) {
    $contact = array();

    if ($myobContact['IsIndividual']) {
      $contact['first_name'] = $myobContact['FirstName'];
      $contact['last_name'] = $myobContact['LastName'];
    }
    else {
      $contact['household_name'] = $myobContact['CompanyName'];
      $contact['organization_name'] = $myobContact['CompanyName'];
    }

    if (isset($myobContact['Addresses']) && is_array($myobContact['Addresses']) && count($myobContact['Addresses'])) {
      $addresses = $myobContact['Addresses'];
      foreach ($addresses as $address) {
        $contact['street_address'] = $address['Street'];
        $contact['city'] = $address['City'];
        $contact['state_province_name'] = $address['State'];
        $contact['postal_code'] = $address['PostCode'];
        $contact['country'] = $address['Country'];
        $contact['display_name'] = $address['ContactName'];
        $contact['phone'] = $address['Phone1'];
        $contact['email'] = $address['Email'];
        $contact['website'] = $address['Website'];
      }
    }

    return $contact;
  }

  /**
   * Map CiviCRM contact details to Myob contact details array.
   * @param $contact
   * @return array|bool
   */
  public static function getMyobContactFromAccountsync($contact) {
    $myobContact = array();
    $myobContact['UID'] = isset($contact['accounts_contact_id']) ? $contact['accounts_contact_id'] : NULL;

    $contact = $contact['api.contact.get']['values'][0];

    if ($contact['contact_type'] == 'Individual') {
      $myobContact['IsIndividual'] = TRUE;
      $myobContact['FirstName'] = $contact['first_name'];
      $myobContact['LastName'] = $contact['last_name'];
    }
    else {
      $myobContact['IsIndividual'] = FALSE;
      $companyNameKey = "organization_name";
      if ($contact['contact_type'] == "Household") {
        $companyNameKey = "household_name";
      }
      $myobContact['CompanyName'] = $contact[$companyNameKey];
    }

    $myobContact['IsActive']  = TRUE;

    $myobContact['Addresses'] = array();
    $addressOne = array();

    if (isset($contact['state_province_name']) && !empty($contact['state_province_name'])) {
      $addressOne = array(
        'Location'     => 1,
        'Street'       => $contact['street_address'],
        'City'         => $contact['city'],
        'State'        => $contact['state_province_name'],
        'PostCode'     => $contact['postal_code'],
        'Country'      => $contact['country'],
        'ContactName'  => $contact['display_name'],
      );
    }

    if (isset($contact['phone']) && !empty($contact['phone'])) {
      $addressOne['Phone1'] = $contact['phone'];
    }

    if (isset($contact['email']) && !empty($contact['email'])) {
      $addressOne['Email'] = $contact['email'];
    }

    if (isset($contact['website']) && !empty($contact['website'])) {
      $addressOne['Website'] = $contact['website'];
    }

    if (!empty($addressOne)) {
      $myobContact['Addresses'][] = $addressOne;
    }
    else {
      unset($myobContact['Addresses']);
    }

    $myobContact['SellingDetails'] = array(
      'SaleLayout' => 'Service',
      'TaxCode' => array(
        'UID' => CRM_Civimyob_Helper_Settings::getSelectedTaxCodeId(),
      ),
      'FreightTaxCode' => array(
        'UID' => CRM_Civimyob_Helper_Settings::getSelectedFreightTaxCodeId(),
      ),
    );

    if ($myobContact['UID'] === NULL) {
      unset($myobContact['UID']);
    }
    else {
      $contactService = new CRM_Myob_Contact();
      $rowVersion = $contactService->getRowVersionByUID($myobContact['UID']);
      if (is_array($rowVersion) && isset($rowVersion['status']) && !$rowVersion['status']) {
        return FALSE;
      }
      $myobContact['RowVersion'] = $rowVersion;
    }

    return $myobContact;
  }

}
