<?php

namespace ilateral\SilverStripe\Users\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Security\Group;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\ConfirmedPasswordField;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\CMS\Controllers\ContentController;
use ilateral\SilverStripe\Users\Users;

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
 * @package Users
 * @author  i-lateral <info@ilateral.co.uk>
 */
class RegisterController extends Controller
{

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
    private static $allowed_actions = [
        "index",
        "sendverification",
        "verify",
        "RegisterForm"
    ];

    /**
     * Setup default templates for this controller
     *
     * @var array
     */
    protected $templates = [
        'index' => [
            'RegisterController',
            self::class,
            'Page'
        ],
        'sendverification' => [
            'RegisterController_sendverification',
            self::class . '_sendverification',
            'RegisterController',
            self::class,
            'Page'
        ],
        'verify' => [
            'RegisterController_verify',
            self::class . '_verify',
            'RegisterController',
            self::class,
            'Page'
        ]
    ];

    /**
     * Internal function designed to allow us to send a verification
     * email from multiple locations
     *
     * @param $member Member object to send email to
     * 
     * @return boolean
     */
    protected function send_verification_email(Member $member)
    {
        if ($member->exists()) {
            return $member->sendVerificationEmail();            
        }

        return false;
    }

    /**
     * Get the link to this controller
     * 
     * @param string $action The URL endpoint for this controller
     * 
     * @return string
     */
    public function Link($action = null)
    {
        return Controller::join_links(
            $this->config()->url_segment,
            $action
        );
    }

    /**
     * Get an absolute link to this controller
     *
     * @param string $action The URL endpoint for this controller
     *
     * @return string
     */
    public function AbsoluteLink($action = null)
    {
        return Director::absoluteURL($this->Link($action));
    }

    /**
     * Get a relative (to the root url of the site) link to this
     * controller
     *
     * @param string $action The URL endpoint for this controller
     *
     * @return string
     */
    public function RelativeLink($action = null)
    {
        return Controller::join_links(
            $this->Link($action)
        );
    }

    /**
     * If content controller exists, return it's menu function
     *
     * @param int $level Menu level to return.
     *
     * @return ArrayList
     */
    public function getMenu($level = 1)
    {
        if (class_exists(ContentController::class)) {
            $controller = Injector::inst()->get(ContentController::class);
            return $controller->getMenu($level);
        }
    }

    /**
     * Shortcut for getMenu
     * 
     * @param int $level Menu level to return.
     * 
     * @return ArrayList
     */
    public function Menu($level)
    {
        return $this->getMenu();
    }

    /**
     * Default action this controller will deal with
     *
     * @return HTMLText
     */
    public function index()
    {
        $this->customise([
            'Title'     => _t('Users.Register', 'Register'),
            'MetaTitle' => _t('Users.Register', 'Register'),
            'Form'      => $this->RegisterForm(),
        ]);

        $this->extend("updateIndexAction");

        return $this->render();
    }


    /**
     * Send a verification email to the user provided (if verification
     * emails are enabled and account is not already verified)
     *
     * @return HTMLText
     */
    public function sendverification()
    {
        // If we don't allow verification emails, return an error
        if (!Users::config()->send_verification_email) {
            return $this->httpError(400);
        }

        $sent = false;
        $member = Security::getCurrentUser();

        if ($member->exists() && !$member->isVerified()) {
            $sent = $this->send_verification_email($member);
        }

        $this->customise([
            "Title" => _t('Users.AccountVerification','Account Verification'),
            "MetaTitle" => _t('Users.AccountVerification','Account Verification'),
            "Content" => $this->renderWith(
                "ilateral\\SilverStripe\\Users\\Includes\\SendVerificationContent",
                ["Sent" => $sent]
            ),
            "Sent" => $sent
        ]);

        $this->extend("updateSendVerificationAction");

        return $this->render();
    }

    /**
     * Verify the provided user (ID) using the verification code (Other
     * ID) provided
     *
     * @return HTMLText
     */
    public function verify()
    {   
        $id = $this->getRequest()->param("ID");
        $code = $this->getRequest()->param("OtherID");
        $member = Member::get()->byID($id);
        $verify = false;

        // Check verification group exists, if not, make it
        // Add a verified users group (only used if we turn on
        // verification)
        $verify_groups = Group::get()
            ->filter("Code", Users::config()->verification_groups);

        $this->extend("onBeforeVerify", $member);

        if (($member && $code) && $code == $member->VerificationCode) {
            foreach ($verify_groups as $group) {
                $group->Members()->add($member);
                $verify = true;
            }
        }

        $this->customise([
            "Title" => _t('Users.AccountVerification','Account Verification'),
            "MetaTitle" => _t('Users.AccountVerification','Account Verification'),
            "Content" => $this->renderWith(
                "ilateral\\SilverStripe\\Users\\Includes\\VerifyContent",
                ["Verify" => $verify]
            ),
            "Verify" => $verify
        ]);

        $this->extend("onAfterVerify", $member);

        return $this->render();
    }

    /**
     * Registration form
     *
     * @return Form
     */
    public function RegisterForm()
    {
        $session = $this->getRequest()->getSession();
        
        // If back URL set, push to session
        if (isset($_REQUEST['BackURL'])) {
            $session->set(
                'BackURL',
                $_REQUEST['BackURL']
            );
        }

        $config = Users::config();

        $form = Form::create(
            $this,
            "RegisterForm",
            FieldList::create(
                TextField::create("FirstName"),
                TextField::create("Surname"),
                EmailField::create("Email"),
                $password_field = ConfirmedPasswordField::create("Password")
            ),
            FieldList::create(
                FormAction::create("doRegister", "Register")
                    ->addExtraClass("btn")
                    ->addExtraClass("btn-green")
            ),
            RequiredFields::create([
                "FirstName",
                "Surname",
                "Email",
                "Password"
            ])
        )->addExtraClass("forms")
        ->addExtraClass("forms-columnar");

        $password_field->minLength = $config->get("password_min_length");
        $password_field->maxLength = $config->get("password_max_length");
        $password_field->requireStrongPassword = $config->get("password_require_strong");

        $this->extend("updateRegisterForm", $form);

        $session_data = $session->get("Form.{$form->FormName()}.data");

        if ($session_data && is_array($session_data)) {
            $form->loadDataFrom($session_data);
            $session->clear("Form.{$form->FormName()}.data");
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
     * @param Form  $form Registration form
     * 
     * @return SS_HTTPResponse
     */
    public function doRegister($data, $form)
    {
        $filter = [];
        $session = $this->getRequest()->getSession();

        if (isset($data['Email'])) {
            $filter['Email'] = $data['Email'];
        }

        $this->extend("updateMemberFilter", $filter);

        // Check if a user already exists
        if ($member = Member::get()->filter($filter)->first()) {
            if ($member) {
                $form->sessionMessage(
                    "Sorry, an account already exists with those details.",
                    ValidationResult::TYPE_ERROR
                );

                // Load errors into session and post back
                unset($data["Password"]);
                $session->set("Form.{$form->FormName()}.data", $data);

                return $this->redirectBack();
            }
        }

        $member = Member::create();
        $member->Register($data);

        $this->extend("updateNewMember", $member, $data);

        $session_url = $session->get("BackURL");
        $request_url = $this->getRequest()->requestVar("BackURL");

        // If a back URL is used in session.
        if (!empty($session_url)) {
            $redirect_url = $session_url;
        } elseif (!empty($request_url)) {
            $redirect_url = $request_url;
        } else {
            $controller = Injector::inst()->get(AccountController::class);
            $redirect_url = $controller->Link();
        }

        return $this->redirect($redirect_url);
    }
}
