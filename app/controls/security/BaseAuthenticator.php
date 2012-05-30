<?php
/**
 * Description of UserAuthenticator
 *
 * @author Buri
 */

abstract class BaseAuthenticator extends \Nette\Object implements \Nette\Security\IAuthenticator{
    
    const BANNED = 6;

    /**
     *
     * @var int UserID
     */
    protected $id;
    
    /**
     *
     * @var array GroupID
     */
    protected $gid;

    /**
     *
     * @var string Username
     */
    protected $name;

    /**
     *
     * @var NotORM Database instance
     */
    protected $db;

    /**
     *
     * @var Preferences
     */
    protected $preferences;

    /**
     *
     * @param NotORM |null$db
     */
    function __construct(Preferences $prefs, $db = null){
        if($db === null)
            $this->db = DB::getInstance();
        else
            $this->db = $db;
        $this->preferences = $prefs;
    }

    /**
     * Check if user is banished from server
     *
     * @throws BanAuthenticationException
     */
    protected function tryBan(){
        $bans = $this->db->bans()->where('user = ? OR ip = ?', array($this->id, $_SERVER["REMOTE_ADDR"]))->where('expires >= ?', time())->order('expires DESC');
        foreach($bans as $ban){
            $x = $this->db->users('id = ?', $ban["author"])->select("username")->fetch();
            $y = $this->db->users('id = ?', $ban["user"])->select("username")->fetch();
            throw new BanAuthenticationException((string)$x["username"] . ";" . (string)$y["username"], self::BANNED, $ban);
        }        
    }

    /**
     * Generate new Identity for user
     *
     * @return \Nette\Security\Identity
     */
    protected function newId(){
        $p = $this->preferences->get($this->id);
        return new \Nette\Security\Identity($this->id,
                array($this->gid),
                $p);
    }
}
