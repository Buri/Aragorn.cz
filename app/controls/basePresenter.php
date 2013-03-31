<?php

class BasePresenter extends Nette\Application\UI\Presenter{

    /**
     *
     * @var Nette\Caching\Cache
     */
    var $cache = null;
    
    /**
     *
     * @var Node
     */
    protected $node;

    /**
     *
     * @var Permissions
     */
    protected $permissions;
    
    /**
     *
     * @param \Nette\DI\Container $container
     * @param Node $node 
     */
    public function __construct(\Nette\DI\Container $context) {
        parent::__construct($context);
        $this->node = $context->Node;
        $this->permissions = $context->Permissions;
    }
    
    /**
     *
     * @return void
     */
    public function startup(){
        parent::startup();
        
        /* Panely */
        if(false && $this->context->parameters['debug']['force'] === true) { // dev only
            // service conflict with @nette.mail
            Nette\Mail\Message::$defaultMailer = new \Schmutzka\Diagnostics\DumpMail($this->getContext()->session);
            \Nette\Diagnostics\Debugger::addPanel(new \Schmutzka\Panels\DumpMail($this->getContext()->session));
            \Extras\Debug\RequestsPanel::register();
            \Panel\User::register();
            \Panel\Navigation::register();
        }

        /* Template shortcut */
        $t = $this->getTemplate();
        
        $this->checkBans();
        
        /* Setup template variables */
        $t->registerHelper('r', function($ar, $i = null){
            return $i == null ? implode(", ", $ar) : $ar[$i];
        });
        $t->staticPath = (!empty($_SERVER["HTTPS"]) ? "https" : "http") . "://" . $this->context->parameters['servers']['staticContent'] ;
        $t->userPath = $this->userpath = (!empty($_SERVER["HTTPS"]) ? "https" : "http") . "://" . $this->context->parameters['servers']['userContent'] ;
        $t->title = "";
        $t->forceReload = false;
        $t->ajax = $this->isAjax();
        $t->node = $this->node;
        $t->startTime = $this->context->parameters['starttime'];
        $t->db = $this->context->database;
        $t->bookmarks = $this->getBookmarks();
        $t->deferScripts = $this->context->parameters['performance']['deferScripts'];
        /* Fix invalid links generation */
        #$this->invalidLinkMode = \Nette\Application\UI\Presenter::INVALID_LINK_EXCEPTION;
    }

    protected function checkBans(){
        /* If user is banished from server, log him out */
        $uid = $this->context->user->getId();
        if($this->context->user->getIdentity() && $this->context->database->bans()
                ->where('expires > ? AND (user = ? OR ip LIKE ?) ', time(), $uid, "%".$_SERVER["REMOTE_ADDR"]."%")
                ->count() > 0){
            $this->actionLogout();
        }
        
    }
    
    protected function getCache($ns = true){
        if($ns)
            $this->cache = new \Nette\Caching\Cache(new \Nette\Caching\Storages\MemcachedStorage(), $this->name);
        else
            $this->cache = new \Nette\Caching\Cache(new \Nette\Caching\Storages\MemcachedStorage());
        return $this->cache;
    }
    
    public function userLink($id = null, $html = true){
        if($id == null) $id =$this->context->user->getId();
        if($id == null) return false;
        $c = $this->getCache(false);        // Set global cache
        $cname = 'user-link-' . $id . ($html ? '-html' : '');
        //dump($cname);
        $link = $c->load($cname);
        if($link !== null){
           // dump("cached link");
            $this->getCache();
            return $link;    
        }
        //dump("uncached link");
        $db = $this->context->database;
        $u = $db->users('id', $id)->fetch();
        $n = $n['url'];
        $link = $this->link('users:view', $n);
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
            $link = "<a href=\"".$link."\" class=\"$role ajax user-link\" data-profile=\"$n\">".$u['username']."</a>\n";
        }
        $c->save($cname, $link);
        $this->getCache(true);      // Reset back to local cache
        return $link;
    }
    
    public function userStatus($id = null){
        if($id == null) $id = $this->getUser()->getId ();
        $u = $this->context->database->users('id', $id)->fetch();
        return $u['status'];
    }
    
    public function userIcon($id = null){
        if($id == null) $id = $this->getUser()->getId ();
        $i = $this->context->database->users('id', $id)->fetch();
        if($i['icon'])
            $ic = $i['icon'];
        else
            $ic = "default.png";
        return $this->userpath."/i/".$ic;
    }
    
    public static $UP_ICON = 1, $UP_STATUS = 2, $UP_ACTIVITY = 4, $UP_LOCATION = 8;
    private $userpath;
    public function userProfile(array $user, $format = 11, $other = array("class"=>"", "id"=>"")){
        $db = $this->context->database;
        if(!empty($user['id']) && $user['id'] != "0"){
            if(isset($user['name'])){
                $u = $db->users('username', $user['name'])->fetch();
            }elseif(isset($user['mail'])){
                $u = $db->users('mail', $user['mail'])->fetch();
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
            $i = $db->users($user['id'])->fetch();
            $profile .= "<span class=\"profilestatus\">".htmlspecialchars($i['status'])."</span>\n";
        }
        
        $profile .= "</div>\n";
        $c->save($uid, $profile);
        $this->getCache();
        return $profile;
    }

    public function createComponentLogInForm(){
        $form = new Components\LoginForm();
        $form->build();
        $form->onSuccess[] = callback($this, "userLogin");
        return $form;
    }
    
    public function createComponentForum($name){
        $c = new Components\ForumComponent;
        return $c->setContext($this->context);
    }
    
    public function actionLogin(){
        $this->handleLogin(array("username"=>"", "password"=>""), 'google');
    }
    
    public function userLogin($form){
        $v = $form->getValues();
        $this->handleLogin($v, 'user');
    }
    
    public function handleLogin($v, $btn = 'login'){
        $user = $this->context->user;
        $user->setAuthenticator($this->context->authenticator);
        try{
            $user->login($v["username"], $v["password"]);
            /*dump($v);
            exit;*/
            if($v['forever']){
                $user->setExpiration('+ 1 year', false);
            }else{
                $user->setExpiration('+ 60 minutes', true, true);
            }
            $this->context->database->users('id', $user->getId())->update(array('login'=>time()));
            $path = 'safe://' . APP_DIR . '/../db/user_ip_addresses/' . $user->getId() . '.txt';
            if(!file_exists($path)){
                $fp = fopen($path, 'w');
                $ips = "";
            }else{
                $size = filesize($path);
                $fp = fopen($path, 'a+');
                rewind($fp);
                $ips = fread($fp, $size);
            }
            if($ips){
                $ips = explode(';', $ips);
            }else{
                $ips = array();
            }
            $out = array();
            $found = false;
            foreach($ips as $ip){
                //if(array_search(, $ips) === false){
                $ipa = explode('=', $ip);
                if($ipa[0] == $_SERVER['REMOTE_ADDR']){
                    $found = true;
                    $ipa[1] = $ipa[1] + 1;
                }
                $out[] = implode('=', $ipa);
            }
            if(!$found){
                $out[] = $_SERVER['REMOTE_ADDR'] . "=" . 1;
            }
            rewind($fp);
            fwrite($fp, implode(';', $out));
            fclose($fp);
            $sid = $this->node->userlogin($user->getId(), $this->context->Permissions, $user);
            setCookie('sid', $sid, 0, '/');
            $this->flashMessage("Přihlášení bylo úspěšné");
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
            $this->flashMessage("Špatné uživatelské jméno nebo heslo");
            $this->redirect(301, 'badlogin:');
        }
    }

    public function actionLogout(){
        $data = '{"command":"user-logout","data":{"nodeSession":"'.$_COOKIE['sid'].'"}}';
        setCookie('sid', $this->node->getConnection()->writeReadClose($data, 4096), 0, '/');
        $this->permissions->unload();
        $this->context->user->logout(true);
        $this->flashMessage("Odhlášení proběhlo úspěšně");
        $this->redirect(301, "dashboard:default");
    }

    public function createComponentSearchForm($name){
        $form = new \Nette\Application\UI\Form($this, $name);
        $form->addText('q', 'Hledat')
                ->addRule(Nette\Application\UI\Form::FILLED)
                ->setAttribute('placeholder', 'Hledat');
        $form->addSubmit('search', 'Hledat');
        $form->setAction($this->link('search:'));
        $form->setMethod('get');
        return $form;
    }

    public function getBookmarks(){
        if(!$this->user->isLoggedIn()) return array();
        
        $db = $this->context->database;
        $bookmarks = array();
        foreach($db->forum_visit(array('iduser' => $this->user->getId(), 'bookmark' => 1)) as $bookmark){
            //{var $b = $db->forum_topic('id', $bm['idforum'])->fetch()} n:href="forum $b['urlfragment']">{$b['name']}</a>

        }
        return $bookmarks;
    }

}
