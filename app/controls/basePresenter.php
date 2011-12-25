<?php

class BasePresenter extends Nette\Application\UI\Presenter{

    public function startup(){
        $t = $this->getTemplate();
        $t->staticPath = (!empty($_SERVER["HTTPS"]) ? "https" : "http") . "://" . Nette\Environment::getVariable("staticServer", "www.aragorn.cz");
        $t->title = "";
        $t->forceReload = false;
        $t->ajax = $this->isAjax();
        $this->invalidLinkMode = \Nette\Application\UI\Presenter::INVALID_LINK_EXCEPTION;
        if($t->user->getIdentity() && DB::bans()->where('expires > ? AND (user = ? OR ip LIKE ?) ', time(), $t->user->getId(), "%".$_SERVER["REMOTE_ADDR"]."%")->count() > 0){
            $this->actionLogout();
        }
        parent::startup();
    }

    public function createComponentLogInForm(){
        $form = new Nette\Application\UI\Form;
        $form->getElementPrototype()->class = "logInForm";
        $form->addText("username", "Nick:");
        $form->addPassword("password", "Heslo:");
        $form->addCheckbox('forever', "Trvalé přihlášení");
        $form->addSubmit("login", "Přihlásit");
        $form->onSuccess[] = callback($this, "userLogin");
        return $form;
    }
    
    public function createComponentForum($id){
        return new frontendModule\ForumComponent();
    }
    public function createComponentDiscussion($id){
        return new frontendModule\DiscussionComponent();
    }
    
    public function actionLogin(){
        $this->handleLogin(array("username"=>"", "password"=>""), 'google');
    }
    
    public function userLogin($form){
        $v = $form->getValues();
        $this->handleLogin($v, 'user');
    }
    
    public function handleLogin($v, $btn = 'login'){
        $user = Nette\Environment::getUser();
        $user->setAuthenticator(new UserAuthenticator);
        try{
            $user->login($v["username"], $v["password"]);
            if($v['forever']){
                $user->setExpiration(0, false);
            }else{
                $user->setExpiration('+ 60 minutes', false, true);
            }
            $sid = 321; #Node::userlogin();
            setCookie('sessid', $sid);
            //$_COOKIE['sessid'] = $sid;
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
        $data = '{"command":"user-logout","data":{"nodeSession":"'.'"}}';
        //usock::writeReadClose($data, 4096);
        $this->redirect(301, "dashboard:default");
    }

}
