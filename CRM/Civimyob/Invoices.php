<?php

class CRM_Civimyob_Invoices extends CRM_Civimyob_Base {

  /**
   * Pull the updates from MYOB and store in AccountSync invoice table.
   * Pull all the non-cancelled invoices from Myob and update the civicrm contribution accordingly.
   * If Invoice is closed, update the contribution to cancelled if invoice due amount is paid update
   * the contribution status to completed.
   *
   * @param $params
   * @param int $limit
   * @return array
   * @throws CRM_Extension_Exception
   */
  public function pull($params, $limit = 0) {
    $invoices = $this->getInvoicesToPull($params, $limit);
    $invoiceService = new CRM_Myob_Invoice();

    $invoicePullErrors = array();
    foreach ($invoices as $invoice) {
      $invoiceId = $invoice['accounts_invoice_id'];
      $accountInvoice = $invoiceService->pull($invoiceId);
      if (isset($accountInvoice['status']) && !$accountInvoice['status']) {
        $invoicePullErrors[] = $accountInvoice['errors'];
      }
      else {
        $response = $this->saveInvoiceUpdate($accountInvoice, $invoice);
        if (!$response['status']) {
          $invoicePullErrors[] = $response['message'];
        }
      }
    }

    if (count($invoicePullErrors)) {
      return array(
        'message' => 'Not all invoices were saved.',
        'errors'  => $invoicePullErrors,
      );
    }
    else {
      return array(
        'message' => (count($invoices)) ? 'All invoices were saved.' : 'No invoices to save.',
      );
    }
  }

  /**
   * Save the invoice updated from MYOB in CiviCRM
   *
   * @param $accountInvoice
   * @param $invoice
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function saveInvoiceUpdate($accountInvoice, $invoice) {
    $contribution = self::getContributionById($invoice['contribution_id']);
    if ($contribution['count'] == 0) {
      return array(
        'status' => FALSE,
        'message' => 'Did not find the Contribution for Contribution ID: ' . $contribution['id'] . ' Invoice ID: ' . $invoice['accounts_invoice_id'],
      );
    }
    $contribution = $contribution['values'][0];

    $contributionStatusToUpdate = -1;

    $myobDueAmount = $accountInvoice['BalanceDueAmount'];
    if ($accountInvoice['Status'] != "Closed") {
      if ($myobDueAmount == 0 && $contribution['contribution_status_id'] != 1) {
        $contributionStatusToUpdate = 1;
      }
    }
    elseif ($accountInvoice['Status'] == 'Open') {
      if ($myobDueAmount == 0) {
        $contributionStatusToUpdate = 1;
      }
      else {
        $contributionStatusToUpdate = 3;
      }
    }
    else {
      $contributionStatusToUpdate = 7;
    }

    if ($contributionStatusToUpdate != -1) {
      $statusUpdate = CRM_Core_DAO::setFieldValue('CRM_Contribute_DAO_Contribution', $contribution['id'], 'contribution_status_id', $contributionStatusToUpdate, 'id');

      if (!$statusUpdate) {
        return array(
          'status' => FALSE,
          'message' => 'Contribution Status update filed for Contribution ID: ' . $contribution['id'] . ' Invoice ID: ' . $invoice['accounts_invoice_id'],
        );
      }

      $modifiedDate = new DateTime();
      $invoice['accounts_status_id'] = $contributionStatusToUpdate;
      $invoice['accounts_needs_update'] = 0;
      $invoice['accounts_data'] = json_encode($accountInvoice);
      $invoice['accounts_modified_date'] = $modifiedDate->format("Y-m-d H:i:s");

      civicrm_api3('AccountInvoice', 'create', $invoice);
    }

    return array(
      'status' => TRUE,
      'message' => 'Contribution updated for Contribution ID: ' . $contribution['id'] . ' Invoice ID: ' . $invoice['accounts_invoice_id'],
    );

  }

  /**
   * Get all the invoices to pull from MYOB.
   *
   * @param $params
   * @param $limit
   * @return mixed
   * @throws CiviCRM_API3_Exception
   */
  private function getInvoicesToPull($params, $limit) {
    $invoicePullParams = array(
      'plugin'              => getAccountsyncPluginName(),
      'accounts_invoice_id' => array('IS NOT NULL' => 1),
      'account_status_Id'   => array('NOT IN' => array(3)),
      'accounts_data'       => array('IS NOT NULL' => 1),
      'error_data'          => array('IS NULL' => 1),
      'options'             => array(
        'limit' => $limit,
      ),
    );
    if (isset($params['contribution_id']) && !empty($params['contribution_id'])) {
      $invoicePullParams['contribution_id'] = $params['contribution_id'];
    }

    $invoices = civicrm_api3('AccountInvoice', 'get', $invoicePullParams);

    return $invoices['values'];
  }

  /**
   * Push all non-sync (ready to sync) invoices from Account sync table to MYOB.
   *
   * @param int $limit
   * @return array
   * @throws CRM_Extension_Exception
   * @throws CiviCRM_API3_Exception
   */
  public function push($limit = 25) {
    if (!$this->hasAllRequirementsConfigured()) {
      return $this->returnInvalidResponse("MYOB extension is not configured properly.");
    }

    $invoices = civicrm_api3('account_invoice', 'get', array(
      'accounts_needs_update' => 1,
      'plugin'                => getAccountsyncPluginName(),
      'sequential'             => TRUE,
      'contribution_id'       => array('IS NOT NULL' => 1),
      'options'               => array(
        'limit' => $limit,
      ),
    ));

    $invoices = $invoices['values'];
    $invoicePushErrors = array();

    $invoiceService = new CRM_Myob_Invoice();

    $success = 0;
    $failed = 0;

    foreach ($invoices as $invoice) {
      $accountInvoice = self::getMyobInvoiceFromAccountSync($invoice);
      if ($accountInvoice['status']) {
        $response = $invoiceService->push($accountInvoice['invoice']);
        $modifyResponse = $this->modifyAccountInvoiceBasedOnResponse($response, $invoice, $accountInvoice);
        if ($modifyResponse !== TRUE) {
          $invoicePushErrors[] = $modifyResponse;
          $failed++;
        }
        else {
          $success++;
        }

        civicrm_api3('account_invoice', 'create', $invoice);
      }
      else {
        $invoicePushErrors[] = $accountInvoice['message'];
        $failed++;
      }
    }

    if (count($invoicePushErrors)) {
      return array(
        'message' => 'Not all invoices were saved',
        'errors'  => $invoicePushErrors,
        'success' => $success,
        'failed'  => $failed,
        'status'  => FALSE,
      );
    }
    else {
      return array(
        'message' => (count($invoices)) ? 'All invoices were saved.' : 'No invoices to save.',
        'success' => $success,
        'failed'  => $failed,
        'status'  => TRUE,
      );
    }
  }

  /**
   * Modify the values in AccountInvoice record according to the received response from MYOB.
   * @param $response
   * @param $invoice
   * @param $accountInvoice
   * @return bool
   */
  private function modifyAccountInvoiceBasedOnResponse($response, &$invoice, $accountInvoice) {
    $hasErrors = FALSE;

    if (!$response["status"]) {
      $hasErrors = TRUE;
      $invoice['error_data'] = json_encode($response["errors"]);
      if (isset($invoice['accounts_data']) && is_array($invoice['accounts_data'])) {
        $invoice['accounts_data'] = json_encode($invoice['accounts_data']);
      }
    }
    else {
      $contributionStatusId = $accountInvoice['contribution_status_id'];
      $accountInvoice = $accountInvoice['invoice'];
      $modifiedDate = new DateTime();
      $invoice['error_data'] = 'null';
      $invoice['accounts_invoice_id'] = $response['invoiceUID'];

      $accountInvoice['UID'] = $response['invoiceUID'];
      $accountInvoice['location'] = $response['location'];

      $invoice['accounts_data'] = json_encode($accountInvoice);
      $invoice['accounts_needs_update'] = 0;
      $invoice['accounts_status_id'] = $contributionStatusId;
      $invoice['accounts_modified_date'] = $modifiedDate->format("Y-m-d H:i:s");
    }

    unset($invoice['last_sync_date']);

    if ($hasErrors) {
      return $response['errors'];
    }

    return TRUE;
  }

  /**
   * Find the CiviCRM Contribution record by given contribution id.
   * @param $contributionId
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private static function getContributionById($contributionId) {
    $contribution = civicrm_api3("Contribution", "get", array(
      'id'              => $contributionId,
      'api.contact.get' => 1,
      'sequential'      => TRUE,
    ));
    return $contribution;
  }

  /**
   * Map CiviCRM contribution details to Myob invoice details array.
   * @param $accountSyncInvoice
   * @return array
   * @throws CRM_Extension_Exception
   * @throws CiviCRM_API3_Exception
   */
  public static function getMyobInvoiceFromAccountSync($accountSyncInvoice) {
    $contributionId = $accountSyncInvoice['contribution_id'];
    $accountInvoiceId = (isset($accountSyncInvoice['accounts_invoice_id'])) ? $accountSyncInvoice['accounts_invoice_id'] : '';

    $myobInvoice = array();
    if ($accountInvoiceId) {
      $myobInvoice['UID'] = $accountInvoiceId;
      $invoiceService = new CRM_Myob_Invoice();

      $myobCloudInvoicee = $invoiceService->pull($accountInvoiceId);

      if (is_array($myobCloudInvoicee) && isset($myobCloudInvoicee['status']) && !$myobCloudInvoicee['status']) {
        return array(
          'status'  => FALSE,
          'message' => $myobCloudInvoicee['errors'],
        );
      }
      $myobInvoice['RowVersion'] = $myobCloudInvoicee['RowVersion'];
      $myobInvoice['Terms'] = $myobCloudInvoicee['Terms'];
    }

    $contribution = self::getContributionById($contributionId);

    if ($contribution["count"]) {

      $contribution = $contribution['values'][0];
      if ($contribution['api.contact.get']['count'] == 0) {
        return array(
          'status'  => FALSE,
          'message' => 'Did not find the contribution contact.',
        );
      }

      $contact = $contribution['api.contact.get']['values'][0];

      $accountSyncContact = civicrm_api3('account_contact', 'get', array(
        'plugin'                => getAccountsyncPluginName(),
        'contact_id'            => $contact['id'],
        'accounts_contact_id'   => array('IS NOT NULL' => 1),
        'sequential'            => TRUE,
      ));

      if ($accountSyncContact['count'] == 0) {
        return array(
          'status'  => FALSE,
          'message' => 'Contact is not yet synced with Myob.',
        );
      }
      $accountSyncContact = $accountSyncContact['values'][0];

      $isContritionToBePushed = self::isInvoiceStatusAmongGiven($contribution['contribution_status_id'], array('pending', 'completed', 'partially paid'));
      $isContributionCancelled = self::isInvoiceStatusAmongGiven($contribution['contribution_status_id'], array('failed', 'cancelled'));

      if ($isContributionCancelled) {
        $myobInvoice['Status'] = 'Closed';
      }
      else {
        if ($isContritionToBePushed) {
          $lineItems = civicrm_api3('LineItem', 'get', [
            'sequential'      => 1,
            'contribution_id' => $contributionId,
          ]);

          $lineItems = $lineItems['values'];

          $paymentDetails = CRM_Contribute_BAO_Contribution::getPaymentInfo($contribution['id'], 'contribute', FALSE, TRUE);

          $total = $paymentDetails['total'];
          $paid = $paymentDetails['paid'];
          $due = $total - $paid;
          $totalTax = 0;

          if ($due > 0) {
            $myobInvoice['BalanceDueAmount'] = $due;
          }

          $myobInvoice['Lines'][] = array(
            "Type"        => "Header",
            "Description" => "Description",
          );

          foreach ($lineItems as $lineItem) {
            $myobInvoice['Lines'][] = array(
              "Type"        => "Transaction",
              "Description" => $lineItem['label'],
              "Total"       => $lineItem['line_total'],
              "Account"     => array(
                'UID'       => CRM_Civimyob_Helper_Settings::getSelectedAccountId(),
              ),
              "TaxCode"     => array(
                'UID'       => CRM_Civimyob_Helper_Settings::getSelectedTaxCodeId(),
              ),
            );
            if (isset($lineItem['tax_amount'])) {
              $totalTax += $lineItem['tax_amount'];
            }
          }

          $myobInvoice['Status'] = 'Open';
          $myobInvoice['ShipToAddress'] = $contact['street_address'];
          $myobInvoice['IsTaxInclusive'] = TRUE;
          $myobInvoice['Subtotal'] = $total;
          $myobInvoice['TotalTax'] = $totalTax;
          $myobInvoice['TotalAmount'] = $total;
        }
        else {
          return array(
            'status'  => FALSE,
            'message' => 'Contribution status is not valid to be pushed.',
          );
        }
      }

      $myobInvoice['Date'] = str_replace(" ", "T", $contribution['receive_date']);
      $myobInvoice['Customer']['UID'] = $accountSyncContact['accounts_contact_id'];
    }
    else {
      return array(
        'status'  => FALSE,
        'message' => 'Did not find the contribution.',
      );
    }

    return array(
      'status'                 => TRUE,
      'invoice'                => $myobInvoice,
      'contribution_status_id' => $contribution['contribution_status_id'],
    );
  }

  /**
   * Check if given contribution status is among the provided list of statues.
   * @param $contributionStatus
   * @param $statuesPool
   * @return bool
   * @throws CiviCRM_API3_Exception
   */
  private static function isInvoiceStatusAmongGiven($contributionStatus, $statuesPool) {

    $selectedContributionStatus = civicrm_api3('OptionValue', 'get', [
      'sequential'      => 1,
      'option_group_id' => "contribution_status",
      'value'           => $contributionStatus,
    ]);

    $isAmongGiven = FALSE;
    if ($selectedContributionStatus['count']) {
      $selectedContributionStatus = strtolower($selectedContributionStatus['values'][0]['name']);
      if (in_array($selectedContributionStatus, $statuesPool)) {
        $isAmongGiven = TRUE;
      }
    }

    return $isAmongGiven;
  }

}
