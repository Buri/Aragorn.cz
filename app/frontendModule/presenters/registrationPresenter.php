<?php

/*
 * TODO:
 *  predavani parametru v url
 *  .
 *
 */
namespace frontendModule{
    use Nette\Environment;
    use Nette\Application\UI\Form;
    use \DB;
    
    class registrationPresenter extends \BasePresenter {

        /**
         *
         * @var array
         */
        protected $config;

        public function startup(){
            $this->template->banned = false;
            $this->template->reg = $this->getContext()->parameters['registration'];
            $this->config = $this->getContext()->parameters;

            $bans = $this->context->database->bans('ip LIKE ?', '%'.$_SERVER['REMOTE_ADDR'].'%')->where('expires > ?', time());
            if(count($bans) > 0){
                $this->template->banned = true;
            }else{
                $hosts = file(CFG_DIR . '/blacklist/host');
                $ip = $_SERVER['REMOTE_ADDR'];
                $hostname = gethostbyaddr($ip);
                foreach($hosts as $host){
                    if(preg_match("/$host/i", $ip) > 0 || preg_match("/$host/i", $hostname) > 0){
                        $this->template->banned = true;
                        break;
                    }
                }
            }
            parent::startup();
        }

        public function createComponentRegisterForm() {
            $db = $this->context->database;
            $form = new Form;
            #$form->addProtection('Cross Site Request Forgery!');
            $form->addText('username', 'Přezdívka: ')
                    ->addRule(Form::FILLED, 'Musíte vyplnit uživatelské jméno!')
                    ->addRule(function (\Nette\Forms\Controls\TextInput $user) use($db){
                        if(count($db->users()->where("username LIKE ? OR urlfragment = ?", array($user->getValue(), \Utilities::string2url($user->getValue()))))){
                            return false;
                        }
                        return true;
                    }, 'Uživatelské jméno je již obsazené.');

            $form->addPassword('password', 'Heslo')
                    ->addRule(Form::FILLED, 'Musíte vyplnit heslo.')
                    ->addRule(Form::MIN_LENGTH, 'Heslo je příliš krátké.', 6);

            $form->addText('mail', 'E-mail')
                    ->addRule(Form::EMAIL, 'Email není validní!')
                    ->addRule(function(\Nette\Forms\Controls\TextInput $mail) use ($db) {
                        if(count($db->users_profiles()->where("mail LIKE ?", $mail->getValue()))){
                            return false;
                        }
                        return true;
                    }, 'Emailová adresa je již obsazená.')
                    ->addRule(function(\Nette\Forms\Controls\TextInput $mail){
                        $val  = $mail->getValue();
                        $blacklist = file(CFG_DIR . '/blacklist/mail');
                        foreach($blacklist as $entry){
                            $entry = trim($entry);
                            //dump("/$entry/i ~ $val ? " . (preg_match("/$entry/i", $val) ? 'yes' : 'no'));
                            if(preg_match("/$entry/i", $val)){
                                return false;
                            }
                        }
                        return false;
                    }, "Vaše mailová adresa je na blacklistu.");

            /*$form->addCheckbox('eula', 'Souhlasím s podmínkami.')
                    ->addRule(Form::FILLED, 'Musíte souhlasit.');*/

            $form->addText('spambot', 'Toto musíte vyplnit.')
                    ->addRule(Form::LENGTH, 'Špatně.', 0)
                    ->getControlPrototype()->class("hidden");
            $form['spambot']->getLabelPrototype()->class("hidden");

            $form->addGroup('Povinné údaje')->add($form['username'],$form['password'],$form['mail'],/*$form['eula'],*/$form['spambot']);

            $form->addSubmit('save', 'Souhlasím s podmínkami, registrovat');
            $form->onSuccess[] = callback($this, 'processRegisterForm');

            $form->setAction($this->link("register"));
            return $form;
        }

        public function processRegisterForm(\Nette\Application\UI\Form $form) {
            if($this->template->banned) return false;
            $data = $form->getValues();
            $data["token"] = md5(uniqid());
            $data["create_time"] = time();
            $data["password"] = sha1($data["password"]);
            unset($data['eula']);
            unset($data['spambot']);
            if(!$this->context->database->registration()->where("token = ?", $data["token"])->count()){
                $this->context->database->registration()->insert($data);
            }
            $this->getTemplate()->mail = $data["mail"];
            $this->redirect(301, "mail", serialize(array("mail"=> $data["mail"], "token" => $data["token"])));
            return true;
        }

        /**
         *
         * @param string $data
         */
        public function actionMail($data){
            $data = unserialize($data);
            $l = $this->link("//finish", $data["token"]);

            $template = new \Nette\Templating\FileTemplate(__DIR__. '/../templates/registration/mail/confirm-mail.latte');
            $template->registerFilter(new \Nette\Latte\Engine);
            $template->link =$l;

            $mail = new \Nette\Mail\Message;
            $mail->setFrom($this->config['registration']['mail']);
            $mail->addTo($data['mail']);
            $mail->setHtmlBody($template);
            $mail->send();

            $this->template->mail = $data['mail'];            
      }

        public function actionFinish( $id ){
            $db = $this->context->database;
            if($this->template->banned) return false;
            $this->getTemplate()->message = "";
            $reg = $db->registration()->where("token = ?", $id);
            if(!count($reg)){
                $this->getTemplate()->message = "Registrace nebyla nalezena. Nevypršela už platnost odkazu?";
            }else{
                foreach($reg as $r){
                    $row = $r;
                    break;
                }
                if($this->context->database->users()->where("username LIKE ?", $row["username"])->count() || DB::users()->where("mail = ?", $row["mail"])->count()){
                    $this->getTemplate()->message = "Uživatelské jméno/mail je již obsazen. Je nám líto.";
                    $reg->delete();
                    return false;
                }
                $db->users()->insert(array(
                    "id" => 0,
                    "username" => $row["username"]
                ));
                $r = $db->users_profiles()->insert(array(
                    "id"=>0,
                    "password" => $row["password"],
                    "mail" => $row["mail"],
                    "created" => $row["create_time"],
                    "login"=>0,
                    'urlfragment'=> \Utilities::string2url($row['username'])
                ));
                $db->users_prerferences()->insert(array(
                    "id"=> $r['id'],
                    "color" => "#fff"
                ));

                $reg->delete(); /* Vloží se pouze jednou, ale smažou se všechny odpovídající tokeny */
                $this->getTemplate()->message = "Registrace byla dokončena. Nyní se můžete přihlásit.";
            }
        }

        public function actionRecoverpassword(){

        }

        public function createComponentRecoverPassword(){
            $form = new Form;
            $form->addText('mail', '')
                    ->addRule(Form::FILLED, 'Je nutné vyplnit e-mail.')
                    ->addRule(Form::EMAIL, 'Zadaný e-mail není platný');
            $form->addSubmit('send', 'Obnovit heslo');
            $form->onSuccess[] = callback($this, 'handleRecoverPassword');
            return $form;
        }

        protected function generatePassword($length=9, $strength=0) {
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength & 1) {
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
	if ($strength & 2) {
		$vowels .= "AEUY";
	}
	if ($strength & 4) {
		$consonants .= '23456789';
	}
	if ($strength & 8) {
		$consonants .= '@#$%';
	}

	$password = '';
	$alt = time() % 2;
	for ($i = 0; $i < $length; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
}


        public function handleRecoverPassword(Form $form){
            $vals = $form->getValues();
            $db = $this->context->database;
            $row = $db->users_profiles('mail', $vals->mail);
            if($row->count()){
                $row = $row->fetch();
                $usr = $db->users('id', $row['id'])->fetch();
                $user = $usr['username'];

                $password = $this->generatePassword(9, 1 | 2 | 4);

                $mail = new \Nette\Mail\Message();
                $mail->addTo($vals->mail);
                $mail->setFrom('system@' . $this->context->parameters['servers']['domain']);
                $template = new \Nette\Templating\FileTemplate(__DIR__. '/../templates/registration/mail/recover-password.latte');
                $template->registerFilter(new \Nette\Latte\Engine);
                $template->username = $user;
                $template->password = $password;
                $mail->setHtmlBody($template);
                $mail->send();
                $row->update(array(
                    "password" => sha1($password)
                ));

                $this->flashMessage('Na váš e-mail bylo odesláno nové heslo.');
                $this->redirect('this');
            }else{
                $form->addError('Zadaný mail nebyl nalezen v databázi.');
            }
        }
    }
}