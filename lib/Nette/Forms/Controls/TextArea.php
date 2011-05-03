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
 * Multiline text input control.
 *
 * @author     David Grudl
 */
class NTextArea extends NTextBase
{

	/**
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  height of the control in text lines
	 */
	public function __construct($label = NULL, $cols = NULL, $rows = NULL)
	{
		parent::__construct($label);
		$this->control->setName('textarea');
		$this->control->cols = $cols;
		$this->control->rows = $rows;
		$this->value = '';
	}



	/**
	 * Generates control's HTML element.
	 * @return NHtml
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->setText($this->getValue() === '' ? $this->translate($this->emptyValue) : $this->value);
		return $control;
	}

}
