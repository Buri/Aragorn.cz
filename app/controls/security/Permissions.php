<?php

/**
 * Description of Permissions
 *
 * @author Buri <buri.buster@gmail.com>
 */

class Permissions extends Nette\Object{
    /**
     *
     * @var array Computed permission asociative array
     */
    protected $storage;

    /**
     *
     * @var string Unique permission id
     */
    protected $uniq_key;
    /**
     *
     * @var array User and group identification for invalidating permissions
     */
    protected $tags;
    /**
     *
     * @var Nette\Caching\Cache
     */
    protected $cache;

    /**
     *
     * @var Nette\DI\Container
     */
    protected $context;

    /**
     *
     * @param Nette\DI\Container $container
     */
    public function __construct(Nette\DI\Container $context){
        $this->context = $context;
        $user = $context->user;
        $roles = $user->getRoles();
        $this->uniq_key = 'permission_' . $user->getId() . '_' . $roles[0];
        $this->cache = new Nette\Caching\Cache($context->cacheStorage, 'permissions');
        $this->tags = array('permission_user_' . $user->getId(), 'permission_group_' . $roles[0], 'permission_all');
        $this->load();
    }

    /**
     *
     * @return array User Roles
     */
    public function getRoles(){
        return $this->context->user->getRoles();
    }

    /**
     *
     * @return int User ID
     */
    public function getUID(){
        return $this->context->user->getId();
    }

    /**
     * Write current storage into cache
     */
     private function updateCache(){
        $this->cache->save($this->uniq_key, $this->storage, array(
            Nette\Caching\Cache::TAGS => $this->tags)
        );
    }

    /**
     *
     * @return string
     */
    public function getId(){
        return $this->uniq_key;
    }

    /**
     *
     * @return array
     */
    public function getRaw(){
        return $this->storage;
    }

    /* Loads permissions for session */
    private function load(){
        /* Get permissions from cache if posible, else reload it form db */
        $r = $this->cache->load($this->uniq_key);
        if($r === null){                                     
            $this->forceReload();
        }else{
            $this->storage = $r;
        }
    }

    /* Removes all permisions from memory/storage */
    public function unload(){
        $this->cache->clean();
        $this->storage = NULL;
    }

    /**
     * Force fetching permissions from db
     */
    public function forceReload(){
        /** @var Nette\User */
        $user = $this->context->user;
        /** @var array */
        $this->storage = array();

        /* get actual premissons from database */
        foreach(DB::permissions("target", $user->getRoles())
                ->where("type", "group")
                ->union(DB::permissions("target", $user->getId())
                        ->where("type", "user")) as $perm){
            
            if(empty($this->storage[$perm["resource"]]))
                $this->storage[$perm["resource"]] = array();
            if(is_null($perm["operation"]))
                $this->storage[$perm["resource"]]['_ALL'] = $perm["value"];
            else
                $this->storage[$perm["resource"]][$perm["operation"]] = $perm["value"];
        }

        /* And cache result */
        $this->updateCache();
    }

    /**
     *
     * @param string $resource
     * @param string $priviledge
     * @param boolean $forceReload
     * @return boolean
     */
    public function get($resource, $priviledge, $forceReload = false){
        /* Required to use noncached permissions */
        if($forceReload) 
            $this->forceReload ();
        /* Permission has not been defined */
        if(empty($this->storage[$resource]))
            return false;

        /* Access with exact permission */
        if(is_array($this->storage[$resource])){                                        
            return (!isset($this->storage[$resource][$priviledge]) ? (isset($this->storage[$resource]['_ALL']) ? $this->storage[$resource]['_ALL'] : false) : $this->storage[$resource][$priviledge]) ? true : false; /* If permission for operation is not set, it will try to use global permision for resource */
        }
        
        /* Fallback */
        return false;                                                                   
    }

    /**
     *
     * @param string $permission
     * @return boolean
     */
    public function hasPermissionSet($permission){
        return isset($this->storage[$permission]);
    }

    /**
     *
     * @param string $resource
     * @param array $permissions 
     * @param boolean $override Override permissions that are already set
     */
    public function setResource($resource, array $permissions, $override = false){
        if(!$this->hasPermissionSet($resource) || $override){
            $this->storage[$resource] = $permissions;
        }
    }

    /**
     * Set current user as owner of $resource
     * @param type $resource
     */
    public function setOwner($resource){
        $this->storage[$resource] = array("_ALL" => true);
        $this->storage[$resource]['owner'] = true;
        return $this;
    }
}
