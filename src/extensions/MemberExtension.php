<?php

namespace ilateral\SilverStripe\Users\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Control\Controller;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Group;
use SilverStripe\Security\Security;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\Email\Email;
use ilateral\SilverStripe\Users\Users;
use ilateral\SilverStripe\Users\Control\RegisterController;

/**
 * Overwrite Member object
 *
 * @package Users
 * @author  i-lateral <info@ilateral.co.uk>
 */
class MemberExtension extends DataExtension
{
    private static $db = array(
        "VerificationCode" => "Varchar(40)"
    );

    /**
     * Is the current member verified?
     * 
     * @return boolean
     */
    public function isVerified()
    {
        return Permission::checkMember($this->owner, "USERS_VERIFIED");
    }

    /**
     * Register a new user account using the provided data
     * and then return the current member
     *
     * @param array $data Array of data to create member from
     *
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
            $groups = Group::get()->filter(
                array(
                "Code" => Users::config()->new_user_groups
                )
            );

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
            /** IdentityStore */
            $request = Injector::inst()->get(HTTPRequest::class);
            $rememberMe = (isset($data['Remember']) && Security::config()->get('autologin_enabled'));
            /** @var IdentityStore $identityStore */
            $identityStore = Injector::inst()->get(IdentityStore::class);
            $identityStore->logIn($this->owner, $rememberMe, $request);
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
                ->setHTMLTemplate("ilateral\\SilverStripe\\Users\\Email\\AccountVerification")
                ->setData(ArrayData::create([
                    "Link" => Controller::join_links(
                        $controller->AbsoluteLink("verify"),
                        $this->owner->ID,
                        $this->owner->VerificationCode
                    )
                ]));

            if ($email->send()) {
                return true;
            }
        }

        return false;
    }
}
