<?php

namespace ilateral\SilverStripe\Users\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Group;
use SilverStripe\Security\Permission;
use SilverStripe\ORM\DB;

/**
 * Overwrite group object so we can setup some more default groups
 *
 * @package Users
 * @author  i-lateral <info@ilateral.co.uk>
 */
class GroupExtension extends DataExtension
{
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        // Add default author group if no other group exists
        $frontend_group = Group::get()->filter("Code", "users-frontend");

        if (!$frontend_group->exists()) {
            $frontend_group = Group::create();
            $frontend_group->Code = 'users-frontend';
            $frontend_group->Title = "Frontend Users";
            $frontend_group->Sort = 1;
            $frontend_group->write();
            Permission::grant($frontend_group->ID, 'USERS_MANAGE_ACCOUNT');

            DB::alteration_message('Front end users group created', 'created');
        }

        // Add a verified users group (only used if we turn on
        // verification)
        $verify_group = Group::get()->filter("Code", "users-verified");

        if (!$verify_group->exists()) {
            $verify_group = Group::create();
            $verify_group->Code = 'users-verified';
            $verify_group->Title = "Verified Users";
            $verify_group->Sort = 1;
            $verify_group->write();
            Permission::grant($verify_group->ID, 'USERS_VERIFIED');

            DB::alteration_message('Verified users group created', 'created');
        }
    }
}
