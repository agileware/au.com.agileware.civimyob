<?php

class CRM_Myob_Contact extends CRM_Myob_Base {

  private $httpConnector;

  /**
   * Library function to push the given contact.
   * @param $contact
   * @return array
   * @throws CRM_Extension_Exception
   */
  public function push($contact) {
    $this->httpConnector = $this->getHttpConnector();
    $this->httpConnector->post($this->getContactPushAPIEndPoint(), json_encode($contact), $this->getDefaultPushHeaders());
    if (!$this->httpConnector->isValidResponse()) {
      return $this->httpConnector->returnInvalidResponse();
    }
    $location = ($this->httpConnector->getResponseHeaders())['Location'];
    $contactUID = $this->getContactUIDFromLocationHeader($location);

    return $this->returnValidResponse("Contact has been pushed successfully.", array(
      'contactUID' => $contactUID,
      'location'   => $location,
    ));
  }

  /**
   * Get contact PUSH API end point.
   * @return string
   */
  public function getContactPushAPIEndPoint() {
    return $this->getAPICompanyURL() . 'Contact/Customer/';
  }

  /**
   * Get contact UID from given location header.
   * @param $location
   * @return mixed
   */
  private function getContactUIDFromLocationHeader($location) {
    $location = explode("/", $location[0]);
    return $location[count($location) - 1];
  }

  /**
   * Get row version of a contact given UID.
   * @param $UID
   * @return array
   */
  public function getRowVersionByUID($UID) {
    $contact = $this->pull($UID);
    if (isset($contact['status']) && !$contact['status']) {
      return $this->returnInvalidResponse("Filed to fetch contact.");
    }
    return $contact['RowVersion'];
  }

  /**
   * Library function to pull a specific contact by given UID.
   * @param $UID
   * @return array
   * @throws CRM_Extension_Exception
   */
  public function pull($UID) {
    $this->httpConnector = $this->getHttpConnector();
    $this->httpConnector->get($this->getContactPushAPIEndPoint() . $UID, array(), $this->getDefaultAuthHeaders());
    if (!$this->httpConnector->isValidResponse()) {
      return $this->httpConnector->returnInvalidResponse();
    }

    return $this->httpConnector->getResponseContent();
  }

  /**
   * Pull all updated contacts by given date & time.
   * Contacts will be updated on or after given date & time value.
   *
   * @param $pullDateTime
   * @return array
   * @throws CRM_Extension_Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function pullUpdates($pullDateTime) {
    $this->httpConnector = $this->getHttpConnector();
    $this->httpConnector->get($this->getContactPushAPIEndPoint(), array(
      '$filter' => "LastModified ge datetime'" . $pullDateTime . "'",
    ), $this->getDefaultAuthHeaders());
    if (!$this->httpConnector->isValidResponse()) {
      return $this->httpConnector->returnInvalidResponse();
    }

    $response = $this->httpConnector->getResponseContent();
    return $response['Items'];
  }

}
