<?php

class Bank {
    public static function getCredit($id = null){
        if(is_null($id)) $id = \Nette\Environment::getUser()->getId();
        $row = DB::users_profiles('id', $id)->fetch();
        return $row['bank'];
    }
    
    public static function sendTo(){}
    
    private static function getPrice($name){
        require(WWW_DIR . '/../config/bank.php');
        return $data[$name];
    }
    
    public static function handleAjax($action, $params){
        switch($action){
            case 'askprice':
                $e = self::getPrice($params);
                if($e){
                    return "<name>".$e["name"]."</name><price>".$e["price"]."</price><credit>" . self::getCredit() . "</credit>";
                }else{
                    return "FAIL";
                }
                break;
            default:
                return 'Unknown';
        }
    }
    
    public static function hascash($action){
        if(\Nette\Environment::getUser()->isAllowed('bank', 'unlimited-cash')) return true;
        $p = self::getPrice($action);
        return $p["price"] <= self::getCredit();
    }
}
