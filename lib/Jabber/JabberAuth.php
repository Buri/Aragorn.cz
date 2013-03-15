<?php

class JabberAuth {
    private $db,$io;
    public function __construct(NotORM $db, stdio $io)
    {
        $this->db = $db;
        $this->io = $io;
    }

    public function go()
    {
        $realm = (gethostname() == 'vs7170') ? 'aragorn.cz' : 'test.aragorn.cz';
        $data = $this->io->read();
        file_put_contents('/tmp/jabber.txt', $data);
        $parts = explode(':', $data);
        switch($parts[0])
        {
            case 'auth':
                $user = $parts[1];
                $server = $parts[2];
                $pass = $parts[3];
                if($server != $realm)
                {
                    $this->io->write(false);
                    break;
                }
                $passwd = $this->db->users_profiles('urlfragment', $user)->fetch();
                $this->io->write(sha1($pass) == $passwd['password']);
                break;
            case 'isuser':
                $server = $parts[2];
                $user = $parts[1];
                if($server != $realm)
                {
                    $this->io->write(false);
                    break;
                }
                $this->io->write(count($this->db->users_profiles('urlfragment', $user)) == 1);
                break;
            default:
                $this->io->write(false);
        }
    }
}

