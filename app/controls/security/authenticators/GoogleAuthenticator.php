<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of GoogleAuthenticator
 *
 * @author Buri <buri.buster@gmail.com>
 */
class GoogleAuthenticator extends BaseAuthenticator{
    public function authenticate(array $c) {
        try {
            $openid = new LightOpenID($_SERVER['HTTP_HOST']); //$_SERVER['SERVER_NAME']);
            $openid->returnUrl = $openid->realm . '/dashboard/login/';
            $openid->validate();
            Nette\Diagnostics\Debugger::dump($openid);
            if(!$openid->mode) {
                if($c[0]){
                    $openid->identity = 'https://www.google.com/accounts/o8/id';
                    header('Location: ' . $openid->authUrl());
                }
            } elseif($openid->mode == 'cancel') {
                echo 'User has canceled authentication!';
            } else {
                echo 'User ' . ($openid->validate() ? $openid->identity . ' has ' : 'has not ') . 'logged in.';
            }
        } catch(ErrorException $e) {
            echo $e->getMessage();
            exit;
        }
        echo 'User ' . ($openid->validate() ? $openid->identity . ' has ' : 'has not ') . 'logged in.';
        exit;
        $this->id = 1;
        $this->tryBan();
        return $this->newId();
    }
}
