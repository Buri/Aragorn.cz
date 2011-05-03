<?php

/** Single row representation
*/
class NotORM_Row extends NotORM_Abstract implements IteratorAggregate, ArrayAccess {
	private $modified = array();
	protected $row, $result;
	
	protected function __construct(array $row, NotORM_Result $result) {
		$this->row = $row;
		$this->result = $result;
	}
	
	/** Get primary key value
	* @return string
	*/
	function __toString() {
		return (string) $this[$this->result->primary]; // (string) - PostgreSQL returns int
	}
	
	/** Get referenced row
	* @param string
	* @return NotORM_Row or null if the row does not exist
	*/
	function __get($name) {
		$column = $this->result->notORM->structure->getReferencedColumn($name, $this->result->table);
		$referenced = &$this->result->referenced[$name];
		if (!isset($referenced)) {
			$keys = array();
			foreach ($this->result->rows as $row) {
				if ($row[$column] !== null) {
					$keys[$row[$column]] = null;
				}
			}
			if ($keys) {
				$table = $this->result->notORM->structure->getReferencedTable($name, $this->result->table);
				$referenced = new NotORM_Result($table, $this->result->notORM);
				$referenced->where("$table." . $this->result->notORM->structure->getPrimary($table), array_keys($keys));
			} else {
				$referenced = array();
			}
		}
		if (!isset($referenced[$this[$column]])) { // referenced row may not exist
			return null;
		}
		return $referenced[$this[$column]];
	}
	
	/** Test if referenced row exists
	* @param string
	* @return bool
	*/
	function __isset($name) {
		return ($this->__get($name) !== null);
	}
	
	// __set is not defined to allow storing custom references (undocumented)
	
	/** Get referencing rows
	* @param string table name
	* @param array (["condition"[, array("value")]])
	* @return NotORM_MultiResult
	*/
	function __call($name, array $args) {
		$table = $this->result->notORM->structure->getReferencingTable($name, $this->result->table);
		$column = $this->result->notORM->structure->getReferencingColumn($table, $this->result->table);
		$return = new NotORM_MultiResult($table, $this->result, $column, $this[$this->result->primary]);
		$return->where("$table.$column", array_keys((array) $this->result->rows)); // (array) - is null after insert
		if ($args) {
			call_user_func_array(array($return, 'where'), $args);
		}
		return $return;
	}
	
	/** Update row
	* @param array or null for all modified values
	* @return int number of affected rows or false in case of an error
	*/
	function update($data = null) {
		// update is an SQL keyword
		if (!isset($data)) {
			$data = $this->modified;
		}
		return $this->result->notORM->__call($this->result->table, array($this->result->primary, $this[$this->result->primary]))->update($data);
	}
	
	/** Delete row
	* @return int number of affected rows or false in case of an error
	*/
	function delete() {
		// delete is an SQL keyword
		return $this->result->notORM->__call($this->result->table, array($this->result->primary, $this[$this->result->primary]))->delete();
	}
	
	protected function access($key, $delete = false) {
		if ($this->result->notORM->cache && $this->result->access($key, $delete)) {
			$this->row = $this->result[$this->row[$this->result->primary]]->row;
		}
	}
	
	// IteratorAggregate implementation
	
	function getIterator() {
		$this->access(null);
		return new ArrayIterator($this->row);
	}
	
	// ArrayAccess implementation
	
	/** Test if column exists
	* @param string column name
	* @return bool
	*/
	function offsetExists($key) {
		$this->access($key);
		$return = array_key_exists($key, $this->row);
		if (!$return) {
			$this->access($key, true);
		}
		return $return;
	}
	
	/** Get value of column
	* @param string column name
	* @return string
	*/
	function offsetGet($key) {
		$this->access($key);
		if (!array_key_exists($key, $this->row)) {
			$this->access($key, true);
		}
		return $this->row[$key];
	}
	
	/** Store value in column
	* @param string column name
	* @return null
	*/
	function offsetSet($key, $value) {
		$this->row[$key] = $value;
		$this->modified[$key] = $value;
	}
	
	/** Remove column from data
	* @param string column name
	* @return null
	*/
	function offsetUnset($key) {
		unset($this->row[$key]);
		unset($this->modified[$key]);
	}
	
}
