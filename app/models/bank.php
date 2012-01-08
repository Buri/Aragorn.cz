<?php

class Bank {
    public static function getCredit($id = null){
        if(is_null($id)) $id = \Nette\Environment::getUser()->getId();
        $row = DB::users_profiles('id', $id)->fetch();
        return $row['bank'];
    }
    
    public static function sendTo(){}
}
