<?php

/**
 * Root class for users, this will mostly be used to store generic
 * config, but will probably be extended in future to provide additional
 * functionality.
 *
 * @package Users
 * @author  i-lateral <info@ilateral.co.uk>
 */
class Users extends SS_Object
{

    /**
     * Minimum character length of the password required
     * on registration/account editing
     *
     * @var    int
     * @config
     */
    private static $password_min_length = 6;

    /**
     * Maximum character length of the password required
     * on registration/account editing
     *
     * @var    int
     * @config
     */
    private static $password_max_length = 16;

    /**
     * Enforces strong password (at least one digit and one alphanumeric
     * character) on registration/account editing
     *
     * @var    boolean
     * @config
     */
    private static $password_require_strong = false;

    /**
     * Stipulate if a user requires verification. NOTE this does not
     * actually deny the user the ability to login, it only alerts them
     * that they need validiation
     *
     * @var    boolean
     * @config
     */
    private static $require_verification = true;

    /**
     * Stipulate whether to send a verification email to users after
     * registration
     *
     * @var    boolean
     * @config
     */
    private static $send_verification_email = false;

    /**
     * Stipulate the sender address for emails sent from this module. If
     * not set, use the default @Email.admin_email instead.
     *
     * @var    string
     * @config
     */
    private static $send_email_from;

    /**
     * Auto login users after registration
     *
     * @var    boolean
     * @config
     */
    private static $login_after_register = true;

    /**
     * Add new users to the following groups. This is a list of group codes.
     * Adding a new code will add the user to this group
     *
     * @var    array
     * @config
     */
    private static $new_user_groups = array(
        "users-frontend"
    );

    /**
     * Remove a group from the list of groups a new user is added to on
     * registering.
     *
     * @param string $code Group code that will be used
     * 
     * @return void
     */
    public static function removeNewUserGroup($code)
    {
        if (isset(self::config()->new_user_groups[$code])) {
            unset(self::config()->new_user_groups[$code]);
        }
    }

    /**
     * Groups a user will be added to when verified. This should be an
     * array of group "codes", NOT names or ID's
     *
     * @var    array
     * @config
     */
    private static $verification_groups = array(
        "users-verified"
    );

    /**
     * Remove a group from the list of groups a new user is added to on
     * registering.
     *
     * @param string $code Group code that will be used
     * 
     * @return void
     */
    public static function removeVerificationGroup($code)
    {
        if (isset(self::config()->verification_groups[$code])) {
            unset(self::config()->verification_groups[$code]);
        }
    }
}
