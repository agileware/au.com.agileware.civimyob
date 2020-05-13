<?php
use CRM_Civimyob_ExtensionUtil as E;

/**
 * Civimyob.Invoicespull API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_civimyob_Invoicespull_spec(&$spec) {
  $spec['contribution_id'] = array(
    'api.default' => '',
    'type' => CRM_Utils_Type::T_INT,
    'name' => 'contribution_id',
    'title' => 'Contribution ID',
    'FKApiName'    => 'Contribution',
    'FKClassName'  => 'CRM_Elections_DAO_Contribution',
    'description' => 'check specific contribution',
  );
}

/**
 * Civimyob.Invoicespull API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civimyob_Invoicespull($params) {
  $options = _civicrm_api3_get_options_from_params($params);
  $myobInvoices = new CRM_Civimyob_Invoices();
  $pullOutput = $myobInvoices->pull($params, $options['limit']);
  return civicrm_api3_create_success($pullOutput, $params, 'Civimyob', 'Invoicespull');
}
