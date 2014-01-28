<?php

class Ext_Users_Member extends DataExtension {
    private static $db = array();

    private static $has_many = array();

    public function updateCMSFields(FieldList $fields) {

        return $fields;
    }
}
