<?php

class CRM_Myob_Company extends CRM_Myob_Base {

  private $httpConnector;

  public function __construct() {
    $this->httpConnector = $this->getHttpConnector();
  }

  /**
   * Library function to return list of all the companies.
   * @return array
   * @throws CRM_Extension_Exception
   */
  public function all() {
    $this->httpConnector->get($this->API_ACCOUNTRIGHT_URL, array(), $this->getDefaultAuthHeaders());
    if (!$this->httpConnector->isValidResponse()) {
      return $this->httpConnector->returnInvalidResponse();
    }

    return $this->httpConnector->getResponseContent();
  }

}
