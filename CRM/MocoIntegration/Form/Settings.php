<?php
class CRM_MocoIntegration_Form_Settings extends CRM_Admin_Form_Settings {
  
  protected $_settings = [
    'moco_integration_api_key' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'moco_integration_domain' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'moco_integration_field_name' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'moco_integration_id_type' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'moco_integration_cache_enabled' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'moco_integration_cache_ttl' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
  ];
  
  public function buildQuickForm() {
    $this->add('text', 'moco_integration_api_key', 
      ts('MOCO API Key'), 
      ['class' => 'huge'],
      TRUE
    );
    
    $this->add('text', 'moco_integration_domain',
      ts('MOCO Domain'),
      ['class' => 'medium'],
      TRUE
    );
    
    $customFields = $this->getCustomFields();
    $this->add('select', 'moco_integration_field_name',
      ts('Custom Field for MOCO ID'),
      ['' => ts('- select -')] + $customFields,
      TRUE
    );
    
    $this->add('select', 'moco_integration_id_type',
      ts('MOCO ID Type'),
      [
        'company' => ts('Company ID'),
        'contact' => ts('Contact ID')
      ],
      TRUE
    );
    
    $this->add('checkbox', 'moco_integration_cache_enabled',
      ts('Enable Caching')
    );
    
    $this->add('text', 'moco_integration_cache_ttl',
      ts('Cache TTL (seconds)'),
      ['class' => 'small']
    );
    
    $this->addButtons([
      ['type' => 'submit', 'name' => ts('Save'), 'isDefault' => TRUE],
      ['type' => 'cancel', 'name' => ts('Cancel')]
    ]);
    
    parent::buildQuickForm();
  }
  
  private function getCustomFields() {
    $fields = [];
    
    $result = civicrm_api3('CustomField', 'get', [
      'extends' => ['IN' => ['Contact', 'Individual', 'Organization']],
      'data_type' => ['IN' => ['String', 'Int']],
      'return' => ['id', 'label'],
      'options' => ['limit' => 0]
    ]);
    
    foreach ($result['values'] as $field) {
      $fields["custom_{$field['id']}"] = $field['label'];
    }
    
    return $fields;
  }
  
  public function postProcess() {
    $values = $this->exportValues();
    
    // Test connection
    if ($values['moco_integration_api_key'] && $values['moco_integration_domain']) {
      $url = "https://{$values['moco_integration_domain']}.mocoapp.com/api/v1/session";
      
      $ch = curl_init();
      curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
          'Authorization: Token token=' . $values['moco_integration_api_key']
        ],
        CURLOPT_TIMEOUT => 10
      ]);
      
      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      
      if ($httpCode !== 200) {
        CRM_Core_Session::setStatus(
          ts('MOCO connection failed'),
          ts('Error'),
          'error'
        );
        return;
      }
    }
    
    parent::postProcess();
    
    CRM_Core_Session::setStatus(
      ts('Settings saved'),
      ts('Success'),
      'success'
    );
  }
}
