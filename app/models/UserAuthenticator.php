<?php
/**
 * Description of UserAuthenticator
 *
 * @author Buri
 */

/*class UserIdentity extends NIdentity {
    public function __consturct($id, $roles = NULL, $data = NULL){
        $data["permissions"] = new Permissions();
        parent::_construct($id, $roles, $data);
    }
    public function resetPermissions(){
        $this->permissions->unload();
    }
}*/

class BanAuthenticationException extends NAuthenticationException{
    private $bdata;
    public function  __construct($message, $code, $ban, $previous = null) {
        $this->bdata = $ban;
        parent::__construct($message, $code, $previous);
    }

    public function getBan(){
        return $this->bdata;
    }
};

class UserAuthenticator extends NObject implements IAuthenticator{
    const BANNED = 6;
    public function authenticate(array $credentials)
    {
        $username = $credentials[self::USERNAME];
        $password = sha1($credentials[self::PASSWORD]);
    
        // přečteme záznam o uživateli z databáze
        $usrs = DB::users()->select("username,id,groupid")->where("username LIKE ?", $username);
        if (!$usrs->count()) { // uživatel nenalezen?
            throw new NAuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
        }
        $row = $usrs->fetch();
        $usr = $row->users_profiles()->select("password")->fetch();
        if ($usr["password"] !== $password) { // hesla se neshodují?
            throw new NAuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
        }
        $bans = DB::bans()->where('user = ? OR ip = ?', array($usr['id'], $_SERVER["REMOTE_ADDR"]))->where('expires >= ?', time())->order('expires DESC');

        foreach($bans as $ban){
            $x = DB::users('id = ?', $ban["author"])->select("username")->fetch();
            $y = DB::users('id = ?', $ban["user"])->select("username")->fetch();
            throw new BanAuthenticationException((string)$x["username"] . ";" . (string)$y["username"], self::BANNED, $ban);
        }

        //$group = DB::groups("id", $row["groupid"])->select("name")->fetch();
        $prefs = DB::users_preferences("id", $row["id"])->fetch();
        return new NIdentity($row["id"], $row["groupid"], array("username" => $row["username"], "preferences" => json_decode($prefs["preference"]))); // vrátíme identitu
    }
}
