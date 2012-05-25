<?php

class Node extends Nette\Object{
    
    /**
     *
     * @var NodeConnector
     */
    protected $connection;
    
    /**
     *
     * @param NodeConnector $connection 
     */
    function __construct(NodeConnector $connection) {
        $this->connection = $connection;
    }
    
    /**
     *
     * @return NodeConnector
     */
    public function getConnection(){
        return $this->connection;
    }

    /**
     *
     * @param int $sid
     * @return string
     */
    public function userlogin($sid){
        if(!$sid) $sid = $_COOKIE["sid"];
        $user = Nette\Environment::getUser();
        $p = \Permissions::getInstance();
        $idt = $user->getIdentity();
        if($idt){
            $id = $idt->getId();
            $roles = $idt->getRoles ();
            $username = $idt->username;
            $preferences = $idt->preferences;
        }else{
            $roles = null;
            $id = null;
            $username = null;
            $preferences = null;
        }
        $data = json_encode(array("command" => "user-login",
                "data" => array("PHPSESSID" => session_id(),
                    "nodeSession" => $sid,
                    "roles" => $roles,
                    "id" => $id,
                    "username" => $username,
                    "preferences" => $preferences,
                    "permissions" => $p->getRaw()
                )));
        return $this->connection->writeReadClose($data, 4096);
    }
    
    static function changeStatus($status){
        $user = Nette\Environment::getUser();
        $data = json_encode(array("command" => "user-status-set",
                "data" => array("PHPSESSID" => session_id(),
                    "nodeSession" => $_COOKIE["sid"],
                    "id" => $user->getIdentity()->getId(),
                    "username" => $user->getIdentity()->username,
                    "status" => $status
                )));
        return $this->connection->writeReadClose($data, 4096);
    }
    
    /**
     *
     * @param int $id
     * @return boolean 
     */
    public function isUserOnline($id = 0){
        return false;
    }
    
    /**
     *
     * @return int
     */
    public function getNumberOfUsersOnline(){
        $data = json_encode(array("command" => "get-number-of-sessions",
                "data" => array(
                )));
        return $this->connection->writeRead($data, 4096);
    }
    
    /**
     *
     * @return int
     */
    public function getNumberOfConnections(){
        $data = json_encode(array("command" => "get-number-of-clients",
                "data" => array(
                )));
        return $this->connection->writeRead($data, 4096);
    }
}
