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
 * Container for form controls.
 *
 * @author     David Grudl
 *
 * @property-read ArrayIterator $controls
 * @property-read NForm $form
 * @property-read bool $valid
 * @property   array $values
 */
class NFormContainer extends NComponentContainer implements ArrayAccess
{
	/** @var array of function(Form $sender); Occurs when the form is validated */
	public $onValidate;

	/** @var NFormGroup */
	protected $currentGroup;

	/** @var bool */
	protected $valid;



	/********************* data exchange ****************d*g**/



	/**
	 * Fill-in with default values.
	 * @param  array|Traversable  values used to fill the form
	 * @param  bool     erase other default values?
	 * @return NFormContainer  provides a fluent interface
	 */
	public function setDefaults($values, $erase = FALSE)
	{
		$form = $this->getForm(FALSE);
		if (!$form || !$form->isAnchored() || !$form->isSubmitted()) {
			$this->setValues($values, $erase);
		}
		return $this;
	}



	/**
	 * Fill-in with values.
	 * @param  array|Traversable  values used to fill the form
	 * @param  bool     erase other controls?
	 * @return NFormContainer  provides a fluent interface
	 */
	public function setValues($values, $erase = FALSE)
	{
		if ($values instanceof Traversable) {
			$values = iterator_to_array($values);

		} elseif (!is_array($values)) {
			throw new InvalidArgumentException("First parameter must be an array, " . gettype($values) ." given.");
		}

		foreach ($this->getComponents() as $name => $control) {
			if ($control instanceof IFormControl) {
				if (array_key_exists($name, $values)) {
					$control->setValue($values[$name]);

				} elseif ($erase) {
					$control->setValue(NULL);
				}

			} elseif ($control instanceof NFormContainer) {
				if (array_key_exists($name, $values)) {
					$control->setValues($values[$name], $erase);

				} elseif ($erase) {
					$control->setValues(array(), $erase);
				}
			}
		}
		return $this;
	}



	/**
	 * Returns the values submitted by the form.
	 * @return NArrayHash
	 */
	public function getValues()
	{
		$values = new NArrayHash;
		foreach ($this->getComponents() as $name => $control) {
			if ($control instanceof IFormControl && !$control->isDisabled() && !$control instanceof ISubmitterControl) {
				$values->$name = $control->getValue();

			} elseif ($control instanceof NFormContainer) {
				$values->$name = $control->getValues();
			}
		}
		return $values;
	}



	/********************* validation ****************d*g**/



	/**
	 * Is form valid?
	 * @return bool
	 */
	public function isValid()
	{
		if ($this->valid === NULL) {
			$this->validate();
		}
		return $this->valid;
	}



	/**
	 * Performs the server side validation.
	 * @return void
	 */
	public function validate()
	{
		$this->valid = TRUE;
		$this->onValidate($this);
		foreach ($this->getControls() as $control) {
			if (!$control->getRules()->validate()) {
				$this->valid = FALSE;
			}
		}
	}



	/********************* form building ****************d*g**/



	/**
	 * @param  NFormGroup
	 * @return NFormContainer  provides a fluent interface
	 */
	public function setCurrentGroup(NFormGroup $group = NULL)
	{
		$this->currentGroup = $group;
		return $this;
	}



	/**
	 * Returns current group.
	 * @return NFormGroup
	 */
	public function getCurrentGroup()
	{
		return $this->currentGroup;
	}



	/**
	 * Adds the specified component to the IComponentContainer.
	 * @param  IComponent
	 * @param  string
	 * @param  string
	 * @return void
	 * @throws InvalidStateException
	 */
	public function addComponent(IComponent $component, $name, $insertBefore = NULL)
	{
		parent::addComponent($component, $name, $insertBefore);
		if ($this->currentGroup !== NULL && $component instanceof IFormControl) {
			$this->currentGroup->add($component);
		}
	}



	/**
	 * Iterates over all form controls.
	 * @return ArrayIterator
	 */
	public function getControls()
	{
		return $this->getComponents(TRUE, 'IFormControl');
	}



	/**
	 * Returns form.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return NForm
	 */
	public function getForm($need = TRUE)
	{
		return $this->lookup('NForm', $need);
	}



	/********************* control factories ****************d*g**/



	/**
	 * Adds single-line text input control to the form.
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 * @return NTextInput
	 */
	public function addText($name, $label = NULL, $cols = NULL, $maxLength = NULL)
	{
		return $this[$name] = new NTextInput($label, $cols, $maxLength);
	}



	/**
	 * Adds single-line text input control used for sensitive input such as passwords.
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 * @return NTextInput
	 */
	public function addPassword($name, $label = NULL, $cols = NULL, $maxLength = NULL)
	{
		$control = new NTextInput($label, $cols, $maxLength);
		$control->setType('password');
		return $this[$name] = $control;
	}



	/**
	 * Adds multi-line text input control to the form.
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  height of the control in text lines
	 * @return NTextArea
	 */
	public function addTextArea($name, $label = NULL, $cols = 40, $rows = 10)
	{
		return $this[$name] = new NTextArea($label, $cols, $rows);
	}



	/**
	 * Adds control that allows the user to upload files.
	 * @param  string  control name
	 * @param  string  label
	 * @return NFileUpload
	 */
	public function addFile($name, $label = NULL)
	{
		return $this[$name] = new NFileUpload($label);
	}



	/**
	 * Adds hidden form control used to store a non-displayed value.
	 * @param  string  control name
	 * @param  mixed   default value
	 * @return NHiddenField
	 */
	public function addHidden($name, $default = NULL)
	{
		$control = new NHiddenField;
		$control->setDefaultValue($default);
		return $this[$name] = $control;
	}



	/**
	 * Adds check box control to the form.
	 * @param  string  control name
	 * @param  string  caption
	 * @return NCheckbox
	 */
	public function addCheckbox($name, $caption = NULL)
	{
		return $this[$name] = new NCheckbox($caption);
	}



	/**
	 * Adds set of radio button controls to the form.
	 * @param  string  control name
	 * @param  string  label
	 * @param  array   options from which to choose
	 * @return NRadioList
	 */
	public function addRadioList($name, $label = NULL, array $items = NULL)
	{
		return $this[$name] = new NRadioList($label, $items);
	}



	/**
	 * Adds select box control that allows single item selection.
	 * @param  string  control name
	 * @param  string  label
	 * @param  array   items from which to choose
	 * @param  int     number of rows that should be visible
	 * @return NSelectBox
	 */
	public function addSelect($name, $label = NULL, array $items = NULL, $size = NULL)
	{
		return $this[$name] = new NSelectBox($label, $items, $size);
	}



	/**
	 * Adds select box control that allows multiple item selection.
	 * @param  string  control name
	 * @param  string  label
	 * @param  array   options from which to choose
	 * @param  int     number of rows that should be visible
	 * @return NMultiSelectBox
	 */
	public function addMultiSelect($name, $label = NULL, array $items = NULL, $size = NULL)
	{
		return $this[$name] = new NMultiSelectBox($label, $items, $size);
	}



	/**
	 * Adds button used to submit form.
	 * @param  string  control name
	 * @param  string  caption
	 * @return NSubmitButton
	 */
	public function addSubmit($name, $caption = NULL)
	{
		return $this[$name] = new NSubmitButton($caption);
	}



	/**
	 * Adds push buttons with no default behavior.
	 * @param  string  control name
	 * @param  string  caption
	 * @return NButton
	 */
	public function addButton($name, $caption)
	{
		return $this[$name] = new NButton($caption);
	}



	/**
	 * Adds graphical button used to submit form.
	 * @param  string  control name
	 * @param  string  URI of the image
	 * @param  string  alternate text for the image
	 * @return NImageButton
	 */
	public function addImage($name, $src = NULL, $alt = NULL)
	{
		return $this[$name] = new NImageButton($src, $alt);
	}



	/**
	 * Adds naming container to the form.
	 * @param  string  name
	 * @return NFormContainer
	 */
	public function addContainer($name)
	{
		$control = new NFormContainer;
		$control->currentGroup = $this->currentGroup;
		return $this[$name] = $control;
	}



	/********************* interface ArrayAccess ****************d*g**/



	/**
	 * Adds the component to the container.
	 * @param  string  component name
	 * @param  IComponent
	 * @return void
	 */
	final public function offsetSet($name, $component)
	{
		$this->addComponent($component, $name);
	}



	/**
	 * Returns component specified by name. Throws exception if component doesn't exist.
	 * @param  string  component name
	 * @return IComponent
	 * @throws InvalidArgumentException
	 */
	final public function offsetGet($name)
	{
		return $this->getComponent($name, TRUE);
	}



	/**
	 * Does component specified by name exists?
	 * @param  string  component name
	 * @return bool
	 */
	final public function offsetExists($name)
	{
		return $this->getComponent($name, FALSE) !== NULL;
	}



	/**
	 * Removes component from the container.
	 * @param  string  component name
	 * @return void
	 */
	final public function offsetUnset($name)
	{
		$component = $this->getComponent($name, FALSE);
		if ($component !== NULL) {
			$this->removeComponent($component);
		}
	}



	/**
	 * Prevents cloning.
	 */
	final public function __clone()
	{
		throw new NotImplementedException('Form cloning is not supported yet.');
	}

}
