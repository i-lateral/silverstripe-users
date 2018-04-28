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
use SilverStripe\Core\Injector\Injector;

/**
 * Default form for editing Member details
 *
 * @package Users
 * @author  i-lateral <info@ilateral.co.uk>
 */
class EditAccountForm extends Form
{

    /**
     * These fields will be ignored by the `Users_EditAccountForm`
     * when generating fields
     * 
     * @var array
     */
    private static $ignore_member_fields = array(
        "LastVisited",
        "FailedLoginCount",
        "DateFormat",
        "TimeFormat",
        "VerificationCode",
        "Password",
        "HasConfiguredDashboard",
        "URLSegment",
        "BlogProfileSummary",
        "BlogProfileImage"
    );

    /**
     * Setup this form
     * 
     * @param Controller $controller Current Controller
     * @param string     $name       Name of this form
     * 
     * @return void
     */
    public function __construct($controller, $name = "Users_EditAccountForm")
    {
        $member = Injector::inst()->get(Member::class);

        $hidden_fields = array_merge(
            $member->config()->hidden_fields,
            static::config()->ignore_member_fields
        );

        $fields = $member->getFrontEndFields();

        // Remove all "hidden fields"
        foreach ($hidden_fields as $field_name) {
            $fields->removeByName($field_name);
        }

        // Add the current member ID
        $fields->add(HiddenField::create("ID"));

        // Switch locale field
        $fields->replaceField(
            'Locale',
            DropdownField::create(
                "Locale",
                $member->fieldLabel("Locale"),
                i18n::get_existing_translations()
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

        $required = RequiredFields::create(
            $member->config()->required_fields
        );

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
     * 
     * @return SS_HTTPResponse
     */
    public function doUpdate($data)
    {
        $filter = array();
        $member = Member::get()->byID($data["ID"]);
        $curr = Security::getCurrentUser();

        $this->extend("onBeforeUpdate", $data);
        
        // Check that a member isn't trying to mess up another users profile
        if (!empty($curr) && $member->canEdit($curr)) {
            try {
                // Save member
                $this->saveInto($member);
                $member->write();

                $this->sessionMessage(
                    _t("Users.DETAILSUPDATED", "Account details updated"),
                    ValidationResult::TYPE_GOOD
                );
            } catch (Exception $e) {
                $this->sessionMessage(
                    $e->getMessage(),
                    ValidationResult::TYPE_ERROR
                );
            }
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