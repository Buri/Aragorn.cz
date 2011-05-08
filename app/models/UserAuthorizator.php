<?php

class UserAuthorizator extends NObject implements IAuthorizator
{
    public function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL)
    {
        /* Role must be defined */
        if($role === null) return false;
        /* root is allowed to do anything */
        if($role == 0 || NEnvironment::getUser()->getId() == 0) return true; 
        
        /* Better safe than sorry */
        $priv = false;
        
        /* Group access to all operations on selected resource */
        foreach(DB::permissions("target", $role)->where("type", "group")->where("resource", $resource)->where("operation", null) as $permission)
            $priv = $permission["value"];
        
        /* Group access to queried operations on selected resource, overrides global group access */
        foreach(DB::permissions("target", $role)->where("type", "group")->where("resource", $resource)->where("operation", $privilege) as $permission)
            $priv = $permission["value"];
        
        /* User access to all operations on selected resource, overrides group access */
        foreach(DB::permissions("target", NEnvironment::getUser()->getId())->where("type", "user")->where("resource", $resource)->where("operation", null) as $permission)
            $priv = $permission["value"];
        /* User access to queried operations on selected resource, overrides all */
        foreach(DB::permissions("target", NEnvironment::getUser()->getId())->where("type", "user")->where("resource", $resource)->where("operation", $privilege) as $permission)
            $priv = $permission["value"];
        
        /* Return final priviledge */
        return $priv;
    }
}
