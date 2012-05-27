<?php

/**
 * Description of NodeConector
 *
 * @author Buri <buri.buster@gmail.com>
 */
class NodeConnector extends Nette\Object{
    /**
     *
     * @var resource
     */
    private $connection;
    
    /**
     *
     * @var string
     */
    private $dns;
    
    /**
     *
     * @param string $dns
     */
    public function __construct($dns){
        $this->dns = $dns;
        $this->connect($this->dns);
    }
    
    /**
     *
     * @param string $dns
     * @return resource
     */
    public function connect($dns){
        if(!$dns) $dns = $this->dns;
        $this->connection = fsockopen($dns, NULL);
        return $this->connection;
    }
    
    /**
     *
     * @return boolean 
     */
    public function close(){
        if(!$this->connection) return false;
        return fclose($this->connection);
    }
    
    /**
     *
     * @param string $content 
     */
    public function write($content){
        fwrite($this->connection, $content);
    }
    
    /**
     *
     * @param string $content
     * @return boolean
     */
    public function writeClose($content){
        if(!fwrite($this->connection, $content)) return false;
        return $this->close();
    }
    
    /**
     *
     * @param string $content
     * @param int $length
     * @return string|false
     */
    public function writeRead($content, $length){
        if(! fwrite($this->connection, $content)) return false;
        return $this->read($length);
    }
    
    /**
     *
     * @param int $length
     * @return string|false
     */
    public function read($length){
        return fread($this->connection, $length);
    }
    
    /**
     *
     * @param int $length
     * @return string|false
     */
    public function readClose($length){
        $d = fread($this->connection, $length);
        $this->close();
        return $d;
    }
    
    /**
     *
     * @param string $content
     * @param int $length
     * @return string|false
     */
    public function writeReadClose($content, $length){
        $this->write($content);
        return $this->readClose($length);
    }

}

