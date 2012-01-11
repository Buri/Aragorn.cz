<?php
/**
 * Description of UserAuthenticator
 *
 * @author Buri
 */

use \Nette\Environment;
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

abstract class BaseAuthenticator extends \Nette\Object implements \Nette\Security\IAuthenticator{
    const BANNED = 6;
    var $id, $gid, $name;
    protected function tryBan(){
        $bans = DB::bans()->where('user = ? OR ip = ?', array($this->id, $_SERVER["REMOTE_ADDR"]))->where('expires >= ?', time())->order('expires DESC');
        foreach($bans as $ban){
            $x = DB::users('id = ?', $ban["author"])->select("username")->fetch();
            $y = DB::users('id = ?', $ban["user"])->select("username")->fetch();
            throw new BanAuthenticationException((string)$x["username"] . ";" . (string)$y["username"], self::BANNED, $ban);
        }        
    }
    protected function newId(){
        $prefs = DB::users_preferences("id", $this->id)->fetch();
        // vrátíme identitu
        return new \Nette\Security\Identity($this->id, array($this->gid), array("username" => $this->name, "preferences" => json_decode($prefs["preference"]))); 
    }
}
class UserAuthenticator extends \BaseAuthenticator{
    public function authenticate(array $credentials)
    {
        #dump($credentials);
        $username = $credentials[self::USERNAME];
        $password = sha1($credentials[self::PASSWORD]);
    
        // přečteme záznam o uživateli z databáze
        $usrs = DB::users()->select("username,id,groupid")->where("username LIKE ?", $username);
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
/*class GoogleAuthenticator extends BaseAuthenticator{
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

class FacebookAuthenticator extends BaseAuthenticator{
    public function authenticate(array $credentials) {
        $this->tryBan();
        return $this->newId();
    }
}
*/