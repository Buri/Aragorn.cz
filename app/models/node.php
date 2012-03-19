<?php

class Node {
    static function userlogin($sid = 0){
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
        return usock::writeReadClose($data, 4096);
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
        return usock::writeReadClose($data, 4096);
    }
    
    static function isUserOnline($id){
        return false;
    }
    
    static function getNumberOfUsersOnline(){
        $data = json_encode(array("command" => "get-number-of-sessions",
                "data" => array(
                )));
        return usock::writeRead($data, 4096);
    }
    
    static function getNumberOfConnections(){
        $data = json_encode(array("command" => "get-number-of-clients",
                "data" => array(
                )));
        return usock::writeRead($data, 4096);
    }
}
