<?php
function civicrm_api3_mailchimp_synchronize( $params ) {
  $lastsynced = '';
  $return = $tags = $createdContact = array();
  $apiKey = CRM_Core_BAO_Setting::getItem('MailChimp Preferences',
                                            'api_key', NULL, FALSE
                                            );
  $tagsCount = civicrm_api3('Tag', 'getcount', array(
                 'sequential' => 1,
               ));
  $tagsGet = civicrm_api3('Tag', 'get', array(
              'sequential' => 1,
              'rowCount' => $tagsCount,
             ));
  foreach($tagsGet['values'] as $tagIds => $tagsValues) {
    $tags[$tagsValues['id']] = $tagsValues['name'];
  }
  $mcClient = new Mailchimp($apiKey);
  $mcLists = new Mailchimp_Lists($mcClient);
  $lists = $mcLists->getList();
  foreach($lists['data'] as $listsDetails) {
    if(!in_array($listsDetails['name'], $tags)) {
      $tagsCreate = civicrm_api3('Tag', 'create', array(
                      'sequential' => 1,
                      'name' => $listsDetails['name'],
                    ));
    }
    $tagsCount = civicrm_api3('Tag', 'getcount', array(
                 'sequential' => 1,
               ));
    $tagsGet = civicrm_api3('Tag', 'get', array(
                 'sequential' => 1,
                 'rowCount' => $tagsCount,
               ));
    foreach($tagsGet['values'] as $tagIds => $tagsValues) {
      $tags[$tagsValues['id']] = $tagsValues['name'];
    }
    if(in_array($listsDetails['name'], $tags)) {
      $keyTags = array_search($listsDetails['name'], $tags);
      $selectQuery = "SELECT * FROM `civicrm_mailchimp` WHERE `list_id` = %1";
      $result = CRM_Core_DAO::executeQuery($selectQuery, array(1 => array($listsDetails['id'], 'String')));
      while ($result->fetch()) {
        $lastsynced = $result->last_synced;
        $lastsynced = date('Y-m-d H:i:s', strtotime('-1 day', strtotime($lastsynced)));
      }
      if($lastsynced == '') {
        $lastsynced = '1970-01-01 00:00:00';
      }
      $members = $mcLists->export($listsDetails['id'],'subscribed',$lastsynced);
      $members = array_reverse($members);
      for($i = 1;$i < count($members)-1;$i++) {
        try {
          $createdContact = civicrm_api3('Contact', 'create', array(
                              'sequential' => 1,
                              'contact_type' => "Individual",
                              'first_name' => $members[$i][1],
                              'last_name' => $members[$i][2],
                              'email' => $members[$i][0],
                              'api.EntityTag.create' => array(
                                'entity_id' => "\$value.id",
                                'entity_table' => "civicrm_contact",
                                'tag_id' => $keyTags
                              ),
                              'dupe_check' => true,
                              'limit' => 0,
                            ));
        } catch (CiviCRM_API3_Exception $e) {
          if(!empty($e->getExtraParams()['ids'][0])) {
            $result = civicrm_api3('EntityTag', 'create', array(
                        'contact_id' => $e->getExtraParams()['ids'][0],
                        'tag_id' => $keyTags,
                      ));
          }
        }
        $query = "INSERT INTO `civicrm_mailchimp` ( `list_id` , `last_synced` ) VALUES (%1 , '{$members[$i][6]}' ) ON DUPLICATE KEY UPDATE `last_synced` = VALUES (`last_synced`)";
        $result = CRM_Core_DAO::executeQuery($query, array(
                    1 => array($listsDetails['id'], 'String')
                  )
        );
      }
    }
  }
  return civicrm_api3_create_success();
}