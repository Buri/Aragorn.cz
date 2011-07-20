<?php

class BasePresenter extends Nette\Application\UI\Presenter{

    public function startup(){
        $t = $this->getTemplate();
        $t->staticPath = (!empty($_SERVER["HTTPS"]) ? "https" : "http") . "://" . Nette\Environment::getVariable("staticServer", "www.aragorn.cz");
        $t->title = "";
        $t->forceReload = false;
        $t->ajax = $this->isAjax();
        
        if(DB::bans()->where('expires > ? AND (id = ? OR ip LIKE ?) ', time(), $t->user->getId(), "%".$_SERVER["REMOTE_ADDR"]."%")->count()){
            $this->actionLogout();
        }
        
        parent::startup();
    }
    protected function createComponent($name) {
        $class = ucfirst($name);
        if( !method_exists($this, "createComponent$class") )
        {
                if( class_exists($class) )
                {
                        return new $class($this, $name);
                }
        }
        return parent::createComponent($name);
    }

    public function createComponentLogInForm(){
        $form = new Nette\Application\UI\Form;
        $form->getElementPrototype()->class = "logInForm";
        $form->addText("username", "Nick:");
        $form->addPassword("password", "Heslo:");
        $form->addCheckbox('forever', "Trvalé přihlášení");
        $form->addSubmit("login", "Přihlásit");
        $form->addImage('google', $this->getTemplate()->staticPath . '/images/google-login-button.png', 'Přihlásit pomocí účtu Google')
                ->onClick[] = callback($this, "googleLogin");
        $form->onSuccess[] = callback($this, "userLogin");
        return $form;
    }
    
    public function actionLogin(){
        $this->handleLogin(array("username"=>"", "password"=>""), 'google');
    }
    
    public function userLogin($form){
        $v = $form->getValues();
        $this->handleLogin($v, 'user');
    }
    
    public function googleLogin($form){
        $this->handleLogin(array("username"=>"google.com", "password"=>""), 'google');
    }
    public function handleLogin($v, $btn = 'login'){
        $user = Nette\Environment::getUser();
        switch($btn){
            case 'google':
                $user->setAuthenticator(new GoogleAuthenticator);
                break;
            case 'user':
            default:
                $user->setAuthenticator(new UserAuthenticator);
                break;
        }
        try{
            $user->login($v["username"], $v["password"]);
            if($v['forever']){
                $user->setExpiration(0, false);
            }
            Node::userlogin();
            $this->redirect(301, 'this');
        }
        catch(BanAuthenticationException $e){
            $b = $e->getBan();
            $u = explode(";", $e->getMessage());
            $d = array("username"=> $u[1],
                "author" => (string) $u[0],
                "time" => (string)date("H:i:s d.m.Y", $b["time"]),
                "expires" => (string)date("H:i:s d.m.Y", $b["expires"]),
                "reason" => (string)$b["reason"],
                "ip" => (string)$b["ip"]
                );
            $_SESSION["ban"] = serialize($d);
            $this->redirect(301, 'ban:');
        }
        catch(NAuthenticationException $e){
            $this->redirect(301, 'badlogin:');
        }
    }

    public function actionLogout(){
        Permissions::unload();
        Nette\Environment::getUser()->logout(true);
        $data = '{"command":"user-logout","data":{"nodeSession":"'.$_COOKIE["sid"].'"}}';
        usock::writeReadClose($data, 4096);
        $this->redirect(301, "dashboard:default");
    }

}
