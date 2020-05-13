<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference

return array(
  0 => array(
    'name' => 'Civimyob Contact Push Job',
    'entity' => 'Job',
    'update' => 'never',
    'params' => array(
      'version' => 3,
      'name' => 'Civimyob Contact Push Job',
      'description' => 'Push updated contacts to Myob',
      'api_entity' => 'Civimyob',
      'api_action' => 'Contactspush',
      'run_frequency' => 'Always',
      'parameters' => '',
    ),
  ),
  1 => array(
    'name' => 'Civimyob Contact Pull Job',
    'entity' => 'Job',
    'update' => 'never',
    'params' => array(
      'version' => 3,
      'name' => 'Civimyob Contact Pull Job',
      'description' => 'Pull updated contacts from Myob',
      'api_entity' => 'Civimyob',
      'api_action' => 'Contactspull',
      'run_frequency' => 'Always',
      'parameters' => "start_date=yesterday",
    ),
  ),
  2 => array(
    'name' => 'Civimyob Invoice Push Job',
    'entity' => 'Job',
    'update' => 'never',
    'params' => array(
      'version' => 3,
      'name' => 'Civimyob Invoice Push Job',
      'description' => 'Push updated invoices to Myob',
      'api_entity' => 'Civimyob',
      'api_action' => 'Invoicespush',
      'run_frequency' => 'Always',
      'parameters' => '',
    ),
  ),
  3 => array(
    'name' => 'Civimyob Invoice Pull Job',
    'entity' => 'Job',
    'update' => 'never',
    'params' => array(
      'version' => 3,
      'name' => 'Civimyob Invoice Pull Job',
      'description' => 'Pull updated invoices from Myob',
      'api_entity' => 'Civimyob',
      'api_action' => 'Invoicespull',
      'run_frequency' => 'Daily',
      'parameters' => '',
    ),
  ),
);
