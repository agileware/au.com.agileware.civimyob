<?php

class CRM_Myob_Invoice extends CRM_Myob_Base {

  private $httpConnector;

  /**
   * Library function to push the given invoice.
   * @param $invoice
   * @return array
   * @throws CRM_Extension_Exception
   */
  public function push($invoice) {
    $this->httpConnector = $this->getHttpConnector();
    if (isset($invoice['UID']) && !empty($invoice['UID'])) {
      $this->httpConnector->put($this->getInoicePushAPIEndPoint() . $invoice['UID'], json_encode($invoice), $this->getDefaultPushHeaders());
    }
    else {
      $this->httpConnector->post($this->getInoicePushAPIEndPoint(), json_encode($invoice), $this->getDefaultPushHeaders());
    }

    if (!$this->httpConnector->isValidResponse()) {
      return $this->httpConnector->returnInvalidResponse();
    }

    $location = ($this->httpConnector->getResponseHeaders())['Location'];
    $invoiceUID = $this->getInvoiceUIDFromLocationHeader($location);

    return $this->returnValidResponse("Invoice has been pushed successfully.", array(
      'invoiceUID' => $invoiceUID,
      'location'   => $location,
    ));
  }

  /**
   * Get invoice UID from given location header.
   * @param $location
   * @return mixed
   */
  private function getInvoiceUIDFromLocationHeader($location) {
    $location = explode("/", $location[0]);
    return $location[count($location) - 1];
  }

  /**
   * Get invoice PUSH API end point.
   * @return string
   */
  public function getInoicePushAPIEndPoint() {
    return $this->getAPICompanyURL() . 'Sale/Invoice/Service/';
  }

  /**
   * Library function to pull a specific invoice by given UID.
   * @param $UID
   * @return array
   * @throws CRM_Extension_Exception
   */
  public function pull($UID) {
    $this->httpConnector = $this->getHttpConnector();
    $this->httpConnector->get($this->getInoicePushAPIEndPoint() . $UID, array(), $this->getDefaultAuthHeaders());
    if (!$this->httpConnector->isValidResponse()) {
      return $this->httpConnector->returnInvalidResponse();
    }

    return $this->httpConnector->getResponseContent();
  }

  /**
   * Pull all updated invoices by given date & time.
   * Invoices will be updated on or after given date & time value.
   *
   * @param $pullDateTime
   * @return array
   * @throws CRM_Extension_Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function pullUpdates($pullDateTime) {
    $this->httpConnector = $this->getHttpConnector();
    $this->httpConnector->get($this->getInoicePushAPIEndPoint(), array(
      '$filter' => "Date ge datetime'" . $pullDateTime . "'",
    ), $this->getDefaultAuthHeaders());
    if (!$this->httpConnector->isValidResponse()) {
      return $this->httpConnector->returnInvalidResponse();
    }

    $response = $this->httpConnector->getResponseContent();
    return $response['Items'];
  }

}
