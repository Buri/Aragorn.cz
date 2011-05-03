<?php

class BasePresenter extends NPresenter{

    public function  __construct(IComponentContainer $parent = NULL, $name = NULL) {
        $this->getTemplate()->staticPath = (!empty($_SERVER["HTTPS"]) ? "https" : "http") . "://" . NEnvironment::getVariable("staticServer", "www.aragorn.cz");
        parent::__construct($parent, $name);
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
        $form = new NAppForm;
        $form->getElementPrototype()->class = "logInForm";
        $form->setAction($this->link("login:"));
        $form->addText("username", "Nick:");
        $form->addPassword("password", "Heslo:");
        $form->addSubmit("login", "Přihlásit");
        $form->onSubmit[] = callback($this, "handleLogin");
        return $form;
    }

    public function handleLogin($form){
        $v = $form->getValues();
        try{
            NEnvironment::getUser()->login($v["username"], $v["password"]);
            /* Sync with node.js */
            $data = '{"command":"user-login","data":{"PHPSESSID":"'.session_id().
            '","nodeSession":"'.$_COOKIE["sid"].
            '","id":'.NEnvironment::getUser()->getIdentity()->getId().
            ',"username":"'.addslashes(NEnvironment::getUser()->getIdentity()->data["username"]).'"}}';
            usock::writeReadClose($data, 4096);
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
            $this->redirect(301, 'neplatneprihlaseni:');
        }
    }

    public function actionLogout(){
        NEnvironment::getUser()->logout();
        $data = '{"command":"user-logout","data":{"nodeSession":"'.$_COOKIE["sid"].'"}}';
        usock::writeReadClose($data, 4096);
        $this->redirect(301, "dashboard:default");
    }

}
