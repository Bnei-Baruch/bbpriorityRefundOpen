<?php

require_once 'CRM/Core/Form.php';

class CRM_BbpriorityRefundOpen_Form_Settings extends CRM_Core_Form {
  public function buildQuickForm() {
    $this->add('checkbox', 'ipn_http', 'Use http for IPN Callback');
    $this->add('text', 'merchant_terminal', 'Merchant Terminal', array('size' => 5));

    $paymentProcessors = $this->getPaymentProcessors();
    foreach( $paymentProcessors as $paymentProcessor ) {
      $settingCode = 'merchant_terminal_' . $paymentProcessor[ "id" ];
      $settingTitle = $paymentProcessor[ "name" ] . " (" .
        ( $paymentProcessor["is_test"] == 0 ? "Live" : "Test" ) . ")";
      $this->add('text', $settingCode, $settingTitle, array('size' => 5));
    }

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));
    parent::buildQuickForm();
  }

  function setDefaultValues() {
    $defaults = array();
    $bbpriorityRefundOpen_settings = CRM_Core_BAO_Setting::getItem("BbpriorityRefundOpen Settings", 'bbpriorityRefundOpen_settings');
    if (!empty($bbpriorityRefundOpen_settings)) {
      $defaults = $bbpriorityRefundOpen_settings;
    }
    return $defaults;
  }

  public function postProcess() {
    $values = $this->exportValues();
    $bbpriorityRefundOpen_settings['ipn_http'] = $values['ipn_http'];
    $bbpriorityRefundOpen_settings['merchant_terminal'] = $values['merchant_terminal'];
    
    $paymentProcessors = $this->getPaymentProcessors();
    foreach( $paymentProcessors as $paymentProcessor ) {
      $settingId = 'merchant_terminal_' . $paymentProcessor[ "id" ];
      $bbpriorityRefundOpen_settings[$settingId] = $values[$settingId];
    }
    
    CRM_Core_BAO_Setting::setItem($bbpriorityRefundOpen_settings, "Bb Priority RefundOpen Settings", 'bbpriorityRefundOpen_settings');
    CRM_Core_Session::setStatus(
      ts('Bb Priority RefundOpen Settings Saved', array( 'domain' => 'info.kabbalah.payment.bbpriorityRefundOpen')),
      'Configuration Updated', 'success');

    parent::postProcess();
  }

  public function getPaymentProcessors() {
    // Get the BbpriorityRefundOpen payment processor type
    $bbpriorityRefundOpenName = array( 'name' => 'BbpriorityRefundOpen' );
    $paymentProcessorType = civicrm_api3( 'PaymentProcessorType', 'getsingle', $bbpriorityRefundOpenName );

    // Get the payment processors of bbpriorityRefundOpen type
    $bbpriorityRefundOpenType = array(
      'payment_processor_type_id' => $paymentProcessorType[ 'id' ],
      'is_active' => 1 );
    $paymentProcessors = civicrm_api3( 'PaymentProcessor', 'get', $bbpriorityRefundOpenType );

    return $paymentProcessors["values"];
  }
}
