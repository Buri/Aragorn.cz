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
 * Responsible for creating a new instance of given presenter.
 *
 * @author Jan Tichý <tichy@medio.cz>
 */
interface IPresenterFactory
{

	/**
	 * @param  string  presenter name
	 * @return string  class name
	 * @throws NInvalidPresenterException
	 */
	function getPresenterClass(& $name);

	/**
	 * Create new presenter instance.
	 * @param  string  presenter name
	 * @return IPresenter
	 */
	function createPresenter($name);

}
