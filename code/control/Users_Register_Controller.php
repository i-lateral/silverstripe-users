<?php

/**
 * Base controller class for users to register. Provides extension hooks to
 * allow third party overwriting of both index and register form actions
 *
 * This controller is also used to allow registered accounts to "verify"
 * their details via email.
 *
 * This is done by adding verified users to the groups stipulated by the
 * $verification_groups config variable
 *
 */
class Users_Register_Controller extends Controller {

    /**
     * URL That you can access this from
     *
     * @config
     */
    private static $url_segment = "users/register";

    /**
     * Current actions available to this controller
     *
     * @var array
     */
    private static $allowed_actions = array(
        "index",
        "sendverification",
        "verify",
        "RegisterForm"
    );

    /**
     * Internal function designed to allow us to send a verification
     * email from multiple locations
     *
     * @param $member Member object to send email to
     * @return boolean
     */
    protected function send_verification_email(Member $member) {
        if($member) {
            $subject = _t("Users.PleaseVerify", "Please verify your account");
            if(Users::config()->send_email_from)
                $from = Users::config()->send_email_from;
            else
                $from = Email::config()->admin_email;

            $body = $this->renderWith(
                'UsersAccountVerification',
                array(
                    "Link" => Controller::join_links(
                        Director::absoluteBaseURL(),
                        $this->config()->url_segment,
                        "verify",
                        $member->ID,
                        $member->VerificationCode
                    )
                )
            );

            $email = new Email($from, $member->Email, $subject, $body);
            $email->sendPlain();

            return true;
        }

        return false;
    }

    public function Link($action = null) {
        return Controller::join_links(
            BASE_URL,
            $this->config()->url_segment,
            $action
        );
    }

    public function index(SS_HTTPRequest $request) {
        $this->customise(array(
            'Title'     => "Register",
            'ClassName' => 'RegisterPage',
            'Content'   => '',
            'Form'      => $this->RegisterForm(),
        ));

        $this->extend("updateIndexAction");

        return $this->renderWith(array(
            "Users_Register",
            "Users",
            "Page"
        ));
    }


    /**
     * Send a verification email to the user provided (if verification
     * emails are enabled and account is not already verified)
     *
     */
    public function sendverification() {
        $sent = false;

        if(Member::currentUserID())
            $member = Member::currentUser();
        else
            $member = Member::get()->byID($this->request->param("ID"));

        if($member && !$member->isVerified() && Users::config()->send_verification_email)
            $sent = $this->send_verification_email($member);
        else
            $sent = false;

        $this->customise(array(
            "ClassName" => "RegisterPage",
            "Sent" => $sent
        ));

        return $this->renderWith(array(
            "Users_Register_sendverification",
            "Users",
            "Page"
        ));
    }


    /**
     * Verify the provided user (ID) using the verification code (Other
     * ID) provided
     *
     */
    public function verify() {
        $member = Member::get()->byID($this->request->param("ID"));
        $code = $this->request->param("OtherID");
        $verify = false;

        // Check verification group exists, if not, make it
        // Add a verified users group (only used if we turn on
        // verification)
        $verify_groups = Group::get()
            ->filter("Code",Users::config()->verification_groups);

        $this->extend("onBeforeVerify", $member);

        if(($member && $code) && $code == $member->VerificationCode) {
            foreach($verify_groups as $group) {
                $group->Members()->add($member);
                $verify = true;
            }
        }

        $this->customise(array(
            "ClassName" => "RegisterPage",
            "Verify" => $verify
        ));

        $this->extend("onAfterVerify", $member);

        return $this->renderWith(array(
            "Users_Register_verify",
            "Users",
            "Page"
        ));
    }

    /**
     * Registration form
     *
     * @return Form
     */
    public function RegisterForm() {

        // If back URL set, push to session
        if(isset($_REQUEST['BackURL']))
            Session::set('BackURL',$_REQUEST['BackURL']);

        // Setup form fields
        $fields = FieldList::create(
            TextField::create("FirstName"),
            TextField::create("Surname"),
            EmailField::create("Email"),
            ConfirmedPasswordField::create("Password")
        );

        // Setup form actions
        $actions = new FieldList(
            FormAction::create("doRegister","Register")
                ->addExtraClass("btn")
                ->addExtraClass("btn-green")
        );

        // Setup required fields
        $required = new RequiredFields(array(
            "FirstName",
            "Surname",
            "Email",
            "Password"
        ));

        $form = Form::create($this, "RegisterForm", $fields, $actions, $required)
            ->addExtraClass("forms")
            ->addExtraClass("forms-columnar");

        $this->extend("updateRegisterForm", $form);

        $session_data = Session::get("Form.{$form->FormName()}.data");

        if($session_data && is_array($session_data)) {
            $form->loadDataFrom($session_data);
            Session::clear("Form.{$form->FormName()}.data");
        }

        return $form;
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
     * @param Form $form Registration form
     */
    function doRegister($data, $form) {
        $filter = array();

        if(isset($data['Email'])) $filter['Email'] = $data['Email'];

        $this->extend("updateMemberFilter", $filter);

        // Check if a user already exists
        if($member = Member::get()->filter($filter)->first()) {
            if($member) {
                $form->addErrorMessage(
                    "Blurb",
                    "Sorry, an account already exists with those details.",
                    "bad"
                );

                // Load errors into session and post back
                unset($data["Password"]);
                Session::set("Form.{$form->FormName()}.data", $data);

                return $this->redirectBack();
            }
        }

        $member = Member::create();
        $form->saveInto($member);

        // Set verification code for this user
        $member->VerificationCode = sha1(mt_rand() . mt_rand());
        $member->write();

        $this->extend("updateNewMember", $member, $data);

        // Add member to any groups that have been specified
        if(count(Users::config()->new_user_groups)) {
            $groups = Group::get()->filter(array(
                "Code" => Users::config()->new_user_groups
            ));

            foreach($groups as $group) {
                $group->Members()->add($member);
                $group->write();
            }
        }

        // Send a verification email, if needed
        if(Users::config()->send_verification_email)
            $sent = $this->send_verification_email($member);
        else
            $sent = false;

        // Login (if enabled)
        if(Users::config()->login_after_register)
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

        return $this->redirect($redirect_url);
    }
}
