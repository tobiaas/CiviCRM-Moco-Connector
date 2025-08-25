<?php
class CRM_MocoIntegration_Page_MocoTab extends CRM_Core_Page {
  
  public function run() {
    $contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this);
    
    if (!$contactId) {
      CRM_Core_Error::statusBounce('Missing contact ID');
    }
    
    try {
      // Get MOCO ID
      $customFieldName = Civi::settings()->get('moco_integration_field_name');
      if (!$customFieldName) {
        throw new Exception('MOCO field not configured');
      }
      
      $contact = civicrm_api3('Contact', 'getsingle', [
        'id' => $contactId,
        'return' => [$customFieldName]
      ]);
      
      $mocoId = $contact[$customFieldName] ?? null;
      
      if (!$mocoId) {
        $this->assign('error', 'No MOCO ID found');
        parent::run();
        return;
      }
      
      // Get MOCO data
      $mocoApi = new CRM_MocoIntegration_Api_MocoClient();
      
      $data = [
        'company' => $mocoApi->getCompanyData($mocoId),
        'projects' => $mocoApi->getCompanyProjects($mocoId),
        'invoices' => $mocoApi->getCompanyInvoices($mocoId),
        'revenue' => $mocoApi->getCompanyRevenue($mocoId),
        'activities' => $mocoApi->getCompanyActivities($mocoId)
      ];
      
      $this->assign('mocoData', $data);
      $this->assign('mocoId', $mocoId);
      $this->assign('contactId', $contactId);
      
    } catch (Exception $e) {
      $this->assign('error', $e->getMessage());
      CRM_Core_Error::debug_log_message('MOCO Error: ' . $e->getMessage());
    }
    
    parent::run();
  }
}
