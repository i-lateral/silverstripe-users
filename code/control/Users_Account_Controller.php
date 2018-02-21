<?php

/**
 * Controller that is used to allow users to manage their accounts via
 * the front end of the site.
 *
 */
class Users_Account_Controller extends Controller implements PermissionProvider
{

    /**
     * URL That you can access this from
     *
     * @config
     */
    private static $url_segment = "users/account";

    /**
     * Allowed sub-URL's on this controller
     * 
     * @var array
     * @config
     */
    private static $allowed_actions = array(
        "edit",
        "changepassword",
        "EditAccountForm",
        "ChangePasswordForm",
    );

    /**
     * User account associated with this controller
     *
     * @var Member
     */
    protected $member;

    /**
     * Getter for member
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Setter for member
     *
     * @param Member $member
     * @return self
     */
    public function setMember(Member $member)
    {
        $this->member = $member;
        return $this;
    }

    /**
     * Determine if current user requires verification (based on their
     * account and Users verification setting).
     *
     * @return boolean
     */
    public function RequireVerification()
    {
        if (!$this->member->isVerified() && Users::config()->require_verification) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Perorm setup when this controller is initialised
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        // Check we are logged in as a user who can access front end management
        if (!Permission::check("USERS_MANAGE_ACCOUNT")) {
            Security::permissionFailure();
        }

        // Set our member object
        $this->member = Member::currentUser();
    }

    /**
     * Get the link to this controller
     * 
     * @param string $action
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
     * @param string $action
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
     * @param string $action
     * @return string
     */
    public function RelativeLink($action = null)
    {
        return Controller::join_links(
            $this->Link($action)
        );
    }

    /**
     * Display the currently outstanding orders for the current user
     *
     */
    public function index()
    {
        $member = Member::currentUser();

        $this->customise(array(
            "ClassName" => "AccountPage"
        ));

        $this->extend("onBeforeIndex");

        return $this->renderWith(array(
            "UserAccount",
            "UserAccount",
            "Page"
        ));
    }

    public function edit()
    {
        $member = Member::currentUser();

        $this->customise(array(
            "ClassName" => "AccountPage",
            "Form"  => $this->EditAccountForm()->loadDataFrom($member)
        ));

        $this->extend("onBeforeEdit");

        return $this->renderWith(array(
            "UserAccount_edit",
            "UserAccount",
            "Page"
        ));
    }

    public function changepassword()
    {
        // Set the back URL for this form
        $back_url = Controller::join_links(
            $this->Link("changepassword"),
            "?s=1"
        );
        
        Session::set("BackURL", $back_url);
        
        $form = $this->ChangePasswordForm();
        
        // Is password changed, set a session message.
        $password_set = $this->request->getVar("s");
        if($password_set && $password_set == 1) {
            $form->sessionMessage(
                _t("Users.PasswordChangedSuccessfully","Password Changed Successfully"),
                "good"
            );
        }

        $this->customise(array(
            "ClassName" => "AccountPage",
            "Form"  => $form
        ));

        $this->extend("onBeforeChangePassword");

        return $this->renderWith(array(
            "UserAccount_changepassword",
            "UserAccount",
            "Page"
        ));
    }

    /**
     * Factory for generating a profile form. The form can be expanded using an
     * extension class and calling the updateEditProfileForm method.
     *
     * @return Form
     */
    public function EditAccountForm()
    {
        $form = Users_EditAccountForm::create($this, "EditAccountForm");

        $this->extend("updateEditAccountForm", $form);

        return $form;
    }

    /**
     * Factory for generating a change password form. The form can be expanded
     * using an extension class and calling the updateChangePasswordForm method.
     *
     * @return Form
     */
    public function ChangePasswordForm()
    {
        $form = ChangePasswordForm::create($this, "ChangePasswordForm");

        $form
            ->Actions()
            ->find("name", "action_doChangePassword")
            ->addExtraClass("btn")
            ->addExtraClass("btn-green");

        $cancel_btn = LiteralField::create(
            "CancelLink",
            '<a href="' . $this->Link() . '" class="btn btn-red">'. _t("Users.CANCEL", "Cancel") .'</a>'
        );

        $form
            ->Actions()
            ->insertBefore($cancel_btn, "action_doChangePassword");

        $this->extend("updateChangePasswordForm", $form);

        return $form;
    }

    /**
     * Return a list of nav items for managing a users profile. You can add new
     * items to this menu using the "updateAccountMenu" extension
     *
     * @return ArrayList
     */
    public function getAccountMenu()
    {
        $menu = new ArrayList();
        
        $curr_action = $this->request->param("Action");

        $menu->add(new ArrayData(array(
            "ID"    => 0,
            "Title" => _t('Users.PROFILESUMMARY', "Profile Summary"),
            "Link"  => $this->Link(),
            "LinkingMode" => (!$curr_action) ? "current" : "link"
        )));

        $menu->add(new ArrayData(array(
            "ID"    => 10,
            "Title" => _t('Users.EDITDETAILS', "Edit account details"),
            "Link"  => $this->Link("edit"),
            "LinkingMode" => ($curr_action == "edit") ? "current" : "link"
        )));

        $menu->add(new ArrayData(array(
            "ID"    => 30,
            "Title" => _t('Users.CHANGEPASSWORD', "Change password"),
            "Link"  => $this->Link("changepassword"),
            "LinkingMode" => ($curr_action == "changepassword") ? "current" : "link"
        )));

        $this->extend("updateAccountMenu", $menu);

        return $menu->sort("ID", "ASC");
    }

    public function providePermissions()
    {
        return array(
            "USERS_MANAGE_ACCOUNT" => array(
                'name' => 'Manage user account',
                'help' => 'Allow user to manage their account details',
                'category' => 'Frontend Users',
                'sort' => 100
            ),
            "USERS_VERIFIED" => array(
                'name' => 'Verified user',
                'help' => 'Users have verified their account',
                'category' => 'Frontend Users',
                'sort' => 100
            ),
        );
    }
}
