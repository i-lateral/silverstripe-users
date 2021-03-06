# Usage

This module mkaes use of two controllers that handle account 
creation and usage.

These controllers are designed as framework only, so can be used
without the CMS (but do contain some basic CMS support, if it is 
installed).

## Registration

The registration controller handles creation of new user accounts
and verification of accounts. By default this is accesed via the
following URL:

    http://yoursite.com/users/register

You can add new fields to the registration form using provided extension hooks.

### Password Security

This module allows enforcement of password security on registration.
By default it required a password between 6 & 16 characters in 
length, but this can be changed and you can also enforce strong 
passwords. To do this, you can add this to your `config.yml`:

    Users:
      password_min_length: 8
      password_max_length: 20
      password_require_strong: true

## Account Management

You can access the account managment controller via the URL:

    http://yoursite.com/users/account

By default this allows you to:

* View a summary of the user account
* Change account details
* Change account password (via a seperate form).

You can also edit these interfaces using provided extension hooks.

## User acccount menu

This module makes `$UserAccountNav` available to all your 
controllers. You can include this variable in your templates and it 
will add an account navigation menu.

If you wish to change the styling of this menu, simply edit the `Users_AccountNav.ss`
template include.

## Adding/Removing fields to the Edit Account form

By default, this module generates an "Edit your account" view via:

        http://yoursite.com/users/account

This view uses an instance of `Users_EditAccountForm` to generate the edit form.

`Users_EditAccountForm` uses `Member::getFrontEndFields()` to generate the fieldlist,
it also uses the `Member.hidden_fields` config variable, as well as it's own
`ignore_member_fields` config variable, to remove fields from the EditForm.

If you want to remove additional fields from this form (for example `Locale`), you can
add the following to your config.yml:

```yml
Users_EditAccountForm:
  ignore_member_fields:
    - "Locale"
```

### Updating Required Fields

This module uses `Member.required_fields` config variable to determine required fields.
If you added the Field `PhoneNumber` and wanted to make it required, you could do as below:

```yml
Member:
  db:
    - "PhoneNumber"
  required_fields:
    - "PhoneNumber"
```