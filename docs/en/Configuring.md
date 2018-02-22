# Configuring

## Sending emails from a unique address

You may want to use a different email address from the default
for sending emails from this module (for example, you may want to use a noreply address, or accounts@).

If this is the case, you can customise the sender address using the 
config variable:

    Users.send_email_from 

## Disabling/Enabling validation

This module will ask users to verify their account if `require_verification` is enabled. If you do not wish this module to 
request users to verify, you can disable this feature by setting the 
following to `false`:

    Users.require_verification

## Sending verification email on registration

By default this module sends a verification email when users 
register. If you do not want this to happen, then you can disable 
the email by setting the following to `false`:

    Users.send_verification_email

## Auto login users

By default this module will auto login new users. If you wish to 
disable this functionality, set the following to `false`:

    Users.login_after_register

## Adding users to your own group when registering

If you want to add users to your own groups automatically when they
register with your site (so you can give them custom permissions on
registration), it is fairly simple to do. You just need to add the
group code to the Users config in your `config.yml`:

    Users:
      new_user_groups:
        - "new-group-code"

## Removing default user groups assigned when registering

Alternativley, if you do not want to add new users to a group (for
example, a group that has been added by another module) you will need
to update the users config.

To do this, just add the following to your *_config.php* file.

    Users::removeNewUserGroup("group-code-to-remove");

## Adding verified users to your own group

This module supports asking users to verify their email addresses
(via a verification email and link). Once a user is verified they will be added to a "Verified Users" group.

If you want to add users to your own groups automatically on verification, you just need to add the group code to the Users config in your `config.yml`:

    Users:
      verification_groups:
        - "verify-group-code"

## Removing default user groups assigned when registering

Alternativley, if you do not want to add  verified users to a group 
you will need to update the users config.

To do this, just add the following to your *_config.php* file.

    Users::removeVerificationGroup("group-code-to-remove");