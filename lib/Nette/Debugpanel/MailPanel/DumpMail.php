<?php

/**
 * Dumps mail into session
 */

namespace Schmutzka\Diagnostics;

use Nette\Mail\IMailer,
	Nette\Mail\Message,
	Nette\ArrayList;

class DumpMail implements IMailer {

	/** @var \Session */
	private $session;

	/** @var int */
	public $expireTime;


	public function __construct($session = NULL, $expireTime = 5)
	{
		$this->session = $session->getSection("dumpMail");
		$this->expireTime = $expireTime;
	}


	/**
	 * Let's send a mail to session
	 */
	function send(Message $mail) {
		$i = substr(uniqid(), 7,6);
		$this->session->{$i} = $mail;
		$this->session->setExpiration("+$this->expireTime seconds", $i); // hold for x secs
	}

}
