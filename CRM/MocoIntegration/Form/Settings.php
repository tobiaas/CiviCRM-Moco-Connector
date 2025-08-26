<?php

use CRM_Core_Error;
use CRM_Core_Session;
use CRM_Core_BAO_Setting;

class CRM_MocoIntegration_Form_Settings extends CRM_Core_Form {
  protected $_settings = [
    'moco_integration_api_key' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'moco_integration_domain' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'moco_integration_field_name' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'moco_integration_id_type' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'moco_integration_cache_enabled' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'moco_integration_cache_ttl' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
  ];

  private function getMocoFields() {
    $fields = [
      'id' => ts('Contact ID'),
      'external_identifier' => ts('External Identifier'),
      'organization_name' => ts('Organization Name'),
    ];

    try {
      $result = civicrm_api3('CustomField', 'get', [
        'extends' => ['IN' => ['Contact', 'Individual', 'Organization']],
        'data_type' => ['IN' => ['String', 'Int']],
        'return' => ['id', 'label'],
        'options' => ['limit' => 0],
      ]);
      foreach ($result['values'] as $field) {
        $fields["custom_{$field['id']}"] = $field['label'];
      }
    }
    catch (Exception $e) {
      CRM_Core_Error::debug_log_message('MOCO Integration: Error loading custom fields - ' . $e->getMessage());
    }

    return $fields;
  }

  public function buildQuickForm() {
    $fields = $this->getMocoFields();

    $this->add('text', 'moco_integration_api_key', ts('MOCO API Key'), ['class' => 'huge'], TRUE);
    $this->add('text', 'moco_integration_domain', ts('MOCO Domain'), ['class' => 'medium'], TRUE);
    $this->add('select', 'moco_integration_field_name', ts('Feld für MOCO-ID'), ['' => ts('- Feld auswählen -')] + $fields, TRUE);
    $this->add('select', 'moco_integration_id_type', ts('MOCO ID Type'), [
      'company' => ts('Company ID'),
      'contact' => ts('Contact ID'),
    ], TRUE);
    $this->add('checkbox', 'moco_integration_cache_enabled', ts('Enable Caching'));
    $this->add('text', 'moco_integration_cache_ttl', ts('Cache TTL (seconds)'), ['class' => 'small']);

    $this->addButtons([
      ['type' => 'submit', 'name' => ts('Save'), 'isDefault' => TRUE],
      ['type' => 'cancel', 'name' => ts('Cancel')],
    ]);

    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    // Test MOCO API connection if credentials provided
    if (!empty($values['moco_integration_api_key']) && !empty($values['moco_integration_domain'])) {
      $url = "https://{$values['moco_integration_domain']}.mocoapp.com/api/v1/session";

      $ch = curl_init();
      curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
          'Authorization: Token token=' . $values['moco_integration_api_key'],
        ],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => false,
      ]);

      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $curlError = curl_error($ch);
      curl_close($ch);

      if ($curlError) {
        CRM_Core_Session::setStatus(ts('MOCO connection failed: %1', [1 => $curlError]), ts('Error'), 'error');
        return;
      }

      if ($httpCode !== 200) {
        $errorMsg = ts('MOCO API returned HTTP %1. Please check your API key and domain.', [1 => $httpCode]);
        CRM_Core_Session::setStatus($errorMsg, ts('Error'), 'error');
        return;
      }
    }

    parent::postProcess();

    CRM_Core_Session::setStatus(ts('MOCO Integration settings saved successfully'), ts('Success'), 'success');
  }
}
