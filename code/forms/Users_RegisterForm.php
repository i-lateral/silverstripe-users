<?php

class Users_RegisterForm extends Form {

    /**
     * Auto login users after registration
     *
     * @var Boolean
     * @config
     */
    private static $login_after_register = true;

    /**
     * Add new users to the following groups. This is a list of group codes.
     * Adding a new code will add the user to this group
     *
     * @var array
     * @config
     */
    private static $new_user_groups = array(
        "users-frontend"
    );

    /**
     * Build our initial registration form. This is designed to be hooked into
     * at several points (using extensions), to allow:
     *
     *  - Updating of form fields
     *  - Updating of form actions
     *  - Updating of requirements
     *
     */
    public function __construct($controller, $name) {

        // If back URL set, push to session
        if(isset($_REQUEST['BackURL']))
            Session::set('BackURL',$_REQUEST['BackURL']);

        // Setup form fields
        $fields = new FieldList();

        $fields->add(TextField::create("FirstName"));
        $fields->add(TextField::create("Surname"));
        $fields->add(EmailField::create("Email"));
        $fields->add(ConfirmedPasswordField::create("Password"));

        $this->extend("updateFormFields", $fields);


        // Setup form actions
        $cancel_url = Controller::join_links(BASE_URL);

        $actions = new FieldList(
            LiteralField::create("cancelLink",'<a class="btn btn-red" href="'.$cancel_url.'">Cancel</a>'),
            FormAction::create("doRegister","Register")
                ->addExtraClass("btn")
                ->addExtraClass("btn-green")
        );

        $this->extend("updateFormActions", $actions);

        // Setup required fields
        $required = new RequiredFields(array(
            "FirstName",
            "Surname",
            "Email",
            "Password"
        ));

        $this->extend("updateRequiredFields", $required);

        parent::__construct($controller, $name, $fields, $actions, $required);
    }

    /**
     * Register a new member. This action is deigned to be intercepted at 2
     * points:
     *
     *  - Modify the initial member filter (so that you can perfom bespoke
     *    member filtering
     *
     *  - Modify the member user before saving (so we can add extra permissions
     *    etc)
     *
     * @param array $data User submitted data
     */
    function doRegister($data) {
        $controller = $this->controller;
        $filter = array();

        $filter['Email'] = $data['Email'];

        $this->extend("updateMemberFilter", $filter);

        // Check if a user already exists
        if($member = Member::get()->filter($filter)->first() && $member) {
            $form->addErrorMessage(
                "Blurb",
                "Sorry, an account already exists with those details.",
                "bad"
            );

            // Load errors into session and post back
            Session::set("FormInfo.Form_Form.data", $data);

            return $controller->redirectBack();
        }


        $member = Member::create();
        $this->saveInto($member);

        $this->extend("updateNewMember", $member, $data);

        $member->write();

        // Finally, add member to any groups that have been specified
        if(count($this->config()->new_user_groups)) {
            $groups = Group::get()->filter(array(
                "Code" => $this->config()->new_user_groups
            ));

            foreach($groups as $group) {
                $group->Members()->add($member);
                $group->write();
            }
        }

        // Login (if enabled)
        if($this->config()->login_after_register)
            $member->LogIn(isset($data['Remember']));

        // If a back URL is used in session.
        if(Session::get("BackURL")) {
            $redirect_url = Session::get("BackURL");
        } else {
            $redirect_url = Controller::join_links(
                BASE_URL,
                Users_Account_Controller::config()->url_segment
            );
        }

        return $controller->redirect($redirect_url);
    }
}
