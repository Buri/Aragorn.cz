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
            $mccfg = Nette\Environment::getConfig('memcache');
            $journal = new Nette\Caching\Storages\FileJournal(TEMP_DIR);
            self::$instance = new Nette\Caching\Storages\MemcachedStorage($mccfg["host"], $mccfg["port"], '', $journal);
        }
        return self::$instance;
    }
    public static function getMemcachedInstance(){
        if(!self::$memcached){
            $mccfg = Nette\Environment::getConfig('memcache');
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
