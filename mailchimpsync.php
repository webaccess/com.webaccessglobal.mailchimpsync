<?php

require_once 'mailchimpsync.civix.php';
require_once 'vendor/mailchimp/Mailchimp.php';
require_once 'vendor/mailchimp/Mailchimp/Lists.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function mailchimpsync_civicrm_config(&$config) {
  _mailchimpsync_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function mailchimpsync_civicrm_xmlMenu(&$files) {
  _mailchimpsync_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mailchimpsync_civicrm_install() {
  // Create a cron job to do sync data between CiviCRM and MailChimp.
  $params = array(
    'sequential' => 1,
    'name'          => 'CiviCRM Mailchimp Sync',
    'description'   => 'Sync contacts from MailChimp to CiviCRM',
    'run_frequency' => 'Daily',
    'api_entity'    => 'Mailchimp',
    'api_action'    => 'synchronize',
    'is_active'     => 1,
  );
  $jobsSynced = civicrm_api3('job', 'create', $params);
  return _mailchimpsync_civix_civicrm_install();
}

function mailchimpsync_civicrm_navigationMenu( &$params ) {
  // get the id of Administer Menu
  $administerMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Administer', 'id', 'name');
  CRM_Core_Error::debug_var( '$administerMenuId', $administerMenuId );
  // skip adding menu if there is no administer menu
  if ($administerMenuId) {
    // get the maximum key under adminster menu
    $maxKey = max( array_keys($params[$administerMenuId]['child']));
    $params[$administerMenuId]['child'][$maxKey+1] =  array (
      'attributes' => array (
        'label'      => 'MailChimp Settings',
        'name'       => 'MailChimp Settings',
        'url'        => 'civicrm/mailchimp/apikeyregister',
        'permission' => 'administer CiviCRM',
        'operator'   => NULL,
        'separator'  => TRUE,
        'parentID'   => $administerMenuId,
        'navID'      => $maxKey+1,
        'active'     => 1
      )
    );
    CRM_Core_BAO_Navigation::add($params);
  }
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function mailchimpsync_civicrm_uninstall() {
  $job = civicrm_api3('Job', 'get', array(
           'sequential' => 1,
           'name' => "CiviCRM Mailchimp Sync",
         ));
  foreach($job['values'] as $key => $value) {
    $deletedJob = civicrm_api3('Job', 'delete', array(
                    'sequential' => 1,
                    'id' => $value['id'],
                  ));
  }
  return _mailchimpsync_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mailchimpsync_civicrm_enable() {
  return _mailchimpsync_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function mailchimpsync_civicrm_disable() {
  return _mailchimpsync_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function mailchimpsync_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _mailchimpsync_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function mailchimpsync_civicrm_managed(&$entities) {
  return _mailchimpsync_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function mailchimpsync_civicrm_caseTypes(&$caseTypes) {
  _mailchimpsync_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function mailchimpsync_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _mailchimpsync_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
