<?php
class CRM_MocoIntegration_Api_MocoClient {
  
  private $apiKey;
  private $domain;
  private $baseUrl;
  
  public function __construct() {
    $this->apiKey = Civi::settings()->get('moco_integration_api_key');
    $this->domain = Civi::settings()->get('moco_integration_domain');
    $this->baseUrl = "https://{$this->domain}.mocoapp.com/api/v1";
  }
  
  /**
   * Get company data from MOCO
   */
  public function getCompanyData($mocoCompanyId) {
    $endpoint = "/companies/{$mocoCompanyId}";
    return $this->makeApiCall($endpoint);
  }
  
  /**
   * Get projects for a company
   */
  public function getCompanyProjects($mocoCompanyId) {
    $endpoint = "/projects";
    $params = ['company_id' => $mocoCompanyId];
    return $this->makeApiCall($endpoint, $params);
  }
  
  /**
   * Get invoices for a company
   */
  public function getCompanyInvoices($mocoCompanyId) {
    $endpoint = "/invoices";
    $params = ['company_id' => $mocoCompanyId];
    return $this->makeApiCall($endpoint, $params);
  }
  
  /**
   * Get revenue summary
   */
  public function getCompanyRevenue($mocoCompanyId) {
    $invoices = $this->getCompanyInvoices($mocoCompanyId);
    $projects = $this->getCompanyProjects($mocoCompanyId);
    
    $revenue = [
      'total_invoiced' => 0,
      'open_invoices' => 0,
      'active_projects' => 0,
      'project_budgets' => 0
    ];
    
    if ($invoices && isset($invoices)) {
      foreach ($invoices as $invoice) {
        $revenue['total_invoiced'] += $invoice['total'] ?? 0;
        if (in_array($invoice['status'], ['created', 'sent'])) {
          $revenue['open_invoices'] += $invoice['total'] ?? 0;
        }
      }
    }
    
    if ($projects && isset($projects)) {
      foreach ($projects as $project) {
        if ($project['active']) {
          $revenue['active_projects']++;
          $revenue['project_budgets'] += $project['budget'] ?? 0;
        }
      }
    }
    
    return $revenue;
  }
  
  /**
   * Get activities for a company
   */
  public function getCompanyActivities($mocoCompanyId, $limit = 20) {
    $projects = $this->getCompanyProjects($mocoCompanyId);
    $activities = [];
    
    if ($projects) {
      foreach ($projects as $project) {
        $endpoint = "/activities";
        $params = ['project_id' => $project['id'], 'limit' => $limit];
        $projectActivities = $this->makeApiCall($endpoint, $params);
        
        if ($projectActivities) {
          $activities = array_merge($activities, $projectActivities);
        }
      }
    }
    
    // Sort by date
    usort($activities, function($a, $b) {
      return strtotime($b['date']) - strtotime($a['date']);
    });
    
    return array_slice($activities, 0, $limit);
  }
  
  /**
   * Make API call to MOCO
   */
  private function makeApiCall($endpoint, $params = []) {
    if (!$this->apiKey || !$this->domain) {
      throw new Exception('MOCO API credentials not configured');
    }
    
    // Check cache first
    $cacheKey = md5($endpoint . serialize($params));
    $cached = $this->getCache($cacheKey);
    if ($cached !== false) {
      return $cached;
    }
    
    $url = $this->baseUrl . $endpoint;
    
    if (!empty($params)) {
      $url .= '?' . http_build_query($params);
    }
    
    $headers = [
      'Authorization: Token token=' . $this->apiKey,
      'Content-Type: application/json',
      'User-Agent: CiviCRM-MOCO-Integration/2.0'
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
      throw new Exception('CURL Error: ' . $error);
    }
    
    if ($httpCode !== 200) {
      throw new Exception("HTTP Error {$httpCode}: " . $response);
    }
    
    $data = json_decode($response, true);
    
    // Cache the result
    $this->setCache($cacheKey, $data);
    
    return $data;
  }
  
  /**
   * Get cached data
   */
  private function getCache($key) {
    if (!Civi::settings()->get('moco_integration_cache_enabled')) {
      return false;
    }
    
    $sql = "SELECT data FROM civicrm_moco_cache 
            WHERE cache_key = %1 AND (expires_at IS NULL OR expires_at > NOW())";
    $dao = CRM_Core_DAO::executeQuery($sql, [1 => [$key, 'String']]);
    
    if ($dao->fetch()) {
      return json_decode($dao->data, true);
    }
    
    return false;
  }
  
  /**
   * Set cache data
   */
  private function setCache($key, $data) {
    if (!Civi::settings()->get('moco_integration_cache_enabled')) {
      return;
    }
    
    $ttl = Civi::settings()->get('moco_integration_cache_ttl') ?: 300;
    $expires = date('Y-m-d H:i:s', time() + $ttl);
    
    $sql = "INSERT INTO civicrm_moco_cache (cache_key, data, expires_at) 
            VALUES (%1, %2, %3)
            ON DUPLICATE KEY UPDATE data = %2, expires_at = %3";
    
    CRM_Core_DAO::executeQuery($sql, [
      1 => [$key, 'String'],
      2 => [json_encode($data), 'String'],
      3 => [$expires, 'Timestamp']
    ]);
  }
}
