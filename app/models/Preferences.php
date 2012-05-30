<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of Preferences
 *
 * @author Buri <buri.buster@gmail.com>
 */
class Preferences extends \Nette\Object{
    protected $db;
    public function __construct(NotORM $db){
        $this->db = $db;
    }

    public function get($uid = -1){
        //if($uid === -1) $uid = $this->getUser()->getId();
        $p = $this->db->users_preferences('id', $uid)->fetch();
        $u =$this->db->users('id', $uid)->fetch();
        return  array(
                    "username" => $u['username'],
                    "preferences" => array(
                        "chat" => array(
                            "color" => $p['chatcolor']
                    )));
    }
}
