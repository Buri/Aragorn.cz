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
 * Supplemental PostgreSQL database driver.
 *
 * @author     David Grudl
 */
class NPdoPgSqlDriver extends NObject implements ISupplementalDriver
{
	/** @var NConnection */
	private $connection;



	public function __construct(NConnection $connection, array $options)
	{
		$this->connection = $connection;
	}



	/********************* SQL ****************d*g**/



	/**
	 * Delimites identifier for use in a SQL statement.
	 */
	public function delimite($name)
	{
		// @see http://www.postgresql.org/docs/8.2/static/sql-syntax-lexical.html#SQL-SYNTAX-IDENTIFIERS
		return '"' . str_replace('"', '""', $name) . '"';
	}



	/**
	 * Formats date-time for use in a SQL statement.
	 */
	public function formatDateTime(DateTime $value)
	{
		return $value->format("'Y-m-d H:i:s'");
	}



	/**
	 * Encodes string for use in a LIKE statement.
	 */
	public function formatLike($value, $pos)
	{
		throw new NotImplementedException;
	}



	/**
	 * Injects LIMIT/OFFSET to the SQL query.
	 */
	public function applyLimit(&$sql, $limit, $offset)
	{
		if ($limit >= 0)
			$sql .= ' LIMIT ' . (int) $limit;

		if ($offset > 0)
			$sql .= ' OFFSET ' . (int) $offset;
	}



	/**
	 * Normalizes result row.
	 */
	public function normalizeRow($row, $statement)
	{
		return $row;
	}

}
