<?php

/* Singleton static class to access main memcache server
 *
 *  usage:
 *      MC::set("foo", "bar");
 *      echo MC::get("foo"); // bar
 *
 */
class MC {
    private function __construct(){}
    private function __clone(){}
    private static $instance;
    public static function getInstance(){
        if(!self::$instance){
            $mccfg = NEnvironment::getConfig('memcache');
            self::$instance = new Memcache;
            self::$instance->connect($mccfg["host"], $mccfg["port"]); # or die ("Could not connect to memcahe server");
        }
        return self::$instance;
    }
    final public static function __callStatic( $chrMethod, $arrArguments ) {

        $objInstance = self::getInstance();

        return call_user_func_array(array($objInstance, $chrMethod), $arrArguments);

    }
}
