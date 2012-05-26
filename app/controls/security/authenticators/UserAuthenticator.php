<?php

/*
 *  This project source is hereby granted under Mozilla Public License
 *  http://www.mozilla.org/MPL/2.0/
 */

/**
 * Description of UserAuthenticator
 *
 * @author Buri <buri.buster@gmail.com>
 */

class UserAuthenticator extends \BaseAuthenticator{

    /**
     *
     * @param array $credentials
     * @return  \Nette\Security\Identity
     * @throws \Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        /** @var string */
        $username = $credentials[self::USERNAME];
        /** @var string  Sha1 encoded password*/
        $password = sha1($credentials[self::PASSWORD]);

        // přečteme záznam o uživateli z databáze
        $usrs = $this->db->users()->select("username,id,groupid")->where("username LIKE ?", $username);
        if (!$usrs->count()) { // uživatel nenalezen?
            throw new \Nette\Security\AuthenticationException("User $username not found.", self::IDENTITY_NOT_FOUND);
        }
        $row = $usrs->fetch();
        $usr = $row->users_profiles()->select("password")->fetch();
        if ($usr["password"] !== $password) { // hesla se neshodují?
            throw new \Nette\Security\AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
        }
        $this->gid = $row["groupid"];
        $this->id = $usr['id'];
        $this->name = $row["username"];

        $this->tryBan();
        return $this->newId();
    }
}
?>
