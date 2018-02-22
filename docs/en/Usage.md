Usage
=====

## Adding users to your own group when registering

If you want to add users to your own groups automatically when they
register with your site (so you can give them custom permissions on
registration), it is fairly simple to do. You just need to add the group
code to the Users config.

To do this, just add the following to your *_config.php* file.

    Users::addNewUserGroup("your-group-code");

## Removing default user groups assigned when registering

Alternativley, if you do not want to add new users to a group (for
example, a group that has been added by another module) you will need
to update the users config.

To do this, just add the following to your *_config.php* file.

    Users::removeNewUserGroup("group-code-to-remove");
