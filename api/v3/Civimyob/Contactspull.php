<?php
use CRM_Civimyob_ExtensionUtil as E;

/**
 * Civimyob.Contactspull API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_civimyob_Contactspull_spec(&$spec) {
  $spec['start_date_time'] = array(
    'api.default' => '',
    'type' => CRM_Utils_Type::T_TIMESTAMP,
    'name' => 'start_date_time',
    'title' => 'Sync Start Date & Time',
    'description' => 'datetime to start pulling from',
  );
}

/**
 * Civimyob.Contactspull API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civimyob_Contactspull($params) {
  $myobContacts = new CRM_Civimyob_Contacts();
  $pushOutput = $myobContacts->pull($params);
  return civicrm_api3_create_success($pushOutput, $params, 'Civimyob', 'Contactspull');
}
