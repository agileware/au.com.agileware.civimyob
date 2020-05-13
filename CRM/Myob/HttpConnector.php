<?php

class CRM_Myob_HttpConnector {
  private $client;
  private $response;
  private $responseErrors;
  private $parser;

  public function __construct(CRM_Myob_ResponseParser $parser) {
    $this->client = new GuzzleHttp\Client();
    $this->response = NULL;
    $this->responseErrors = [];
    $this->parser = $parser;
  }

  /**
   * HTTP Post request method.
   * @param $url
   * @param array $data
   * @param array $headers
   */
  public function post($url, $data = array(), $headers = array()) {
    $requestData = array();
    $requestData['headers'] = $headers;
    if (is_array($data)) {
      $requestData['form_params'] = $data;
    }
    else {
      $requestData['body'] = $data;
    }
    try {
      $this->response = $this->client->post($url, $requestData);
    }
    catch (\GuzzleHttp\Exception\BadResponseException $e) {
      $this->handleResponseException($e);
    }
  }

  /**
   * HTTP Post request method.
   * @param $url
   * @param array $data
   * @param array $headers
   */
  public function put($url, $data = array(), $headers = array()) {
    $requestData = array();
    $requestData['headers'] = $headers;
    if (is_array($data)) {
      $requestData['form_params'] = $data;
    }
    else {
      $requestData['body'] = $data;
    }
    try {
      $this->response = $this->client->put($url, $requestData);
    }
    catch (\GuzzleHttp\Exception\BadResponseException $e) {
      $this->handleResponseException($e);
    }
  }

  /**
   * HTTP Get request method.
   * @param $url
   * @param array $data
   * @param array $headers
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function get($url, $data = array(), $headers = array()) {
    $requestData = array();
    $requestData['headers'] = $headers;
    $requestData['query'] = $data;

    try {
      $this->response = $this->client->request('GET', $url, $requestData);
    }
    catch (\GuzzleHttp\Exception\BadResponseException $e) {
      $this->handleResponseException($e);
    }
  }

  /**
   * Handle response exception from POST/GET requests.
   * @param \GuzzleHttp\Exception\BadResponseException $e
   */
  private function handleResponseException(\GuzzleHttp\Exception\BadResponseException $e) {
    $foundErrors = FALSE;
    $content = $e->getResponse()->getBody()->getContents();

    if ($content) {
      $content = $this->parser->parse($content);
      if (isset($content['error_description'])) {
        $this->responseErrors[] = $content['error_description'];
        $foundErrors = TRUE;
      }

      if (isset($content['error'])) {
        $this->responseErrors[] = $content['error'];
        $foundErrors = TRUE;
      }

      if (isset($content['Message'])) {
        $this->responseErrors[] = $content['Message'];
        $foundErrors = TRUE;
      }

      if (isset($content['Errors']) && count($content['Errors'])) {
        foreach ($content['Errors'] as $responseError) {
          $this->responseErrors[] = $responseError;
        }
        $foundErrors = TRUE;
      }
    }

    if (!$foundErrors) {
      $this->responseErrors[] = $e->getCode() . " : " . $e->getMessage();
    }
  }

  /**
   * Method to return invalid response.
   * @return array
   */
  public function returnInvalidResponse() {
    return array(
      'status' => FALSE,
      'errors' => $this->getResponseErrors(),
    );
  }

  /**
   * Method to return response errors.
   * @return array
   */
  public function getResponseErrors() {
    return $this->responseErrors;
  }

  /**
   * Method to check if last response was valid or not.
   * @return bool
   */
  public function isValidResponse() {
    if (!$this->response) {
      return FALSE;
    }

    if (!in_array($this->response->getStatusCode(), $this->getValidStatusCodes())) {
      return FALSE;
    }

    if (count($this->responseErrors)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Function to return response of GET requests.
   * @return mixed
   */
  public function getResponseContent() {
    return $this->parser->parse($this->response->getBody()->getContents());
  }

  /**
   * Function to return headers of response.
   * @return mixed
   */
  public function getResponseHeaders() {
    return $this->response->getHeaders();
  }

  /**
   * Method to define valid status codes.
   * @return array
   */
  private function getValidStatusCodes() {
    return array(
      200,
      201,
    );
  }

}
