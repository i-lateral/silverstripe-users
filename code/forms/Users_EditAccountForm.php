<?php

/**
 * Default form for editing Member details
 *
 * @package Users
 * @author  i-lateral <info@ilateral.co.uk>
 */
class Users_EditAccountForm extends Form
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
        $member = Member::singleton();
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

        $cancel_url = Controller::join_links($controller->Link());

        $actions = new FieldList(
            LiteralField::create(
                "cancelLink",
                '<a class="btn btn-red" href="'.$cancel_url.'">'. _t("Users.CANCEL", "Cancel") .'</a>'
            ),
            FormAction::create("doUpdate", _t("CMSMain.SAVE", "Save"))
                ->addExtraClass("btn")
                ->addExtraClass("btn-green")
        );

        $this->extend("updateFormActions", $actions);

        $required = new RequiredFields(
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

        $this->extend("onBeforeUpdate", $data);

        // Check that a member isn't trying to mess up another users profile
        if (Member::currentUserID() && $member->canEdit(Member::currentUser())) {
            try {
                // Save member
                $this->saveInto($member);
                $member->write();
                
                $this->sessionMessage(
                    _t("Users.DETAILSUPDATED", "Account details updated"),
                    "success"
                );
            } catch (Exception $e) {
                $this->sessionMessage(
                    $e->getMessage(),
                    "warning"
                );
            }
        } else {
            $this->sessionMessage(
                _t("Users.CANNOTEDIT", "You cannot edit this account"),
                "warning"
            );
        }

        $this->extend("onAfterUpdate", $data);

        return $this->controller->redirectBack();
    }
}
