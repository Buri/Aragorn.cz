<?php

class BasePresenter extends Nette\Application\UI\Presenter{

    public function startup(){
        parent::startup();
        
        /* Panely */
        \Extras\Debug\RequestsPanel::register();
        \Panel\User::register();
        
        
        $t = $this->getTemplate();
        $t->staticPath = (!empty($_SERVER["HTTPS"]) ? "https" : "http") . "://" . Nette\Environment::getVariable("staticServer", "www.aragorn.cz");
        $t->userPath = (!empty($_SERVER["HTTPS"]) ? "https" : "http") . "://" . Nette\Environment::getVariable("userServer", "www.aragorn.cz");
        $this->userpath = $t->userPath;
        $t->title = "";
        $t->forceReload = false;
        $t->ajax = $this->isAjax();
        $this->invalidLinkMode = \Nette\Application\UI\Presenter::INVALID_LINK_EXCEPTION;
        if($t->user->getIdentity() && DB::bans()->where('expires > ? AND (user = ? OR ip LIKE ?) ', time(), $t->user->getId(), "%".$_SERVER["REMOTE_ADDR"]."%")->count() > 0){
            $this->actionLogout();
        }
    }
    
    public function userLink($id = null, $html = false){
        if($id == null) $id = \Nette\Environment::getUser()->getId ();
        $u = DB::users('id', $id)->fetch();
        $n = $u['username'];
        $link = $this->link(':frontend:users:profile', $n);
        return $html ? "<a href=\"".$link."\">".$n."</a>\n" : $link;
    }
    
    public function userIcon($id = null){
        if($id == null) $id = \Nette\Environment::getUser()->getId ();
        $i = DB::users_profiles('id', $id)->fetch();
        if($i['icon'])
            $ic = $i['icon'];
        else
            $ic = "default.png";
        return $this->userpath."/i/".$ic;
    }
    
    public static $UP_ICON = 1, $UP_STATUS = 2, $UP_ACTIVITY = 4, $UP_LOCATION = 8;
    private $userpath;
    public function userProfile(array $user, $format = 11, $other = array("class"=>"", "id"=>"")){
        if(!empty($user['id']) && $user['id'] != "0"){
            if(isset($user['name'])){
                $u = DB::users('username', $user['name'])->fetch();
            }elseif(isset($user['mail'])){
                $u = DB::users_profiles('mail', $user['mail'])->fetch();
            }else{
                throw new Exception("Bad format for user query.");
            }
            $user['id'] = $u['id'];
        }
        $profile = "<div class=\"userprofile";
        if($other["class"])
            $profile .= " ".$other["class"];
        $profile .= "\"";
        if($other["id"])
            $profile .= " id=\"".$other["id"] . "\"";
        $profile .= ">\n";
        $profile .= $this->userLink($user['id'], true);
        if($format & self::$UP_ICON){
            $profile .= "<img src=\"".$this->userIcon($user['id'])."\" alt=\"Ikona uživatele\" />\n";
        }
        if($format & self::$UP_ACTIVITY){
            // Query node.js here
        }
        if($format & self::$UP_STATUS){
            $i = DB::users_profiles($user['id'])->fetch();
            $profile .= "<span class=\"profilestatus\">".htmlspecialchars($i['status'])."</span>\n";
        }
        
        $profile .= "</div>\n";
        return $profile;
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
