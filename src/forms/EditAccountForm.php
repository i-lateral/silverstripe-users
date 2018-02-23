<?php

namespace ilateral\SilverStripe\Users\Forms;

use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\ORM\ValidationResult;

class EditAccountForm extends Form
{

    public function __construct($controller, $name = "Users_EditAccountForm")
    {
        $fields = FieldList::create(
            HiddenField::create("ID"),
            TextField::create(
                "FirstName",
                _t('Member.FIRSTNAME', "First Name")
            ),
            TextField::create(
                "Surname",
                _t('Member.SURNAME', "Surname")
            ),
            EmailField::create(
                "Email",
                _t("Member.EMAIL", "Email")
            )
        );

        $this->extend("updateFormFields", $fields);

        $cancel_url = $controller->Link();

        $actions = FieldList::create(
            LiteralField::create(
                "cancelLink",
                '<a class="btn btn-red" href="'.$cancel_url.'">'. _t("Users.CANCEL", "Cancel") .'</a>'
            ),
            FormAction::create("doUpdate", _t("CMSMain.SAVE", "Save"))
                ->addExtraClass("btn")
                ->addExtraClass("btn-green")
        );

        $this->extend("updateFormActions", $actions);

        $required = RequiredFields::create([
            "FirstName",
            "Surname",
            "Email"
        ]);

        $this->extend("updateRequiredFields", $required);

        parent::__construct(
            $controller,
            $name,
            $fields,
            $actions,
            $required
        );
        
        $this->extend("updateForm", $this);
    }

    /**
     * Register a new member
     *
     * @param array $data User submitted data
     */
    public function doUpdate($data)
    {
        $filter = array();
        $member = Member::get()->byID($data["ID"]);
        $curr = Security::getCurrentUser();

        $this->extend("onBeforeUpdate", $data);
        
        // Check that a member isn't trying to mess up another users profile
        if (!empty($curr) && $member->canEdit($curr)) {
            // Save member
            $this->saveInto($member);
            $member->write();

            $this->sessionMessage(
                _t("Users.DETAILSUPDATED", "Account details updated"),
                ValidationResult::TYPE_GOOD
            );
        } else {
            $this->sessionMessage(
                _t("Users.CANNOTEDIT", "You cannot edit this account"),
                ValidationResult::TYPE_ERROR
            );
        }

        $this->extend("onAfterUpdate", $data);

        return $this
            ->getController()
            ->redirectBack();
    }
}