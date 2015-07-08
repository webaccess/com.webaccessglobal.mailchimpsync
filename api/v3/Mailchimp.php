<?php
function civicrm_api3_mailchimp_synchronize( $params ) {
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
      $memberCount = $mcLists->members($listsDetails['id']);
      $pointer = $memberCount['total']/100;
      $iteration = ceil($pointer);
      for($i = 0;$i < $iteration;$i++) {
        $members = $mcLists->members($listsDetails['id'],'subscribed',array('start'=> $i,'limit' => 100));
        foreach($members['data'] as $key => $value) {
          try {
            $createdContact = civicrm_api3('Contact', 'create', array(
                                'sequential' => 1,
                                'contact_type' => "Individual",
                                'first_name' => $value['merges']['FNAME'],
                                'last_name' => $value['merges']['LNAME'],
                                'email' => $value['merges']['EMAIL'],
                                'api.EntityTag.create' => array(
                                  'entity_id' => "\$value.id",
                                  'entity_table' => "civicrm_contact",
                                  'tag_id' => $keyTags
                                ),
                                'dupe_check' => true,
                              ));
          } catch (CiviCRM_API3_Exception $e) {
            if(!empty($e->getExtraParams()['ids'][0])) {
              $result = civicrm_api3('EntityTag', 'create', array(
                          'contact_id' => $e->getExtraParams()['ids'][0],
                          'tag_id' => $keyTags,
                        ));
            }
          }
        }
      }
    }
  }
  return civicrm_api3_create_success();
}