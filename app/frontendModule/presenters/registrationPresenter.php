<?php

/*
 * TODO:
 *  predavani parametru v url
 *  .
 *
 */
namespace frontendModule{
    class registrationPresenter extends \BasePresenter {

        public function createComponentRegisterForm() {
            $form = new Nette\Application\UI\Form;
            #$form->addProtection('Cross Site Request Forgery!');
            $form->addText('username', 'Přezdívka: ')
                    ->addRule(NForm::FILLED, 'Musíte vyplnit uživatelské jméno!')
                    ->addRule(function (NTextInput $user){
                        if(count(db::users()->where("username LIKE ?", $user->getValue()))){
                            return false;
                        }
                        return true;
                    }, 'Uživatelské jméno je již obsazené.');

            $form->addPassword('password', 'Heslo')
                    ->addRule(NForm::FILLED, 'Musíte vyplnit heslo.')
                    ->addRule(NForm::LENGTH, 'Heslo je příliš krátké.');

            $form->addText('mail', 'E-mail')
                    ->addRule(NForm::EMAIL, 'Email není validní!')
                    ->addRule(function(NTextInput $mail){
                        if(count(db::users_profiles()->where("mail LIKE ?", $mail->getValue()))){
                            return false;
                        }
                        return true;
                    }, 'Emailová adresa je již obsazená.');

            $form->addCheckbox('eula', 'Souhlasím s podmínkami.')
                    ->addRule(NForm::FILLED, 'Musíte souhlasit.');

            $form->addText('spambot', 'Toto musíte vyplnit.')
                    ->addRule(NForm::LENGTH, 'Špatně.', 0)
                    ->getControlPrototype()->class("hidden");
            $form['spambot']->getLabelPrototype()->class("hidden");

            $form->addGroup('Povinné údaje')->add($form['username'],$form['password'],$form['mail'],$form['eula'],$form['spambot']);

            $form->addSubmit('save', 'Registrovat');
            $form->onSubmit[] = callback($this, 'processRegisterForm');

            $form->setAction($this->link("register"));
            //$form->getElementPrototype()->action = $this->link("register");

            return $form;
        }

        public function processRegisterForm(Nette\Application\UI\Form $form) {
        #public function actionRegister(NAppForm $form) {
            $data = $form->getValues();
            $data["token"] = md5(uniqid());
            $data["create_time"] = time();
            $data["password"] = sha1($data["password"]);
            unset($data['eula']);
            unset($data['spambot']);
            if(!DB::registration()->where("token = ?", $data["token"])->count()){
                DB::registration()->insert($data);
            }
            $this->getTemplate()->mail = $data["mail"];
            $this->redirect(301, "mail", serialize(array("mail"=> $data["mail"], "token" => $data["token"])));
            return true;
        }

        public function actionMail($data){
            $data = unserialize($data);
            $this->getTemplate()->mail = $data["mail"];
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
            $headers .= "From: registrace@" . $_SERVER["SERVER_NAME"] . "\r\nX-Mailer: PHP/" . phpversion();

            $l = $this->link("finish", $data["token"]);
            $bdy = 'Blablabla <a href="' . $l . '">' . $l . '</a>';

            $this->getTemplate()->p = $bdy;
            #mail($data["mail"], "Registrace na serveru " . $_SERVER["SERVER_NAME"], $bdy, $headers);
        }

        public function actionFinish( $id ){
            $this->getTemplate()->message = "";
            $reg = DB::registration()->where("token = ?", $id);
            if(!count($reg)){
                $this->getTemplate()->message = "Registrace nebyla nalezena. Nevypršela už platnost odkazu?";
            }else{
                foreach($reg as $r){
                    $row = $r;
                    break;
                }
                if(DB::users()->where("username LIKE ?", $row["username"])->count() || DB::users()->where("mail = ?", $row["mail"])->count()){
                    $this->getTemplate()->message = "Uživatelské jméno/mail je již obsazen. Je nám líto.";
                    $reg->delete();
                    return false;
                }
                DB::users()->insert(array("id" => 0, "username" => $row["username"]));
                DB::users_profiles()->insert(array("id"=>0, "password" => $row["password"], "mail" => $row["mail"], "created" => $row["create_time"], "login"=>0));

                $reg->delete(); /* Vloží se pouze jednou, ale smažou se všechny odpovídající tokeny */
                $this->getTemplate()->message = "Registrace byla dokončena. Nyní se můžete přihlásit.";
            }
        }

    }
}