<?php

use CRM_Civimyob_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Civimyob_Form_Settings extends CRM_Core_Form {
  private $_settingFilter = array('group' => 'civimyob');
  private $_submittedValues = array();
  private $_settings = array();
  private $accessToken = "";
  private $refreshToken = "";
  private $accessCode = "";
  private $selectedCompanyId = "";
  private $companyFiles = array();
  private $taxCodes = array();
  private $elementErrors = array();
  private $selectedTaxCodeId = "";
  private $selectedFreightTaxCodeId = "";
  private $accounts = array();

  /**
   * CiviCRM function to build the form.
   * @throws CRM_Core_Exception
   */
  public function buildQuickForm() {
    $this->getAccessCodeIfRequired();

    $this->accessToken = Civi::settings()->get('myob_access_token');
    $this->refreshToken = Civi::settings()->get('myob_refresh_token');
    $this->selectedCompanyId = Civi::settings()->get('myob_selected_company_id');
    $this->selectedTaxCodeId = Civi::settings()->get('myob_selected_taxcode_id');
    $this->selectedFreightTaxCodeId = Civi::settings()->get('myob_selected_freighttaxcode_id');

    $this->addFormElements();
    parent::buildQuickForm();
  }

  /**
   * Validates the form submission.
   *
   * @return bool
   */
  public function validate() {
    $values = $this->exportValues();
    $requiredElements = array(
      'myob_api_key',
      'myob_api_secret',
      'myob_redirect_uri',
    );
    foreach ($requiredElements as $requiredElement) {
      if (isset($values[$requiredElement]) && $values[$requiredElement] == '') {
        $this->_errors[$requiredElement] = 'This is a required field.';
      }
    }
    return parent::validate();
  }

  /**
   * Get Redirect URL
   * @return mixed|string
   */
  public static function getRedirectURL() {
    $redirectUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $redirectUrl .= CRM_Utils_System::url('civicrm/myob/settings');
    $redirectUrl = str_replace("%2F", "/", $redirectUrl);
    $redirectUrl = str_replace("amp;", "", $redirectUrl);
    return $redirectUrl;
  }

  /**
   * Handles the redirect login.
   * Process the received access code to get the Access token and Refresh token from MYOB.
   *
   * @throws CRM_Core_Exception
   */
  private function getAccessCodeIfRequired() {
    $this->accessCode = CRM_Utils_Request::retrieve('code', 'Text', $form, FALSE, '');
    if ($this->accessCode && $this->accessCode != '') {

      $values = $this->setDefaultValues();
      $myobAuth = new CRM_Myob_Auth($values['myob_api_key'], $values['myob_api_secret'], CRM_Civimyob_Form_Settings::getRedirectURL());
      $data = $myobAuth->getAccessToken($this->accessCode);
      if (!$data['status']) {
        $this->elementErrors = array_merge($this->elementErrors, array_map(['self', 'parseError'], $data['errors']));

        $this->_submittedValues['myob_access_token']  = '';
        $this->_submittedValues['myob_refresh_token'] = '';
        $this->_submittedValues['myob_token_expiry']  = '';
        $this->_submittedValues['myob_selected_company_json']  = '';
        $this->_submittedValues['myob_selected_company_id']  = '';

        $this->saveSettings();
      }
      else {
        $response = $data['response'];
        $currentDateTime = new DateTime();
        $currentDateTime->modify("+" . $response['expires_in'] . " seconds");

        $this->_submittedValues['myob_access_token']  = $response['access_token'];
        $this->_submittedValues['myob_refresh_token'] = $response['refresh_token'];
        $this->_submittedValues['myob_token_expiry']  = $currentDateTime->format("Y-m-d H:i:s");

        $this->saveSettings();
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/myob/settings'));
      }
    }
  }

  /**
   * Get formatted redirect URL for request.
   *
   * @param $redirectUrl
   * @return mixed
   */
  public static function getFormattedRedirectUriForRequest($redirectUrl) {
    $redirectUrl = str_replace("&", "%26", $redirectUrl);
    return $redirectUrl;
  }

  /**
   * Handles the form submission.
   */
  public function postProcess() {
    $this->_submittedValues = $this->exportValues();
    $hasAuthorised = $this->hasAuthorised();

    $this->saveSettings();
    if (!$hasAuthorised) {
      $redirectUrl = CRM_Civimyob_Form_Settings::getFormattedRedirectUriForRequest(CRM_Civimyob_Form_Settings::getRedirectURL());
      $authroiseUrl = 'https://secure.myob.com/oauth2/account/authorize?client_id=' . $this->_submittedValues["myob_api_key"] . '&redirect_uri=' . $redirectUrl . '&response_type=code&scope=CompanyFile la.global';
      CRM_Utils_System::redirect($authroiseUrl);
    }
    else {
      CRM_Utils_System::redirect($_SERVER['REQUEST_URI']);
    }

    parent::postProcess();
  }

  /**
   * Add form elements
   */
  public function addFormElements() {
    $settings = $this->getFormSettings();
    foreach ($settings as $name => $setting) {
      if (isset($setting['quick_form_type'])) {
        $add = 'add' . $setting['quick_form_type'];
        if ($add == 'addElement') {
          $this->$add($setting['html_type'], $name, $setting['title'], CRM_Utils_Array::value('html_attributes', $setting, array()));
        }
        elseif ($setting['html_type'] == 'Select') {
          $optionValues = array();
          if (!empty($setting['pseudoconstant']) && !empty($setting['pseudoconstant']['optionGroupName'])) {
            $optionValues = CRM_Core_OptionGroup::values($setting['pseudoconstant']['optionGroupName'], FALSE, FALSE, FALSE, NULL, 'name');
          }
          elseif (!empty($setting['pseudoconstant']) && !empty($setting['pseudoconstant']['callback'])) {
            $callBack = Civi\Core\Resolver::singleton()->get($setting['pseudoconstant']['callback']);
            $optionValues = call_user_func_array($callBack, $optionValues);
          }
          $this->add('select', $setting['name'], $setting['title'], $optionValues, FALSE, $setting['html_attributes']);
        }
        else {
          $this->$add($name, $setting['title']);
        }
      }
    }

    $submitButtonLabel = "Submit";
    $hasAuthorised = $this->hasAuthorised();
    if (!$hasAuthorised) {
      $submitButtonLabel = "Authorize";
    }
    else {
      $companies = array();
      $this->companyFiles = CRM_Civimyob_Page_FetchCompanies::getCompanies();
      if (!isset($this->companyFiles['status'])) {
        foreach ($this->companyFiles as $company) {
          $companies[] = array(
            'text' => $company['Name'],
            'id'   => $company['Id'],
          );
        }
      }
      else {
        $this->elementErrors = array_merge(
          $this->elementErrors,
          array_map(['self', 'parseError'], $this->companyFiles['errors'])
        );
      }
      $this->add(
        'select2',
        'myob_selected_company_id',
        'Company',
        $companies,
        FALSE,
        array('placeholder' => ' - Select Company - ')
      );
      $this->add(
        'select2',
        'myob_login_type',
        'Cloud File Login Type',
        CRM_Civimyob_Form_Settings::getLoginTypeOptions(),
        FALSE,
        array('placeholder' => ' - Select Login Type - ')
      );
    }

    if ($this->selectedCompanyId != '') {
      $taxCodes = array();
      $accounts = array();

      $this->taxCodes = CRM_Civimyob_Page_FetchTaxCodes::getTaxCodes();
      if (!isset($this->taxCodes['status'])) {
        $this->taxCodes = $this->taxCodes['Items'];
        foreach ($this->taxCodes as $taxCode) {
          $taxCodes[] = array(
            'text' => $taxCode['Code'],
            'id'   => $taxCode['UID'],
          );
        }
      }
      else {
        $this->elementErrors = array_merge($this->elementErrors, array_map(['self', 'parseError'], $this->taxCodes['errors']));
      }

      $this->accounts = CRM_Civimyob_Page_FetchAccounts::getAccounts();
      if (!isset($this->accounts['status'])) {
        $this->accounts = $this->accounts['Items'];
        foreach ($this->accounts as $account) {
          $accounts[] = array(
            'text' => $account['Name'],
            'id'   => $account['UID'],
          );
        }
      }
      else {
        $this->elementErrors = array_merge($this->elementErrors, array_map(['self', 'parseError'], $this->accounts['errors']));
      }

      $this->add('select2', 'myob_selected_taxcode_id', 'Tax Code', $taxCodes, TRUE, array('placeholder' => ' - Select TaxCode - '));
      $this->add('select2', 'myob_selected_freighttaxcode_id', 'Freight Tax Code', $taxCodes, TRUE, array('placeholder' => ' - Select Freight TaxCode - '));
      $this->add('select2', 'myob_selected_account_id', 'Account', $accounts, TRUE, array('placeholder' => ' - Select Account - '));
    }

    $hasSelectedCompany = TRUE;
    if ($this->selectedCompanyId == "" && $hasAuthorised) {
      $submitButtonLabel = "Select Company";
      $hasSelectedCompany = FALSE;
    }

    if ($this->selectedTaxCodeId == '' && $this->selectedCompanyId != "" && $hasAuthorised) {
      $this->elementErrors[] = 'Please select TaxCode.';
    }

    if ($this->selectedFreightTaxCodeId == '' && $this->selectedCompanyId != "" && $hasAuthorised) {
      $this->elementErrors[] = 'Please select Freight TaxCode.';
    }

    $this->assign('hasSelectedCompany', $hasSelectedCompany);
    $this->assign('hasAuthorised', $hasAuthorised);
    $this->assign('errors', $this->elementErrors);

    $this->assign('elementNames', $this->getRenderableElementNames());
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts($submitButtonLabel),
        'isDefault' => TRUE,
      ),
    ));
  }

  /**
   * Check if user has already authorised the extension.
   *
   * @return bool
   */
  private function hasAuthorised() {
    $hasAuthorised = TRUE;

    $defaults = $this->setDefaultValues();
    if ($this->accessToken == "" || $this->refreshToken == "" || (isset($this->_submittedValues['myob_api_key']) && $this->_submittedValues['myob_api_key'] != $defaults['myob_api_key']) || (isset($this->_submittedValues['myob_api_secret']) && $this->_submittedValues['myob_api_secret'] != $defaults['myob_api_secret'])) {
      $hasAuthorised = FALSE;
    }
    return $hasAuthorised;
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = array(
          "name"        => $element->getName(),
          "description" => (isset($this->_settings[$element->getName()]["description"])) ? $this->_settings[$element->getName()]["description"] : '',
        );
      }
    }
    return $elementNames;
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  public function getFormSettings() {
    if (empty($this->_settings)) {
      $settings = civicrm_api3('setting', 'getfields', array('filters' => $this->_settingFilter));
      $settings = $settings['values'];
      $this->_settings = $settings;
    }
    if ($this->accessToken != "" && $this->refreshToken != "" && $this->selectedCompanyId != "") {
      $extraSettings = civicrm_api3('setting', 'getfields', array('filters' => array('group' => 'accountsync')));
      $this->_settings = $this->_settings + $extraSettings['values'];
    }
    return $this->_settings;
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  public function saveSettings() {
    $settings = $this->getFormSettings();
    $values = array_intersect_key($this->_submittedValues, $settings);
    $defaults = $this->setDefaultValues();

    if (isset($values['myob_selected_company_id']) && $values['myob_selected_company_id']) {
      $selectedCompanyId = $values['myob_selected_company_id'];
      $selectedCompanyFile = "";
      foreach ($this->companyFiles as $companyFile) {
        if ($companyFile['Id'] == $selectedCompanyId) {
          $selectedCompanyFile = $companyFile;
        }
      }

      if ($selectedCompanyFile) {
        $selectedCompanyFile = json_encode($selectedCompanyFile);
        $values['myob_selected_company_json'] = $selectedCompanyFile;
      }
    }
    else {
      $values['myob_selected_company_json'] = '';
    }

    $this->assignTaxCodeJSONFiles($values);

    civicrm_api3('setting', 'create', $values);

    return $settings;
  }

  /**
   * Save selected tax code JSON in Settings.
   *
   * @param $values
   */
  private function assignTaxCodeJSONFiles(&$values) {

    $taxCodeElements = array(
      'myob_selected_taxcode_id' => 'myob_selected_taxcode_json',
      'myob_selected_freighttaxcode_id' => 'myob_selected_freighttaxcode_json',
    );

    foreach ($taxCodeElements as $taxCodeIdKey => $taxCodeIdJson) {
      if (isset($values[$taxCodeIdKey]) && $values[$taxCodeIdKey]) {
        $selectedTaxCode = $values[$taxCodeIdKey];
        $selectedTaxFile = "";
        foreach ($this->taxCodes as $taxCode) {
          if ($taxCode['UID'] == $selectedTaxCode) {
            $selectedTaxFile = $taxCode;
          }
        }

        if ($selectedTaxFile) {
          $selectedTaxFile = json_encode($selectedTaxFile);
          $values[$taxCodeIdJson] = $selectedTaxFile;
        }
      }
      else {
        $values[$taxCodeIdJson] = '';
      }
    }
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  public function setDefaultValues() {
    $existing = civicrm_api3('setting', 'get', array('return' => array_keys($this->getFormSettings())));
    $defaults = array();
    $domainID = CRM_Core_Config::domainID();
    foreach ($existing['values'][$domainID] as $name => $value) {
      $defaults[$name] = $value;
    }
    $defaults['myob_redirect_uri'] = CRM_Civimyob_Form_Settings::getRedirectURL();
    return $defaults;
  }

  /**
   * Get login type options for Cloud file.
   * @return array
   */
  public static function getLoginTypeOptions() {
    return [
      [
         'text' => 'my.MYOB Account Details',
        'id' => 'myob',
      ],
      [
        'text' => 'Company File Login',
        'id' => 'company',
      ],
    ];
  }

  public static function parseError($err) {
    return is_array($err)
      ? sprintf('<a target="_blank" href="%s">Access Token %s %s %s - %s</a>',
        $err['LearnMore'], $err['Severity'], $err['ErrorCode'], $err['Name'], $err['Message']
      )
      : $err ;
  }
}
