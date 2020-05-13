<?php

class CRM_Civimyob_Base {

  /**
   * Check if MYOB is configured in a way to push/pull the contacts to/from MYOB.
   * @return bool
   */
  public function hasAllRequirementsConfigured() {
    $requiredSettings = array(
      'myob_selected_taxcode_id',
      'myob_selected_freighttaxcode_id',
      'myob_api_key',
      'myob_selected_company_id',
      'myob_selected_account_id',
    );

    if (Civi::settings()->get('myob_is_sandbox')) {
      unset($requiredSettings[3]);
    }

    foreach ($requiredSettings as $requiredSetting) {
      $value = Civi::settings()->get($requiredSetting);
      if ($value == '') {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Return invalid response with status and errors key.
   *
   * @param $message
   * @return array
   */
  public function returnInvalidResponse($message) {
    return array(
      'status'  => FALSE,
      'errors' => array($message),
    );
  }

  /**
   * Utility function get date & time in given params. If nothing provided then return current date & time.
   * @param $params
   * @return bool|DateTime|string
   */
  public function getPullDateTimeFromParams($params) {
    $currentDateTime = (new DateTime())->modify("-7 days")->format("YmdHis");
    $pullDateTime = (isset($params['start_date_time']) && $params['start_date_time'] != '') ? $params['start_date_time'] : $currentDateTime;
    $pullDateTime = DateTime::createFromFormat('YmdHis', $pullDateTime);
    $pullDateTime = $pullDateTime->format("Y-m-d\TH:i:s");

    return $pullDateTime;
  }

}
