<?php

class BasePresenter extends Nette\Application\UI\Presenter{
    
    var $cache = null;
    public function startup(){
        parent::startup();
        
        /* Panely */
        \Extras\Debug\RequestsPanel::register();
        \Panel\User::register();
        $this->getCache();
        
        $t = $this->getTemplate();
        $t->registerHelper('r', function($ar, $i = null){
            return $i == null ? implode(", ", $ar) : $ar[$i];
        });
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
    
    protected function getCache($ns = true){
        if($ns)
            $this->cache = new \Nette\Caching\Cache(new \Nette\Caching\Storages\FileStorage(TEMP_DIR), $this->name);
        else
            $this->cache = new \Nette\Caching\Cache(new \Nette\Caching\Storages\FileStorage(TEMP_DIR));
        return $this->cache;
    }
    
    public function userLink($id = null, $html = true){
        $c = $this->getCache(false);        // Set global cache
        $cname = 'user-link' . $id . ($html ? '-html' : '');
        $link = $c->load($cname);
        if($link !== null){
            $this->getCache();
            return $link;    
        }
        if($id == null) $id = \Nette\Environment::getUser()->getId();
        if($id == null) return false;
        $u = DB::users('id', $id)->fetch();
        $v = $u->users_profiles()->fetch();
        $n = $v['urlfragment'];
        $link = $this->link(':frontend:users:profile', $n);
        if($html){
            $role = $u['groupid'];
            switch($role){
                case 0: 
                    $role = 'role-root';
                    break;
                case 1:
                    $role = 'role-moderator';
                    break;
                case 2:
                    $role = 'role-user';
                    break;
                default:
                    $role = 'role-guest';
            }
            return "<a href=\"".$link."\" class=\"$role ajax\">".$u['username']."</a>\n";
        }
        $c->save($cname, $link);
        $this->getCache(true);      // Reset back to local cache
        return $link;
    }
    
    public function userStatus($id = null){
        if($id == null) $id = \Nette\Environment::getUser()->getId ();
        $u = DB::users_profiles('id', $id)->fetch();
        return $u['status'];
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
        $c = $this->getCache(false); // Global cache
        $uid = "user-profile-" . $user['id'] . '-' . $format . '-' . implode('-', $other);
        $profile = $c->load($uid);
        if($profile !== null){
            $this->getCache();
            return $profile;
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
        $c->save($uid, $profile);
        $this->getCache();
        return $profile;
    }

    public function createComponentLogInForm(){
        $form = new Nette\Application\UI\Form;
        /*$this['logInForm'] = $form;
        $form->setAction($this->link(':frontend:dashboard:'));*/
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
            DB::users_profiles('id', $user->getId())->update(array('login'=>time()));
            $path = 'safe://' . APP_DIR . '/../db/user_ip_addresses/' . $user->getId() . '.txt';
            if(!file_exists($path)){
                $fp = fopen($path, 'w');
                $ips = "";
            }else{
                $size = filesize($path);
                $fp = fopen($path, 'a+');
                rewind($fp);
                $ips = fread($fp, filesize($path));
            }
            if($ips){
                $ips = explode(';', $ips);
            }else{
                $ips = array();
            }
            if(array_search($_SERVER['REMOTE_ADDR'], $ips) === false){
                $ips[] = $_SERVER['REMOTE_ADDR'];
                rewind($fp);
                fwrite($fp, implode(';', $ips));
            }
            fclose($fp);
            $sid = Node::userlogin();
            setCookie('sid', $sid, 0, '/');
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
        catch(\Nette\Security\AuthenticationException $e){
            $this->redirect(301, 'badlogin:');
        }
    }

    public function actionLogout(){
        $data = '{"command":"user-logout","data":{"nodeSession":"'.$_COOKIE['sid'].'"}}';
        setCookie('sid', usock::writeReadClose($data, 4096), 0, '/');
        Permissions::unload();
        Nette\Environment::getUser()->logout(true);
        $this->redirect(301, "dashboard:default");
    }

}
