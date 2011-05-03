<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Templates
 */



/**
 * Caching template helper.
 *
 * @author     David Grudl
 */
class NCachingHelper extends NObject
{
	/** @var array */
	private $frame;

	/** @var string */
	private $key;



	/**
	 * Starts the output cache. Returns CachingHelper object if buffering was started.
	 * @param  string
	 * @param  array of CachingHelper
	 * @param  array
	 * @return NCachingHelper
	 */
	public static function create($key, & $parents, $args = NULL)
	{
		if ($args) {
			if (array_key_exists('if', $args) && !$args['if']) {
				return $parents[] = new self;
			}
			$key = array_merge(array($key), array_intersect_key($args, range(0, count($args))));
		}
		if ($parents) {
			end($parents)->frame[NCache::ITEMS][] = $key;
		}

		$cache = self::getCache();
		if (isset($cache[$key])) {
			echo $cache[$key];
			return FALSE;

		} else {
			$obj = new self;
			$obj->key = $key;
			$obj->frame = array(
				NCache::TAGS => isset($args['tags']) ? $args['tags'] : NULL,
				NCache::EXPIRATION => isset($args['expire']) ? $args['expire'] : '+ 7 days',
			);
			ob_start();
			return $parents[] = $obj;
		}
	}



	/**
	 * Stops and saves the cache.
	 * @return void
	 */
	public function save()
	{
		if ($this->key !== NULL) {
			$this->getCache()->save($this->key, ob_get_flush(), $this->frame);
		}
		$this->key = $this->frame = NULL;
	}



	/**
	 * Adds the file dependency.
	 * @param  string
	 * @return void
	 */
	public function addFile($file)
	{
		$this->frame[NCache::FILES][] = $file;
	}



	/********************* backend ****************d*g**/



	/**
	 * @return NCache
	 */
	protected static function getCache()
	{
		return NEnvironment::getCache('Nette.Template.Cache');
	}

}