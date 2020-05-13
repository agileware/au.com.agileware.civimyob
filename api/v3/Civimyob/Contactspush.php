<?php
use CRM_Civimyob_ExtensionUtil as E;

/**
 * Civimyob.Contactspush API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_civimyob_Contactspush_spec(&$spec) {

}

/**
 * Civimyob.Contactspush API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civimyob_Contactspush($params) {
  $options = _civicrm_api3_get_options_from_params($params);
  $myobContacts = new CRM_Civimyob_Contacts();
  $pushOutput = $myobContacts->push($options['limit']);
  return civicrm_api3_create_success($pushOutput, $params, 'Civimyob', 'Contactspush');
}
