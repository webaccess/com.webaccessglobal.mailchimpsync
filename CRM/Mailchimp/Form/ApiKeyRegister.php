<?php
class CRM_Mailchimp_Form_ApiKeyRegister extends CRM_Core_Form {

  const
    MC_SETTING_GROUP = 'MailChimp Preferences';
  private $_apiKey;
  function preProcess() {
    $this->_apiKey = null;
  }

  /**
   * Function to actually build the components of the form
   *
   * @return void
   * @access public
   */
  function buildQuickForm() {
    $this->add('text', 'api_key', ts('API Key'), array('size' => "64", 'maxlength' => "64"));
    $buttons = array(
                     array(
                           'type' => 'upload',
                           'name' => ts('Save'),
                           'subName' => 'view',
                           'isDefault' => TRUE,
                           )
                     );
    $this->addButtons($buttons);
  }

  function setDefaultValues() {
    $apiKey = CRM_Core_BAO_Setting::getItem(self::MC_SETTING_GROUP,
                                            'api_key', NULL, FALSE
                                            );
    if(isset($apiKey)) {
      return array('api_key' => $apiKey);
    } else {
      return array('api_key' => $this->_apiKey);
    }
  }

  /**
   * Form submission of new/edit api is processed.
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    //get the submitted values in an array
    $params = $this->controller->exportValues($this->_name);
    // Save the API Key & Save the Security Key
    if (CRM_Utils_Array::value('api_key', $params)) {
      CRM_Core_BAO_Setting::setItem($params['api_key'],
                                    self::MC_SETTING_GROUP,
                                    'api_key'
                                    );
    }
    $session = CRM_Core_Session::singleton();
    $message = "Api Key has been Saved!";
    $session->setStatus($message, ts('Api Key Saved'), 'success');
    $urlParams = null;
    $session->replaceUserContext(CRM_Utils_System::url('civicrm/mailchimp/apikeyregister', $urlParams));
    try {
      $mcClient = new Mailchimp($params['api_key']);
      $mcHelper = new Mailchimp_Helper($mcClient);
      $details  = $mcHelper->accountDetails();
    } catch (Mailchimp_Invalid_ApiKey $e) {
      CRM_Core_Session::setStatus($e->getMessage());
      return FALSE;
    } catch (Mailchimp_HttpError $e) {
      CRM_Core_Session::setStatus($e->getMessage());
      return FALSE;
    }
    $message = "Following is the account information received from API callback:<br/>
        <table class='mailchimp-table'>
        <tr><td>Company:</td><td>{$details['contact']['company']}</td></tr>
        <tr><td>First Name:</td><td>{$details['contact']['fname']}</td></tr>
        <tr><td>Last Name:</td><td>{$details['contact']['lname']}</td></tr>
        </table>";
    CRM_Core_Session::setStatus($message);
  }
}