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
 * Single validation rule or condition represented as value object.
 *
 * @author     David Grudl
 */
final class NRule extends NObject
{
	/** type */
	const CONDITION = 1;

	/** type */
	const VALIDATOR = 2;

	/** type */
	const FILTER = 3;

	/** @var IFormControl */
	public $control;

	/** @var mixed */
	public $operation;

	/** @var mixed */
	public $arg;

	/** @var int (CONDITION, VALIDATOR, FILTER) */
	public $type;

	/** @var bool */
	public $isNegative = FALSE;

	/** @var string (only for VALIDATOR type) */
	public $message;

	/** @var NRules (only for CONDITION type)  */
	public $subRules;

}
