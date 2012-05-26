<?php

use Nette\Environment;

class UserAuthorizator extends Nette\Object implements Nette\Security\IAuthorizator
{
    private static $instance;
    /**
     *
     * @var Permissions
     */
    private $permissions;
    /**
     *
     * @param Permissions $permissions
     */
    function __construct(Permissions $permissions){
        $this->permissions = $permissions;
    }
    public function getPermissionsInstance(){
        return $this->permissions;
    }
    public function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL)
    {
        /* Role must be defined */
        if($role == null || !Environment::getUser()->isLoggedIn()) return false;
        /* root is allowed to do anything */
        
        if($role == 0 || Environment::getUser()->getId() == 0 || (is_array($role) && in_array(0, $role))) return true; 
        
        /* Return final priviledge */
        return self::getInstance()->get($resource, $privilege);
    }
}
