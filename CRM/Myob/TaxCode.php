<?php

class CRM_Myob_TaxCode extends CRM_Myob_Base {

  private $httpConnector;

  public function __construct() {
    $this->httpConnector = $this->getHttpConnector();
  }

  /**
   * Library function to fetch list of all tax codes from MYOB Cloud.
   * @return array|mixed
   * @throws CRM_Extension_Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function all() {
    $this->httpConnector->get($this->getTaxCodeListAPIEndPoint(), array(), $this->getDefaultAuthHeaders());
    if (!$this->httpConnector->isValidResponse()) {
      return $this->httpConnector->returnInvalidResponse();
    }

    return $this->httpConnector->getResponseContent();
  }

  /**
   * Method to return TaxCode API end point.
   * @return string
   */
  public function getTaxCodeListAPIEndPoint() {
    return $this->getAPICompanyURL() . 'GeneralLedger/TaxCode/';
  }

}
