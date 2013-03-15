<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Feed
 *
 * @author Buri <buri.buster@gmail.com>
 */
class Feed extends \Nette\Object {

    /** @var int $uid */
    private $uid;

    /**
     *
     * @var NotORM
     */
    private $db;

    /**
     * @param NotORM $db
     * @param int $uid
     */
    public function __construct(NotORM $db, $uid)
    {
        parent::_construct();
        $this->uid = $uid;
        $this->db = $db;
    }

    /**
     *
     * @param string $title
     * @param string $message
     * @param string $url
     * @return boolean
     */
    public function Add($title,$message,$url)
    {
        return true;
    }

    /**
     *
     * @return array
     */
    public function GetAll()
    {
        return array();
    }
}
