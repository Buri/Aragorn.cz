<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Database\Drivers
 */



/**
 * Supplemental SQLite2 database driver.
 *
 * @author     David Grudl
 */
class NPdoSqlite2Driver extends NPdoSqliteDriver
{

	/**
	 * Encodes string for use in a LIKE statement.
	 */
	public function formatLike($value, $pos)
	{
		throw new NotSupportedException;
	}



	/**
	 * Normalizes result row.
	 */
	public function normalizeRow($row, $statement)
	{
		if (!is_object($row)) {
			$iterator = $row;
		} elseif ($row instanceof Traversable) {
			$iterator = iterator_to_array($row);
		} else {
			$iterator = (array) $row;
		}
		foreach ($iterator as $key => $value) {
			unset($row[$key]);
			if ($key[0] === '[' || $key[0] === '"') {
				$key = substr($key, 1, -1);
			}
			$row[$key] = $value;
		}
		return $row;
	}

}
