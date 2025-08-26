<?php
require_once 'civicrm_moco_integration.civix.php';
use CRM_MocoIntegration_ExtensionUtil as E;

/**
 * Implementation of hook_civicrm_config
 */
function civicrm_moco_integration_civicrm_config(&$config) {
  _civicrm_moco_integration_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_install
 */
function civicrm_moco_integration_civicrm_install() {
  _civicrm_moco_integration_civix_civicrm_install();
  // Create cache table for MOCO data
  $sql = "
    CREATE TABLE IF NOT EXISTS `civicrm_moco_cache` (
      `id` int unsigned NOT NULL AUTO_INCREMENT,
      `cache_key` varchar(255) NOT NULL,
      `data` longtext,
      `expires_at` timestamp NULL DEFAULT NULL,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `cache_key` (`cache_key`),
      KEY `expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ";
  CRM_Core_DAO::executeQuery($sql);
}

/**
 * Implementation of hook_civicrm_enable
 */
function civicrm_moco_integration_civicrm_enable() {
  _civicrm_moco_integration_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_tabset
 */
function civicrm_moco_integration_civicrm_tabset($tabsetName, &$tabs, $context) {
  if ($tabsetName == 'civicrm/contact/view') {
    $contactID = $context['contact_id'] ?? NULL;

    if ($contactID) {
      $mocoId = _civicrm_moco_integration_get_moco_id($contactID);

      if ($mocoId) {
        $url = CRM_Utils_System::url(
          'civicrm/contact/view/moco',
          "reset=1&snippet=1&force=1&cid={$contactID}"
        );

        $tabs[] = [
          'id' => 'moco_integration',
          'url' => $url,
          'title' => E::ts('MOCO Data'),
          'weight' => 200,
          'icon' => 'crm-i fa-euro'
        ];
      }
    }
  }
}

/**
 * Get MOCO ID from selected field (custom or standard)
 */
function _civicrm_moco_integration_get_moco_id($contactId) {
  try {
    $fieldName = Civi::settings()->get('moco_integration_field_name');
    if (!$fieldName) {
      return NULL;
    }
    if (strpos($fieldName, 'custom_') === 0) {
      // Custom Field
      $result = civicrm_api3('Contact', 'get', [
        'id' => $contactId,
        'return' => [$fieldName]
      ]);
      return $result['values'][$contactId][$fieldName] ?? NULL;
    } else {
      // Core (standard) field
      $result = civicrm_api3('Contact', 'getsingle', [
        'id' => $contactId,
        'return' => [$fieldName]
      ]);
      return $result[$fieldName] ?? NULL;
    }
  } catch (Exception $e) {
    CRM_Core_Error::debug_log_message('MOCO Integration: ' . $e->getMessage());
    return NULL;
  }
}

/**
 * Implementation of hook_civicrm_xmlMenu
 */
function civicrm_moco_integration_civicrm_xmlMenu(&$files) {
  _civicrm_moco_integration_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_entityTypes
 */
function civicrm_moco_integration_civicrm_entityTypes(&$entityTypes) {
  _civicrm_moco_integration_civix_civicrm_entityTypes($entityTypes);
}
