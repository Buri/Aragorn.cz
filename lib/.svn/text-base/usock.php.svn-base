<?php
/**
 * Description of usock
 *
 * @author Buri
 */
class usock {
    //put your code here
    static $handle;
    
    private function __construct(){}
    
    static function getInstance(){
        if(!self::$handle){
            self::$handle = fsockopen("unix:///tmp/nodejs.socket", NULL);
        }
        return self::$handle;
    }
    
    static function close(){
        fclose(self::getInstance());
    }
    static function write($content){
        fwrite(self::getInstance(), $content);
    }
    static function writeClose($content){
        fwrite(self::getInstance(), $content);
        self::close();
    }
    static function writeRead($content, $l){
        fwrite(self::getInstance(), $content);
        return self::read($l);
    }
    static function read($l){
        return fread(self::getInstance(), $l);
    }
    static function readClose($l){
        $d = fread(self::getInstance(), $l);
        self::close();
        return $d;
    }
    
    static function writeReadClose($c, $l){
        self::write($c);
        return self::readClose($l);
    }
}
