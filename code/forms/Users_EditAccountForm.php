<?php

class Users_EditAccountForm extends Form {

    public function __construct($controller, $name = "Users_EditAccountForm") {
        $fields = new FieldList();

        $fields->add(HiddenField::create("ID"));
        $fields->add(TextField::create("FirstName", _t('Member.FIRSTNAME',"First Name")));
        $fields->add(TextField::create("Surname", _t('Member.SURNAME',"Surname")));
        $fields->add(EmailField::create("Email", _t("Member.EMAIL","Email")));

        $this->extend("updateFormFields", $fields);

        $cancel_url = Controller::join_links($controller->Link());

        $actions = new FieldList(
            LiteralField::create(
                "cancelLink",
                '<a class="btn btn-red" href="'.$cancel_url.'">'. _t("Users.CANCEL", "Cancel") .'</a>'
            ),
            FormAction::create("doUpdate",_t("CMSMain.SAVE", "Save"))
                ->addExtraClass("btn")
                ->addExtraClass("btn-green")
        );

        $this->extend("updateFormActions", $actions);

        $required = new RequiredFields(array(
            "FirstName",
            "Surname",
            "Email"
        ));

        $this->extend("updateRequiredFields", $required);

        parent::__construct($controller, $name, $fields, $actions, $required);
    }

    /**
     * Register a new member
     *
     * @param array $data User submitted data
     * @param Form $form The used form
     */
    function doUpdate($data) {
        $filter = array();
        $member = Member::get()->byID($data["ID"]);

        // Check that a mamber isn't trying to mess up another users profile
        if(Member::currentUserID() && $member->canEdit(Member::currentUser())) {
            // Save member
            $this->saveInto($member);
            $member->write();
            
            $this->sessionMessage(
                _t("Users.DETAILSUPDATED","Account details updated"),
                "success"
            );
        } else {
            $this->sessionMessage(
                _t("Users.CANNOTEDIT","You cannot edit this account"),
                "warning"
            );
        }

        return $this->controller->redirectBack();
    }
}
