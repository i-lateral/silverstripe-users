<?php

/**
 * Base controller class for users to register. Provides extension hooks to
 * allow third party overwriting of both index and register form actions
 *
 */
class Users_Register_Controller extends Controller {

    /**
     * Current actions available to this controller
     *
     * @var array
     */
    private static $allowed_actions = array(
        "index",
        "RegisterForm"
    );

    public function index(SS_HTTPRequest $request) {
        $this->customise(array(
            'Title'     => "Register",
            'ClassName' => 'RegisterPage',
            'Content'   => '',
            'Form'      => $this->RegisterForm(),
        ));

        $this->extend("updateIndexAction");

        return $this->renderWith(array(
            "Users_Register",
            "Users",
            "Page"
        ));
    }

    public function RegisterForm() {
        $form = Users_RegisterForm::create($this,"RegisterForm")
            ->addExtraClass("forms")
            ->addExtraClass("forms-columnar");

        $this->extend("updateRegisterForm", $form);

        return $form;
    }
}
