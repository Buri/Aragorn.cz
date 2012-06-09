<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of LoginForm
 *
 * @author Buri <buri.buster@gmail.com>
 */
namespace Components{
    use Nette\Application\UI\Form;
    class LoginForm  extends \Nette\Application\UI\Form{
        public function build(){
            $this->getElementPrototype()->class = "logInForm";
            $this->addText("username", "Nick")
                    ->addRule(Form::FILLED);
            $this->addPassword("password", "Heslo")
                    ->addRule(Form::FILLED);
            $this->addCheckbox('forever', "Trvalé přihlášení")
                    ->setAttribute('title', "Trvalé přihlášení");
            $this->addSubmit("login", "Přihlásit");
        }
}
}