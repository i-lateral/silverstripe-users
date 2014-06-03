Examples
========

## Adding a simple captcha field to registration

Adding fields to the registration form is fairly simple, using
Silverstripe's extension class.

**Note** This example uses the [textcaptcha](https://github.com/azt3k/abc-silverstripe-textcap)
module.

First, you need to add an extension class that will add the additional
"ABCTextCapField" field and required field

    // mysite/code/extensions/AppUsersRegister.php
    class AppUsersRegister extends Extension {
        public function updateRegisterForm($form) {
            $form->removeExtraClass("forms-columnar");
            // Add field to registration form
            $form
                ->Fields()
                ->add(ABCTextCapField::create("Captcha", "Answer this to prove you are human"));
            // Add validator
            $form
                ->getValidator()
                ->addRequiredField("Captcha");
        }
    }

Now, you will need to map the extension via your config.yml (mysite/_config/config.yml)

    Users_Register_Controller:
      extensions:
        - AppUsersRegister

Next, visit [textcaptcha](http://textcaptcha.com/) and sign up for an
API key.

Finally, add the API key to your _config.php

    AbcTextCap::$text_captcha_api_key = 'your-api-key'

Now you should get a text captcha field appearing in your registration
form.
