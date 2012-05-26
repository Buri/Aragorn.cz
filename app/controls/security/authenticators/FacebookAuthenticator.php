<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of FacebookAuthenticator
 *
 * @author Buri <buri.buster@gmail.com>
 */
class FacebookAuthenticator extends BaseAuthenticator{
    public function authenticate(array $credentials) {
        $this->tryBan();
        return $this->newId();
    }
}

