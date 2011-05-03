<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Caching
 */



/**
 * Memcached storage.
 *
 * @author     David Grudl
 */
class NMemcachedStorage extends NObject implements ICacheStorage
{
	/** @internal cache structure */
	const META_CALLBACKS = 'callbacks',
		META_DATA = 'data',
		META_DELTA = 'delta';

	/** @var Memcache */
	private $memcache;

	/** @var string */
	private $prefix;

	/** @var ICacheJournal */
	private $journal;



	/**
	 * Checks if Memcached extension is available.
	 * @return bool
	 */
	public static function isAvailable()
	{
		return extension_loaded('memcache');
	}



	public function __construct($host = 'localhost', $port = 11211, $prefix = '', ICacheJournal $journal = NULL)
	{
		if (!self::isAvailable()) {
			throw new NotSupportedException("PHP extension 'memcache' is not loaded.");
		}

		$this->prefix = $prefix;
		$this->journal = $journal;
		$this->memcache = new Memcache;
		NDebug::tryError();
		$this->memcache->connect($host, $port);
		if (NDebug::catchError($e)) {
			throw new InvalidStateException('Memcache::connect(): ' . $e->getMessage(), 0, $e);
		}
	}



	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	public function read($key)
	{
		$key = $this->prefix . $key;
		$meta = $this->memcache->get($key);
		if (!$meta) return NULL;

		// meta structure:
		// array(
		//     data => stored data
		//     delta => relative (sliding) expiration
		//     callbacks => array of callbacks (function, args)
		// )

		// verify dependencies
		if (!empty($meta[self::META_CALLBACKS]) && !NCache::checkCallbacks($meta[self::META_CALLBACKS])) {
			$this->memcache->delete($key, 0);
			return NULL;
		}

		if (!empty($meta[self::META_DELTA])) {
			$this->memcache->replace($key, $meta, 0, $meta[self::META_DELTA] + time());
		}

		return $meta[self::META_DATA];
	}



	/**
	 * Writes item into the cache.
	 * @param  string key
	 * @param  mixed  data
	 * @param  array  dependencies
	 * @return void
	 */
	public function write($key, $data, array $dp)
	{
		if (isset($dp[NCache::ITEMS])) {
			throw new NotSupportedException('Dependent items are not supported by MemcachedStorage.');
		}

		$key = $this->prefix . $key;
		$meta = array(
			self::META_DATA => $data,
		);

		$expire = 0;
		if (isset($dp[NCache::EXPIRATION])) {
			$expire = (int) $dp[NCache::EXPIRATION];
			if (!empty($dp[NCache::SLIDING])) {
				$meta[self::META_DELTA] = $expire; // sliding time
			}
		}

		if (isset($dp[NCache::CALLBACKS])) {
			$meta[self::META_CALLBACKS] = $dp[NCache::CALLBACKS];
		}

		if (isset($dp[NCache::TAGS]) || isset($dp[NCache::PRIORITY])) {
			if (!$this->journal) {
				throw new InvalidStateException('CacheJournal has not been provided.');
			}
			$this->journal->write($key, $dp);
		}

		$this->memcache->set($key, $meta, 0, $expire);
	}



	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return void
	 */
	public function remove($key)
	{
		$this->memcache->delete($this->prefix . $key, 0);
	}



	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return void
	 */
	public function clean(array $conds)
	{
		if (!empty($conds[NCache::ALL])) {
			$this->memcache->flush();

		} elseif ($this->journal) {
			foreach ($this->journal->clean($conds) as $entry) {
				$this->memcache->delete($entry, 0);
			}
		}
	}

}
