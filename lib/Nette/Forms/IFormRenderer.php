<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Forms
 */



/**
 * Defines method that must implement form rendered.
 *
 * @author     David Grudl
 */
interface IFormRenderer
{

	/**
	 * Provides complete form rendering.
	 * @param  NForm
	 * @return string
	 */
	function render(NForm $form);

}
