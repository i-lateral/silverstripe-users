<?php

/**
 * Controller that is used to allow users to manage their accounts via
 * the front end of the site.
 *
 */
class Users_Account_Controller extends Controller implements PermissionProvider {

    protected $member;

    /**
     * URL That you can access this from
     *
     * @config
     */
    private static $url_segment = "users/account";

    private static $allowed_actions = array(
        "edit",
        "changepassword",
        "EditAccountForm",
        "ChangePasswordForm",
    );

    /**
     * Determine if current user requires verification (based on their
     * account and Users verification setting).
     *
     * @return Boolean
     */
    public function RequireVerification() {
        if(!$this->member->isVerified() && Users::config()->require_verification)
            return true;
        else
            return false;
    }

    public function init() {
        parent::init();

        // Check we are logged in as a user who can access front end management
        if(!Permission::check("USERS_MANAGE_ACCOUNT")) Security::permissionFailure();

        // Set our member object
        $this->member = Member::currentUser();
    }

    public function Link($action = null) {
        return Controller::join_links(
            BASE_URL,
            $this->config()->url_segment,
            $action
        );
    }

    /**
     * Display the currently outstanding orders for the current user
     *
     */
    public function index() {
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

    public function edit() {
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

    public function changepassword() {
        // Set the back URL for this form
        Session::set("BackURL",$this->Link("changepassword"));

        $this->customise(array(
            "ClassName" => "AccountPage",
            "Form"  => $this->ChangePasswordForm()
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
    public function EditAccountForm() {
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
    public function ChangePasswordForm() {
        $form = ChangePasswordForm::create($this,"ChangePasswordForm");

        $form
            ->Actions()
            ->find("name","action_doChangePassword")
            ->addExtraClass("btn")
            ->addExtraClass("btn-green");

        $cancel_btn = LiteralField::create(
            "CancelLink",
            '<a href="' . $this->Link() . '" class="btn btn-red">'. _t("Users.CANCEL", "Cancel") .'</a>'
        );

        $form
            ->Actions()
            ->insertBefore($cancel_btn,"action_doChangePassword");

        $this->extend("updateChangePasswordForm", $form);

        return $form;
    }

    /**
     * Return a list of nav items for managing a users profile. You can add new
     * items to this menu using the "updateAccountMenu" extension
     *
     * @return ArrayList
     */
    public function getAccountMenu() {
        $menu = new ArrayList();

        $menu->add(new ArrayData(array(
            "ID"    => 0,
            "Title" => _t('Users.PROFILESUMMARY',"Profile Summary"),
            "Link"  => $this->Link()
        )));

        $menu->add(new ArrayData(array(
            "ID"    => 10,
            "Title" => _t('Users.EDITDETAILS',"Edit account details"),
            "Link"  => $this->Link("edit")
        )));

        $menu->add(new ArrayData(array(
            "ID"    => 30,
            "Title" => _t('Users.CHANGEPASSWORD',"Change password"),
            "Link"  => $this->Link("changepassword")
        )));

        $this->extend("updateAccountMenu", $menu);

        return $menu->sort("ID","ASC");
    }

    public function providePermissions() {
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
