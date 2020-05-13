<?php

class CRM_Myob_JsonResponseParser implements CRM_Myob_ResponseParser {

  /**
   * Method to parse given JSON content(String) in Array.
   * @param $content
   * @return mixed
   */
  public function parse($content) {
    return json_decode($content, TRUE);
  }

}
