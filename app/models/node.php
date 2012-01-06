<?php

class Node {
    static function userlogin(){
        $user = Nette\Environment::getUser();
        $p = new Permissions();
        $data = json_encode(array("command" => "user-login",
                "data" => array("PHPSESSID" => session_id(),
                    #"nodeSession" => $_COOKIE["sid"],
                    "roles" => $user->getIdentity()->getRoles(),
                    "id" => $user->getIdentity()->getId(),
                    "username" => $user->getIdentity()->username,
                    "preferences" => $user->getIdentity()->preferences,
                    "permissions" => $p->getRaw()
                )));
        return usock::writeReadClose($data, 4096);
    }
    
    static function changeStatus($status){
        $user = Nette\Environment::getUser();
        $data = json_encode(array("command" => "user-status-set",
                "data" => array("PHPSESSID" => session_id(),
                    #"nodeSession" => $_COOKIE["sid"],
                    "id" => $user->getIdentity()->getId(),
                    "username" => $user->getIdentity()->username,
                    "status" => $status
                )));
        return usock::writeReadClose($data, 4096);
    }
}
