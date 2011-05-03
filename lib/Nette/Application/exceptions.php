<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Application
 */



/**
 * The exception that is thrown when user attempts to terminate the current presenter or application.
 * This is special "silent exception" with no error message or code.
 */
class NAbortException extends Exception
{
}



/**
 * Application fatal error.
 */
class NApplicationException extends Exception
{
	public function __construct($message = '', $code = 0, Exception $previous = NULL)
	{
		if (PHP_VERSION_ID < 50300) {
			$this->previous = $previous;
			parent::__construct($message, $code);
		} else {
			parent::__construct($message, $code, $previous);
		}
	}
}



/**
 * The exception that is thrown when a presenter cannot be loaded.
 */
class NInvalidPresenterException extends Exception
{
}



/**
 * Bad HTTP / presenter request exception.
 */
class NBadRequestException extends Exception
{
	/** @var int */
	protected $defaultCode = 404;


	public function __construct($message = '', $code = 0, Exception $previous = NULL)
	{
		if ($code < 200 || $code > 504) {
			$code = $this->defaultCode;
		}

		if (PHP_VERSION_ID < 50300) {
			$this->previous = $previous;
			parent::__construct($message, $code);
		} else {
			parent::__construct($message, $code, $previous);
		}
	}

}



/**
 * Forbidden request exception - access denied.
 */
class NForbiddenRequestException extends NBadRequestException
{
	/** @var int */
	protected $defaultCode = 403;

}
