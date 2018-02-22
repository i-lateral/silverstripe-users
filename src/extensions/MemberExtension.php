<?php

namespace ilateral\SilverStripe\Users\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Control\Controller;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Group;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\Email\Email;
use ilateral\SilverStripe\Users\Users;
use ilateral\SilverStripe\Users\Control\RegisterController;

class MemberExtension extends DataExtension
{
    private static $db = array(
        "VerificationCode" => "Varchar(40)"
    );

    private static $has_many = array();

    public function isVerified()
    {
        return Permission::checkMember($this->owner, "USERS_VERIFIED");
    }

    /**
     * Register a new user account using the provided data
     * and then return the current member
     *
     * @param array $data
     * @return Member
     */
    public function Register($data)
    {
        // If we have passed a confirm password field, clean the
        // data
        if (isset($data["Password"]) && is_array($data["Password"]) && isset($data["Password"]["_Password"])) {
            $data["Password"] = $data["Password"]["_Password"];
        }
        
        $this->owner->update($data);

        // Set verification code for this user
        $this->owner->VerificationCode = sha1(mt_rand() . mt_rand());
        $this->owner->write();

        // Add member to any groups that have been specified
        if (count(Users::config()->new_user_groups)) {
            $groups = Group::get()->filter(array(
                "Code" => Users::config()->new_user_groups
            ));

            foreach ($groups as $group) {
                $group->Members()->add($this->owner);
                $group->write();
            }
        }

        // Send a verification email, if needed
        if (Users::config()->send_verification_email) {
            $this->owner->sendVerificationEmail();
        }

        // Login (if enabled)
        if (Users::config()->login_after_register) {
            $this->owner->LogIn(isset($data['Remember']));
        }

        return $this->owner;
    }

    /**
     * Send a verification email to this user account
     *
     * @return boolean
     */
    public function sendVerificationEmail()
    {
        if ($this->owner->exists()) {
            $controller = Injector::inst()->get(RegisterController::class);
            $subject = _t("Users.PleaseVerify", "Please verify your account");

            if (Users::config()->send_email_from) {
                $from = Users::config()->send_email_from;
            } else {
                $from = Email::config()->admin_email;
            }

            $email = Email::create();
            $email
                ->setFrom($from)
                ->setTo($this->owner->Email)
                ->setSubject($subject)
                ->setTemplate("ilateral\\SilverStripe\\Users\\Email\\AccountVerification")
                ->populateTemplate(ArrayData::create([
                    "Link" => Controller::join_links(
                        $controller->AbsoluteLink("verify"),
                        $this->owner->ID,
                        $this->owner->VerificationCode
                    )
                ]));

            $email->send();

            return true;
        }

        return false;
    }
}
