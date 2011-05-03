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
 * List of validation & condition rules.
 *
 * @author     David Grudl
 */
final class NRules extends NObject implements IteratorAggregate
{
	/** @internal */
	const VALIDATE_PREFIX = 'validate';

	/** @var array */
	public static $defaultMessages = array(
		NForm::PROTECTION => 'Security token did not match. Possible CSRF attack.',
		NForm::EQUAL => 'Please enter %s.',
		NForm::FILLED => 'Please complete mandatory field.',
		NForm::MIN_LENGTH => 'Please enter a value of at least %d characters.',
		NForm::MAX_LENGTH => 'Please enter a value no longer than %d characters.',
		NForm::LENGTH => 'Please enter a value between %d and %d characters long.',
		NForm::EMAIL => 'Please enter a valid email address.',
		NForm::URL => 'Please enter a valid URL.',
		NForm::INTEGER => 'Please enter a numeric value.',
		NForm::FLOAT => 'Please enter a numeric value.',
		NForm::RANGE => 'Please enter a value between %d and %d.',
		NForm::MAX_FILE_SIZE => 'The size of the uploaded file can be up to %d bytes.',
		NForm::IMAGE => 'The uploaded file must be image in format JPEG, GIF or PNG.',
	);

	/** @var array of NRule */
	private $rules = array();

	/** @var NRules */
	private $parent;

	/** @var array */
	private $toggles = array();

	/** @var IFormControl */
	private $control;



	public function __construct(IFormControl $control)
	{
		$this->control = $control;
	}



	/**
	 * Adds a validation rule for the current control.
	 * @param  mixed      rule type
	 * @param  string     message to display for invalid data
	 * @param  mixed      optional rule arguments
	 * @return NRules      provides a fluent interface
	 */
	public function addRule($operation, $message = NULL, $arg = NULL)
	{
		$rule = new NRule;
		$rule->control = $this->control;
		$rule->operation = $operation;
		$this->adjustOperation($rule);
		$rule->arg = $arg;
		$rule->type = NRule::VALIDATOR;
		if ($message === NULL && is_string($rule->operation) && isset(self::$defaultMessages[$rule->operation])) {
			$rule->message = self::$defaultMessages[$rule->operation];
		} else {
			$rule->message = $message;
		}
		$this->rules[] = $rule;
		return $this;
	}



	/**
	 * Adds a validation condition a returns new branch.
	 * @param  mixed      condition type
	 * @param  mixed      optional condition arguments
	 * @return NRules      new branch
	 */
	public function addCondition($operation, $arg = NULL)
	{
		return $this->addConditionOn($this->control, $operation, $arg);
	}



	/**
	 * Adds a validation condition on specified control a returns new branch.
	 * @param  IFormControl form control
	 * @param  mixed      condition type
	 * @param  mixed      optional condition arguments
	 * @return NRules      new branch
	 */
	public function addConditionOn(IFormControl $control, $operation, $arg = NULL)
	{
		$rule = new NRule;
		$rule->control = $control;
		$rule->operation = $operation;
		$this->adjustOperation($rule);
		$rule->arg = $arg;
		$rule->type = NRule::CONDITION;
		$rule->subRules = new self($this->control);
		$rule->subRules->parent = $this;

		$this->rules[] = $rule;
		return $rule->subRules;
	}



	/**
	 * Adds a else statement.
	 * @return NRules      else branch
	 */
	public function elseCondition()
	{
		$rule = clone end($this->parent->rules);
		$rule->isNegative = !$rule->isNegative;
		$rule->subRules = new self($this->parent->control);
		$rule->subRules->parent = $this->parent;
		$this->parent->rules[] = $rule;
		return $rule->subRules;
	}



	/**
	 * Ends current validation condition.
	 * @return NRules      parent branch
	 */
	public function endCondition()
	{
		return $this->parent;
	}



	/**
	 * Toggles HTML elememnt visibility.
	 * @param  string     element id
	 * @param  bool       hide element?
	 * @return NRules      provides a fluent interface
	 */
	public function toggle($id, $hide = TRUE)
	{
		$this->toggles[$id] = $hide;
		return $this;
	}



	/**
	 * Validates against ruleset.
	 * @param  bool    stop before first error?
	 * @return bool    is valid?
	 */
	public function validate($onlyCheck = FALSE)
	{
		foreach ($this->rules as $rule) {
			if ($rule->control->isDisabled()) continue;

			$success = ($rule->isNegative xor $this->getCallback($rule)->invoke($rule->control, $rule->arg));

			if ($rule->type === NRule::CONDITION && $success) {
				if (!$rule->subRules->validate($onlyCheck)) {
					return FALSE;
				}

			} elseif ($rule->type === NRule::VALIDATOR && !$success) {
				if (!$onlyCheck) {
					$rule->control->addError(self::formatMessage($rule, TRUE));
				}
				return FALSE;
			}
		}
		return TRUE;
	}



	/**
	 * Iterates over ruleset.
	 * @return ArrayIterator
	 */
	final public function getIterator()
	{
		return new ArrayIterator($this->rules);
	}



	/**
	 * @return array
	 */
	final public function getToggles()
	{
		return $this->toggles;
	}



	/**
	 * Process 'operation' string.
	 * @param  NRule
	 * @return void
	 */
	private function adjustOperation($rule)
	{
		if (is_string($rule->operation) && ord($rule->operation[0]) > 127) {
			$rule->isNegative = TRUE;
			$rule->operation = ~$rule->operation;
		}

		if (!$this->getCallback($rule)->isCallable()) {
			$operation = is_scalar($rule->operation) ? " '$rule->operation'" : '';
			throw new InvalidArgumentException("Unknown operation$operation for control '{$rule->control->name}'.");
		}
	}



	private function getCallback($rule)
	{
		$op = $rule->operation;
		if (is_string($op) && strncmp($op, ':', 1) === 0) {
			return callback(get_class($rule->control), self::VALIDATE_PREFIX . ltrim($op, ':'));
		} else {
			return callback($op);
		}
	}



	public static function formatMessage($rule, $withValue)
	{
		$message = $rule->message;
		if (!isset($message)) { // report missing message by notice
			$message = self::$defaultMessages[$rule->operation];
		}
		if ($translator = $rule->control->getForm()->getTranslator()) {
			$message = $translator->translate($message, is_int($rule->arg) ? $rule->arg : NULL);
		}
		$message = vsprintf(preg_replace('#%(name|label|value)#', '%$0', $message), (array) $rule->arg);
		$message = str_replace('%name', $rule->control->getName(), $message);
		$message = str_replace('%label', $rule->control->translate($rule->control->caption), $message);
		if ($withValue && strpos($message, '%value') !== FALSE) {
			$message = str_replace('%value', $rule->control->getValue(), $message);
		}
		return $message;
	}

}
