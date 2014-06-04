<?php

/**
 * Root class for users, this will mostly be used to store generic
 * config, but will probably be extended in future to provide additional
 * functionality.
 *
 * @author i-lateral (http://www.i-lateral.com)
 * @package users
 */
class Users extends Object {

    /**
     * Stipulate if a user requires verification. NOTE this does not
     * actually deny the user the ability to login, it only alerts them
     * that they need validiation
     *
     * @var Boolean
     * @config
     */
    private static $require_verification = true;

    /**
     * Stipulate whether to send a verification email to users after
     * registration
     *
     * @var Boolean
     * @config
     */
    private static $send_verification_email = true;

    /**
     * Stipulate the sender address for emails sent from this module. If
     * not set, use the default @Email.admin_email instead.
     *
     * @var strong
     * @config
     */
    private static $send_email_from;

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
     * Add a group to the list of groups a new user is added to on
     * registering.
     *
     * @param string Group code that will be used
     */
    public static function addNewUserGroup($code) {
        self::$new_user_groups[] = $code;
    }

    /**
     * Remove a group from the list of groups a new user is added to on
     * registering.
     *
     * @param string Group code that will be used
     */
    public static function removeNewUserGroup($code) {
        if(isset(self::$new_user_groups[$code]))
            unset(self::$new_user_groups[$code]);
    }

    /**
     * Groups a user will be added to when verified. This should be an
     * array of group "codes", NOT names or ID's
     *
     * @var array
     * @config
     */
    private static $verification_groups = array(
        "users-verified"
    );

    /**
     * Add a group to the list of groups a new user is added to on
     * registering.
     *
     * @param string Group code that will be used
     */
    public static function addVerificationGroup($code) {
        self::$verification_groups[] = $code;
    }

    /**
     * Remove a group from the list of groups a new user is added to on
     * registering.
     *
     * @param string Group code that will be used
     */
    public static function removeVerificationGroup($code) {
        if(isset(self::$verification_groups[$code]))
            unset(self::$verification_groups[$code]);
    }

}
