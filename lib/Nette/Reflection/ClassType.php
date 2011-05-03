<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Reflection
 */



/**
 * Reports information about a class.
 *
 * @author     David Grudl
 */
class NClassReflection extends ReflectionClass
{

	/** @var array (method => array(type => callback)) */
	private static $extMethods;



	/**
	 * @param  string|object
	 * @return NClassReflection
	 */
	public static function from($class)
	{
		return new self($class);
	}



	public function __toString()
	{
		return 'Class ' . $this->getName();
	}



	/**
	 * @return bool
	 */
	public function hasEventProperty($name)
	{
		if (preg_match('#^on[A-Z]#', $name) && $this->hasProperty($name)) {
			$rp = $this->getProperty($name);
			return $rp->isPublic() && !$rp->isStatic();
		}
		return FALSE;
	}



	/**
	 * Adds a method to class.
	 * @param  string  method name
	 * @param  mixed   callback or closure
	 * @return NClassReflection  provides a fluent interface
	 */
	public function setExtensionMethod($name, $callback)
	{
		$l = & self::$extMethods[strtolower($name)];
		$l[strtolower($this->getName())] = callback($callback);
		$l[''] = NULL;
		return $this;
	}



	/**
	 * Returns extension method.
	 * @param  string  method name
	 * @return mixed
	 */
	public function getExtensionMethod($name)
	{
		if (self::$extMethods === NULL || $name === NULL) { // for backwards compatibility
			$list = get_defined_functions(); // names are lowercase!
			foreach ($list['user'] as $fce) {
				$pair = explode('_prototype_', $fce);
				if (count($pair) === 2) {
					self::$extMethods[$pair[1]][$pair[0]] = callback($fce);
					self::$extMethods[$pair[1]][''] = NULL;
				}
			}
			if ($name === NULL) return NULL;
		}

		$class = strtolower($this->getName());
		$l = & self::$extMethods[strtolower($name)];

		if (empty($l)) {
			return FALSE;

		} elseif (isset($l[''][$class])) { // cached value
			return $l[''][$class];
		}

		$cl = $class;
		do {
			if (isset($l[$cl])) {
				return $l[''][$class] = $l[$cl];
			}
		} while (($cl = strtolower(get_parent_class($cl))) !== '');

		foreach (class_implements($class) as $cl) {
			$cl = strtolower($cl);
			if (isset($l[$cl])) {
				return $l[''][$class] = $l[$cl];
			}
		}
		return $l[''][$class] = FALSE;
	}



	/********************* Reflection layer ****************d*g**/



	/**
	 * @return NMethodReflection
	 */
	public function getConstructor()
	{
		return ($ref = parent::getConstructor()) ? NMethodReflection::from($this->getName(), $ref->getName()) : NULL;
	}



	/**
	 * @return NExtensionReflection
	 */
	public function getExtension()
	{
		return ($name = $this->getExtensionName()) ? new NExtensionReflection($name) : NULL;
	}



	public function getInterfaces()
	{
		$res = array();
		foreach (parent::getInterfaceNames() as $val) {
			$res[$val] = new self($val);
		}
		return $res;
	}



	/**
	 * @return NMethodReflection
	 */
	public function getMethod($name)
	{
		return new NMethodReflection($this->getName(), $name);
	}



	public function getMethods($filter = -1)
	{
		foreach ($res = parent::getMethods($filter) as $key => $val) {
			$res[$key] = new NMethodReflection($this->getName(), $val->getName());
		}
		return $res;
	}



	/**
	 * @return NClassReflection
	 */
	public function getParentClass()
	{
		return ($ref = parent::getParentClass()) ? new self($ref->getName()) : NULL;
	}



	public function getProperties($filter = -1)
	{
		foreach ($res = parent::getProperties($filter) as $key => $val) {
			$res[$key] = new NPropertyReflection($this->getName(), $val->getName());
		}
		return $res;
	}



	/**
	 * @return NPropertyReflection
	 */
	public function getProperty($name)
	{
		return new NPropertyReflection($this->getName(), $name);
	}



	/********************* NAnnotations support ****************d*g**/



	/**
	 * Has class specified annotation?
	 * @param  string
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		$res = NAnnotationsParser::getAll($this);
		return !empty($res[$name]);
	}



	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return IAnnotation
	 */
	public function getAnnotation($name)
	{
		$res = NAnnotationsParser::getAll($this);
		return isset($res[$name]) ? end($res[$name]) : NULL;
	}



	/**
	 * Returns all annotations.
	 * @return array
	 */
	public function getAnnotations()
	{
		return NAnnotationsParser::getAll($this);
	}



	/********************* NObject behaviour ****************d*g**/



	/**
	 * @return NClassReflection
	 */
	public function getReflection()
	{
		return new NClassReflection($this);
	}



	public function __call($name, $args)
	{
		return NObjectMixin::call($this, $name, $args);
	}



	public function &__get($name)
	{
		return NObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return NObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return NObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		NObjectMixin::remove($this, $name);
	}

}
