<?php

class CRM_Myob_Base {

  private $API_BASE_URL = "https://ar1.api.myob.com/accountright/";
  protected $API_AUTH_BASE_URL = 'https://secure.myob.com/oauth2/v1/authorize/';
  protected $API_ACCOUNTRIGHT_URL = 'https://api.myob.com/accountright';

  /**
   * Get default auth headers for API operations.
   * @return array
   * @throws CRM_Extension_Exception
   */
  public function getDefaultAuthHeaders() {
    $accessToken = CRM_Myob_AccessToken::get();
    $codes = CRM_Myob_AuthCodes::get();

    return array(
      'x-myobapi-key' => $codes['api_key'],
      'x-myobapi-version' => 'v2',
      'Accept-Encoding' => 'gzip,deflate',
      'Authorization' => "Bearer " . $accessToken,
    );
  }

  /**
   * Get default push headers for Create/Update operations.
   * @return array
   * @throws CRM_Extension_Exception
   */
  public function getDefaultPushHeaders() {
    $headers = $this->getDefaultAuthHeaders();
    $headers['Content-Type'] = 'application/json';
    return $headers;
  }

  /**
   * Handel http Response.
   * @param $httpConnector
   * @return array
   */
  protected function handleHttpResponse($httpConnector) {
    if (!$httpConnector->isValidResponse()) {
      return array(
        'status' => FALSE,
        'errors' => $httpConnector->getResponseErrors(),
      );
    }
    else {
      return array(
        'status' => TRUE,
        'response' => $httpConnector->getResponseContent(),
      );
    }
  }

  /**
   * Get default token headers
   * @return array
   */
  protected function getDefaultTokenHeaders() {
    return array(
      'Content-Type' => 'application/x-www-form-urlencoded',
    );
  }

  /**
   * Get API Base URI by given Company ID.
   * @param $companyId
   * @return string
   */
  protected function getAPIBaseURL($companyId) {
    return $this->API_BASE_URL . $companyId .  "/";
  }

  /**
   * Get instance of HTTP Connector.
   *
   * @return CRM_Myob_HttpConnector
   */
  protected function getHttpConnector() {
    return new CRM_Myob_HttpConnector(new CRM_Myob_JsonResponseParser());
  }

  /**
   * Get API Company URL for all non-company related operations.
   * @return string
   */
  protected function getAPICompanyURL() {
    return $this->getAPIBaseURL(CRM_Civimyob_Helper_Settings::getSelectedCompanyId());
  }

  /**
   * Return valid response using message and extra data.
   * @param $message
   * @param array $data
   * @return array
   */
  protected function returnValidResponse($message, $data = array()) {
    $response = array(
      'status'  => TRUE,
      'message' => $message,
    );
    $response = array_merge($response, $data);
    return $response;
  }

  /**
   * Return invalid response using message and extra data.
   *
   * @param $message
   * @param array $data
   * @return array
   */
  protected function returnInvalidResponse($message, $data = array()) {
    $response = array(
      'status'  => FALSE,
      'message' => $message,
    );
    $response = array_merge($response, $data);
    return $response;
  }

}
