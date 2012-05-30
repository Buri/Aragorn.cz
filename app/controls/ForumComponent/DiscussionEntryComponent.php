<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of DiscussionEntry
 *
 * @author Buri <buri.buster@gmail.com>
 */
namespace Components{
    class DiscussionEntry extends \Nette\Application\UI\Control{
        /**
         *
         * @var NotORM
         */
        protected $db;

        /**
         *
         * @var Nette\Security\User
         */
        protected $user;

        /**
         *
         * @param \NotORM $db
         * @return \Components\DiscussionEntry
         */
        public function setupDB(\NotORM $db) {
            $this->db =$db;
            return $this;
        }

        /**
         *
         * @param \Nette\Security\User $user
         * @return \Components\DiscussionEntry
         */
        public function setupUser(\Nette\Security\User $user){
            $this->user = $user;
            return $this;
        }

        public function init(){
            return $this;
        }

        public function add(){

        }

        public function delete(){
            
        }

        public function render(){
            
        }
    }
}
