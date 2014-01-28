<?php

/**
 * Overwrite group object so we can setup some more default groups
 */
class Ext_Users_Group extends DataExtension {
    public function requireDefaultRecords() {
        parent::requireDefaultRecords();

        // Add default author group if no other group exists
        $curr_group = Group::get()->filter("Code","users-frontend");

        if(!$curr_group->exists()) {
            $group = new Group();
            $group->Code = 'users-frontend';
            $group->Title = "Frontend Users";
            $group->Sort = 1;
            $group->write();
            Permission::grant($group->ID, 'USERS_MANAGE_ACCOUNT');

            DB::alteration_message('Front end users group created', 'created');
        }
    }
}

