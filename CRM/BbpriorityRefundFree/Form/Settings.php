<?php

require_once 'CRM/Core/Form.php';

class CRM_BbpriorityRefund_Form_Settings extends CRM_Core_Form {
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
    $bbpriorityRefund_settings = CRM_Core_BAO_Setting::getItem("BbpriorityRefund Settings", 'bbpriorityRefund_settings');
    if (!empty($bbpriorityRefund_settings)) {
      $defaults = $bbpriorityRefund_settings;
    }
    return $defaults;
  }

  public function postProcess() {
    $values = $this->exportValues();
    $bbpriorityRefund_settings['ipn_http'] = $values['ipn_http'];
    $bbpriorityRefund_settings['merchant_terminal'] = $values['merchant_terminal'];
    
    $paymentProcessors = $this->getPaymentProcessors();
    foreach( $paymentProcessors as $paymentProcessor ) {
      $settingId = 'merchant_terminal_' . $paymentProcessor[ "id" ];
      $bbpriorityRefund_settings[$settingId] = $values[$settingId];
    }
    
    CRM_Core_BAO_Setting::setItem($bbpriorityRefund_settings, "Bb Priority Refund Settings", 'bbpriorityRefund_settings');
    CRM_Core_Session::setStatus(
      ts('Bb Priority Refund Settings Saved', array( 'domain' => 'info.kabbalah.payment.bbpriorityRefund')),
      'Configuration Updated', 'success');

    parent::postProcess();
  }

  public function getPaymentProcessors() {
    // Get the BbpriorityRefund payment processor type
    $bbpriorityRefundName = array( 'name' => 'BbpriorityRefund' );
    $paymentProcessorType = civicrm_api3( 'PaymentProcessorType', 'getsingle', $bbpriorityRefundName );

    // Get the payment processors of bbpriorityRefund type
    $bbpriorityRefundType = array(
      'payment_processor_type_id' => $paymentProcessorType[ 'id' ],
      'is_active' => 1 );
    $paymentProcessors = civicrm_api3( 'PaymentProcessor', 'get', $bbpriorityRefundType );

    return $paymentProcessors["values"];
  }
}
