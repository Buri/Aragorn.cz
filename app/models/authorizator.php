<?php

use Nette\Environment;
class Permissions extends Nette\Object{
    private $storage;
    private $uniq_key;
    private $tags;
    private static $instance;
    private function __construct(){
        $user = Environment::getUser();
        $roles = $user->getRoles();
        $this->uniq_key = 'permission_' . $user->getId() . '_' . $roles[0];
        $this->tags = array('permission_user_' . $user->getId(), 'permission_group_' . $roles[0], 'permission_all');
        $this->load();
    }
    
    public function __destruct() {
        /* Has to be moddified */
        /*try{
            #dump($this);
            if(!is_null($this->storage) || $this->modified){
                $this->updateCache();
            }
        /*}
        catch(Exception $e){
            echo $e->getMessage();
        }*/
    }
    
    public static function getInstance(){
        if(!self::$instance)
            self::$instance = new \Permissions ();
        return self::$instance;
    }
    
    private function updateCache(){
        #echo "Updating cache";
        #dump(MC::getInstance());
        MC::write($this->uniq_key, serialize($this->storage), array("tags" => $this->tags));
    }
    public function getId(){
        return $this->uniq_key;
    }
    
    public function getRaw(){
        return $this->storage;
    }
    
    /* Loads permissions for session */
    private function load(){
        $r = MC::read($this->uniq_key);
        if($r){                                     /* Get permissions from cache if posible, else reload it form db */
            $this->storage = unserialize ($r);
        }else{
            $this->forceReload();
        }
    }
    
    /* Removes all permisions from memory/storage */
    public static function unload(){
        $t = empty($this) ? new Permissions() : $this;
        $t->storage = NULL;
        MC::remove($t->getId());
    }
    
    public function forceReload(){
        #echo "Force realoading";
        $this->storage = array();
        foreach(DB::permissions("target", Environment::getUser()->getRoles())->where("type", "group")->union(DB::permissions("target", Environment::getUser()->getId())->where("type", "user")) as $perm){
            if(empty($this->storage[$perm["resource"]])) $this->storage[$perm["resource"]] = array();
            if(is_null($perm["operation"])) $this->storage[$perm["resource"]]['_ALL'] = $perm["value"];
            else $this->storage[$perm["resource"]][$perm["operation"]] = $perm["value"];
        }    
        #dump($this->storage);
        $this->updateCache();
    }
    
    public function get($resource, $priviledge, $forceReload = false){
        if($forceReload) $this->forceReload ();
        #dump($this->storage);
        if(empty($this->storage[$resource])) return false;                              /* Permission has not been defined */
        #dump("pass1");
        if(is_array($this->storage[$resource])){                                        /* Access with exact permission */
            return (!isset($this->storage[$resource][$priviledge]) ? (isset($this->storage[$resource]['_ALL']) ? $this->storage[$resource]['_ALL'] : false) : $this->storage[$resource][$priviledge]) ? true : false; /* If permission for operation is not set, it will try to use global permision for resource */
        }
        #dump("Pass 2");
        return false;                                                                   /* Fallback */
    }
    
    public function hasPermissionSet($permission){
        return isset($this->storage[$permission]);
    }
    
    public function setResource($resource, array $permissions, $override = false){
        if(!$this->hasPermissionSet($resource) || $override){
            $this->storage[$resource] = $permissions;
        }
    }
    public function setOwner($resource){
        $this->storage[$resource] = array("_ALL" => true);
        $this->storage[$resource]['owner'] = true;
    }
    public function dump(){
        #dump($this->storage);
    }
}

class UserAuthorizator extends Nette\Object implements Nette\Security\IAuthorizator
{
    private static $instance;
    public static function getInstance(){
        #dump('UserAuthorizator::getInstance()');
        if(!self::$instance){
            self::$instance = Permissions::getInstance();
        }
        return self::$instance;
    }
    public function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL)
    {
        /* Role must be defined */
        if($role == null || !Environment::getUser()->isLoggedIn()) return false;
        /* root is allowed to do anything */
        #dump($role == 0);
        if($role == 0 || Environment::getUser()->getId() == 0 || (is_array($role) && in_array(0, $role))) return true; 
        #dump('Nonadmin');
        /* Return final priviledge */
        return self::getInstance()->get($resource, $privilege);
    }
}
