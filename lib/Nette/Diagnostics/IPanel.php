<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette
 */



/**
 * Custom output for NDebug.
 *
 * @author     David Grudl
 */
interface IDebugPanel
{

	/**
	 * Renders HTML code for custom tab.
	 * @return void
	 */
	function getTab();

	/**
	 * Renders HTML code for custom panel.
	 * @return void
	 */
	function getPanel();

	/**
	 * Returns panel ID.
	 * @return string
	 */
	function getId();

}