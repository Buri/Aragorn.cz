<?php

class stdio {
    
    private $stdout;
    private $stdin;
    public function __construct()
    {
        $this->stdin = fopen('php://stdin', 'r');
        $this->stdout = fopen('php://stdout', 'r');
    }
    public function __destruct()
    {
        fclose($this->stdin);
        fclose($this->stdout);
    }

    public function read()
    {
        $l         = @fgets($this->stdin, 3); // We take the length of string
        $length = @unpack("n", $l); // ejabberd give us something to play with ...
        $len      = $length["1"]; // and we now know how long to read.
        if($len > 0) { // if not, we'll fill logfile ... and disk full is just funny once
            $data   = @fgets($this->stdin, $len+1);
            return $data;
        }
    }

    public function write($message)
    {
        @fwrite($this->stdout, pack('nn', 2, $message ? 1 : 0) ); // We reply ...
    }
}

