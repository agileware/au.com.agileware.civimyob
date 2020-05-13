<?php

class CRM_Myob_Auth extends CRM_Myob_Base {
  private $apiKey;
  private $apiSecret;
  private $redirectUri;
  private $httpConnector;

  public function __construct($apiKey, $apiSecret, $redirectUri) {
    $this->apiKey = $apiKey;
    $this->apiSecret = $apiSecret;
    $this->redirectUri = $redirectUri;
    $this->httpConnector = $this->getHttpConnector();
  }

  /**
   * Library function to refresh the access token.
   * @param $refreshToken
   * @return array
   */
  public function getRefreshedAccessToken($refreshToken) {
    $this->httpConnector->post($this->API_AUTH_BASE_URL, array(
      'client_id'     => $this->apiKey,
      'client_secret' => $this->apiSecret,
      'grant_type'    => 'refresh_token',
      'refresh_token' => $refreshToken,
    ), $this->getDefaultTokenHeaders());
    return $this->handleHttpResponse($this->httpConnector);
  }

  /**
   * Library function to get the access token.
   * @param $accessCode
   * @return array
   */
  public function getAccessToken($accessCode) {
    $this->httpConnector->post($this->API_AUTH_BASE_URL, array(
      'client_id'     => $this->apiKey,
      'client_secret' => $this->apiSecret,
      'grant_type'    => 'authorization_code',
      'code'          => $accessCode,
      'redirect_uri'  => $this->redirectUri,
      'scope'         => 'CompanyFile',
    ), $this->getDefaultTokenHeaders());
    return $this->handleHttpResponse($this->httpConnector);
  }

}
