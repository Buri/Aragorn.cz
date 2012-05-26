<?php

/**
 * Description of BanAuthenticationException
 *
 * @author Buri <buri.buster@gmail.com>
 */
class BanAuthenticationException extends \Nette\Security\AuthenticationException{
    private $bdata;
    public function  __construct($message, $code, $ban, $previous = null) {
        $this->bdata = $ban;
        parent::__construct($message, $code, $previous);
    }

    public function getBan(){
        return $this->bdata;
    }
};

