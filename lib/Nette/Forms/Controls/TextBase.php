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
 * Implements the basic functionality common to text input controls.
 *
 * @author     David Grudl
 *
 * @property   string $emptyValue
 */
abstract class NTextBase extends NFormControl
{
	/** @var string */
	protected $emptyValue = '';

	/** @var array */
	protected $filters = array();



	/**
	 * Sets control's value.
	 * @param  string
	 * @return NTextBase  provides a fluent interface
	 */
	public function setValue($value)
	{
		$this->value = is_scalar($value) ? (string) $value : '';
		return $this;
	}



	/**
	 * Returns control's value.
	 * @return string
	 */
	public function getValue()
	{
		$value = $this->value;
		foreach ($this->filters as $filter) {
			$value = (string) $filter->invoke($value);
		}
		return $value === $this->translate($this->emptyValue) ? '' : $value;
	}



	/**
	 * Sets the special value which is treated as empty string.
	 * @param  string
	 * @return NTextBase  provides a fluent interface
	 */
	public function setEmptyValue($value)
	{
		$this->emptyValue = (string) $value;
		return $this;
	}



	/**
	 * Returns the special value which is treated as empty string.
	 * @return string
	 */
	final public function getEmptyValue()
	{
		return $this->emptyValue;
	}



	/**
	 * Appends input string filter callback.
	 * @param  callback
	 * @return NTextBase  provides a fluent interface
	 */
	public function addFilter($filter)
	{
		$this->filters[] = callback($filter);
		return $this;
	}



	public function getControl()
	{
		$control = parent::getControl();
		foreach ($this->getRules() as $rule) {
			if ($rule->type === NRule::VALIDATOR && !$rule->isNegative
				&& ($rule->operation === NForm::LENGTH || $rule->operation === NForm::MAX_LENGTH)
			) {
				$control->maxlength = is_array($rule->arg) ? $rule->arg[1] : $rule->arg;
			}
		}
		if ($this->emptyValue !== '') {
			$control->data('nette-empty-value', $this->translate($this->emptyValue));
		}
		return $control;
	}



	public function addRule($operation, $message = NULL, $arg = NULL)
	{
		if ($operation === NForm::FLOAT) {
			$this->addFilter(callback(__CLASS__, 'filterFloat'));
		}
		return parent::addRule($operation, $message, $arg);
	}



	/**
	 * Min-length validator: has control's value minimal length?
	 * @param  NTextBase
	 * @param  int  length
	 * @return bool
	 */
	public static function validateMinLength(NTextBase $control, $length)
	{
		return NString::length($control->getValue()) >= $length;
	}



	/**
	 * Max-length validator: is control's value length in limit?
	 * @param  NTextBase
	 * @param  int  length
	 * @return bool
	 */
	public static function validateMaxLength(NTextBase $control, $length)
	{
		return NString::length($control->getValue()) <= $length;
	}



	/**
	 * Length validator: is control's value length in range?
	 * @param  NTextBase
	 * @param  array  min and max length pair
	 * @return bool
	 */
	public static function validateLength(NTextBase $control, $range)
	{
		if (!is_array($range)) {
			$range = array($range, $range);
		}
		$len = NString::length($control->getValue());
		return ($range[0] === NULL || $len >= $range[0]) && ($range[1] === NULL || $len <= $range[1]);
	}



	/**
	 * Email validator: is control's value valid email address?
	 * @param  NTextBase
	 * @return bool
	 */
	public static function validateEmail(NTextBase $control)
	{
		$atom = "[-a-z0-9!#$%&'*+/=?^_`{|}~]"; // RFC 5322 unquoted characters in local-part
		$localPart = "(?:\"(?:[ !\\x23-\\x5B\\x5D-\\x7E]*|\\\\[ -~])+\"|$atom+(?:\\.$atom+)*)"; // quoted or unquoted
		$chars = "a-z0-9\x80-\xFF"; // superset of IDN
		$domain = "[$chars](?:[-$chars]{0,61}[$chars])"; // RFC 1034 one domain component
		return (bool) NString::match($control->getValue(), "(^$localPart@(?:$domain?\\.)+[-$chars]{2,19}\\z)i");
	}



	/**
	 * URL validator: is control's value valid URL?
	 * @param  NTextBase
	 * @return bool
	 */
	public static function validateUrl(NTextBase $control)
	{
		$chars = "a-z0-9\x80-\xFF";
		return (bool) NString::match(
			$control->getValue(),
			"#^(?:https?://|)(?:[$chars](?:[-$chars]{0,61}[$chars])?\\.)+[-$chars]{2,19}(/\S*)?$#i"
		);
	}



	/** @deprecated */
	public static function validateRegexp(NTextBase $control, $regexp)
	{
		return (bool) NString::match($control->getValue(), $regexp);
	}



	/**
	 * Regular expression validator: matches control's value regular expression?
	 * @param  NTextBase
	 * @param  string
	 * @return bool
	 */
	public static function validatePattern(NTextBase $control, $pattern)
	{
		return (bool) NString::match($control->getValue(), "\x01^($pattern)$\x01u");
	}



	/**
	 * Integer validator: is a control's value decimal number?
	 * @param  NTextBase
	 * @return bool
	 */
	public static function validateInteger(NTextBase $control)
	{
		return (bool) NString::match($control->getValue(), '/^-?[0-9]+$/');
	}



	/**
	 * Float validator: is a control's value float number?
	 * @param  NTextBase
	 * @return bool
	 */
	public static function validateFloat(NTextBase $control)
	{
		return (bool) NString::match($control->getValue(), '/^-?[0-9]*[.,]?[0-9]+$/');
	}



	/**
	 * Rangle validator: is a control's value number in specified range?
	 * @param  NTextBase
	 * @param  array  min and max value pair
	 * @return bool
	 */
	public static function validateRange(NTextBase $control, $range)
	{
		return ($range[0] === NULL || $control->getValue() >= $range[0])
			&& ($range[1] === NULL || $control->getValue() <= $range[1]);
	}



	/**
	 * Float string cleanup.
	 * @param  string
	 * @return string
	 */
	public static function filterFloat($s)
	{
		return str_replace(array(' ', ','), array('', '.'), $s);
	}

}
