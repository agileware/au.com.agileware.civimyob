<?php

class CRM_Myob_Account extends CRM_Myob_Base {

  private $httpConnector;

  public function __construct() {
    $this->httpConnector = $this->getHttpConnector();
  }

  /**
   * Library function to fetch list of all accounts from MYOB Cloud.
   * @return array|mixed
   * @throws CRM_Extension_Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function all() {
    $this->httpConnector->get($this->getAccountListAPIEndPoint(), array(), $this->getDefaultAuthHeaders());
    if (!$this->httpConnector->isValidResponse()) {
      return $this->httpConnector->returnInvalidResponse();
    }

    return $this->httpConnector->getResponseContent();
  }

  /**
   * Method to return Account API end point.
   * @return string
   */
  public function getAccountListAPIEndPoint() {
    return $this->getAPICompanyURL() . 'GeneralLedger/Account/';
  }

}
