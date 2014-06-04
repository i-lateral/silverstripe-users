<?php

class Ext_Users_Member extends DataExtension {
    private static $db = array(
        "VerificationCode" => "Varchar(40)"
    );

    private static $has_many = array();

    public function isVerified() {
        return Permission::checkMember($this->owner, "USERS_VERIFIED");
    }

    public function updateCMSFields(FieldList $fields) {
        return $fields;
    }
}
