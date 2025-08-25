<?php
// AUTO-GENERATED FILE
class CRM_MocoIntegration_ExtensionUtil {
  const SHORT_NAME = 'moco_integration';
  const LONG_NAME = 'civicrm_moco_integration';
  const CLASS_PREFIX = 'CRM_MocoIntegration';

  public static function ts($text, $params = []) {
    if (!array_key_exists('domain', $params)) {
      $params['domain'] = [self::LONG_NAME, NULL];
    }
    return ts($text, $params);
  }
}

function _civicrm_moco_integration_civix_civicrm_config(&$config = NULL) {
  static $configured = FALSE;
  if ($configured) {
    return;
  }
  $configured = TRUE;
  $extRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR;
  $include_path = $extRoot . PATH_SEPARATOR . get_include_path();
  set_include_path($include_path);
}

function _civicrm_moco_integration_civix_civicrm_install() {
  _civicrm_moco_integration_civix_civicrm_config();
}

function _civicrm_moco_integration_civix_civicrm_enable() {
  _civicrm_moco_integration_civix_civicrm_config();
}

function _civicrm_moco_integration_civix_civicrm_xmlMenu(&$files) {
  foreach (glob(__DIR__ . '/xml/Menu/*.xml') as $file) {
    $files[] = $file;
  }
}

function _civicrm_moco_integration_civix_civicrm_entityTypes(&$entityTypes) {
  $entityTypes = array_merge($entityTypes, []);
}

use CRM_MocoIntegration_ExtensionUtil as E;
