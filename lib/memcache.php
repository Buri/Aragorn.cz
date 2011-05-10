<?php

/* Singleton static class to access main memcache server
 *
 *  usage:
 *      MC::set("foo", "bar");
 *      echo MC::get("foo"); // bar
 */

class MC {
    private function __construct(){}
    private function __clone(){}
    private static $instance;
    private static $memcached;
    public static function getInstance(){
        if(!self::$instance){
            $mccfg = NEnvironment::getConfig('memcache');
            self::$instance = new NMemcachedStorage($mccfg["host"], $mccfg["port"], '', new NFileJournal(TEMP_DIR));
        }
        return self::$instance;
    }
    public static function getMemcachedInstance(){
        if(!self::$memcached){
            $mccfg = NEnvironment::getConfig('memcache');
            self::$memcached = new Memcache;
            self::$memcached->connect($mccfg["host"], $mccfg["port"]); # or die ("Could not connect to memcahe server");
        }
        return self::$memcached;
    }
    final public static function __callStatic( $chrMethod, $arrArguments ) {

        $objInstance = self::getInstance();

        return call_user_func_array(array($objInstance, $chrMethod), $arrArguments);

    }
}
