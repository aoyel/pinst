<?php

namespace pinst\base;

abstract class Db extends Object{

	public $host = "localhost";
	public $port = 3306;
	public $username="root";
	public $password = "";
	public $database = "";
	public $charset = "utf-8";
	public $prefix = "";
	
	
	protected $_db = null;
	protected $_error = null;
	
	protected $_tableName = "";
	protected $_fields = "*";
	protected $_query = "";
	protected $_join = [];
	protected $_having = [];
	protected $_groupBy = [];
	protected $_orderBy = [];
	protected $_where = [];
	protected $_limit = null;
	
	public $totalCount = 0;
	
	public $execTime = 0;
	
	protected $_transaction_in_progress = false;
	
	public function init(){
		parent::init();
	}
	
	protected function log($msg,$level=Logger::LEVEL_DEBUG){
		if($level < Logger::LEVEL_WARNING){
			\Pinst::debug($msg);
		}else{
			\Pinst::error($msg);
		}
	}
	

	/**
	 * get last error message
	 */
	public function getLastError(){
		return $this->_error;
	}
	/**
	 * set error message
	 * @param string $error
	 */
	protected function setLastError($error){
		$this->log($error,Logger::LEVEL_ERROR);
		$this->_error = $error;
	}
	
	protected function reset()
	{
		$this->_tableName = null;
		$this->_fields = "*";
		$this->_where = array();
		$this->_join = array();
		$this->_orderBy = array();
		$this->_groupBy = array();
		$this->_query = null;
		$this->totalCount = 0;
	}
	
	function __destruct(){
		$this->close();
	}
	/**
	 *
	 * @return
	 */
	protected function getDb(){
		return $this->_db;
	}
	
	/**
	 * get table name
	 * @param string $tableName
	 * @return string
	 */
	public function getTableName($tableName){
		if(empty($this->prefix))
			return $tableName;
		
		if(stripos($this->prefix, $tableName) === false)
			return $this->prefix.$tableName;
		
		return $tableName;
	}
	
	public function from($tableName){
		$this->_tableName = $this->getTableName($tableName);
		return $this;
	}
	
	/**
	 * set field
	 * @param string $fields
	 * @return
	 */
	public function fields($fields="*"){
		if(is_string($fields)){
			$this->_fields = $fields;
		}elseif (is_array($fields)){
			$this->_fields = implode(",", $fields);
		}
		return $this;
	}
	
	/**
	 * This method allows you to concatenate joins for the final SQL statement.
	 * @param string $joinTable
	 * @param string $joinCondition
	 * @param string $joinType
	 * @return boolean|
	 */
	public function join($joinTable, $joinCondition, $joinType = '')
	{
		$allowedTypes = array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER');
		$joinType = strtoupper (trim ($joinType));
	
		if ($joinType && !in_array ($joinType, $allowedTypes)){
			$this->setLastError('Wrong JOIN type: '.$joinType);
			return false;
		}
		$joinTable = $this->getTableName($joinTable);
		$this->_join[] = [$joinType,  $joinTable, $joinCondition];
		return $this;
	}
	
	/**
	 * @uses 
	 * <p>Base user:</p>
	 * $db->where(['id'=>1])
	 * <p>other user</p>
	 * $db->where(['id'=>['in','1,2,3']])
	 * @param string|array $where
	 */
	public function where($where){
		if(empty($this->_where))
			$this->_where = $where;
		else
			if(is_array($where))
				$this->_where = array_merge($this->_where,$where);
			else
				$this->_where = $where;
		return $this;
	}
		
	/**
	 * 
	 * @param string $havingProp
	 * @param string $havingValue
	 * @param string $operator
	 * @return
	 */
	public function having($havingProp, $havingValue = null, $operator = null)
	{
		if ($operator)
			$havingValue = Array ($operator => $havingValue);
	
		$this->_having[] = Array ("AND", $havingValue, $havingProp);
		return $this;
	}
	
	/**
	 * This method allows you to specify multiple (method chaining optional) ORDER BY statements for SQL queries.
	 * @uses $MySqliDb->orderBy('id', 'desc')->orderBy('name', 'desc');
	 * @param string $orderByField
	 * @param string $orderbyDirection
	 * @param string $customFields
	 * @return \pinst\base\Db
	 */
	public function orderBy($orderByField, $orderbyDirection = "DESC", $customFields = null)
	{
		$allowedDirection = Array ("ASC", "DESC");
		$orderbyDirection = strtoupper (trim ($orderbyDirection));
		$orderByField = preg_replace ("/[^-a-z0-9\.\(\),_`\*\'\"]+/i",'', $orderByField);
	
		// Add table prefix to orderByField if needed.
		//FIXME: We are adding prefix only if table is enclosed into `` to distinguish aliases
		// from table names
		$orderByField = preg_replace('/(\`)([`a-zA-Z0-9_]*\.)/', '\1' . $this->prefix.  '\2', $orderByField);
		if (empty($orderbyDirection) || !in_array ($orderbyDirection, $allowedDirection))
			die ('Wrong order direction: '.$orderbyDirection);
	
		if (is_array ($customFields)) {
			foreach ($customFields as $key => $value)
				$customFields[$key] = preg_replace ("/[^-a-z0-9\.\(\),_`]+/i",'', $value);
	
			$orderByField = 'FIELD (' . $orderByField . ', "' . implode('","', $customFields) . '")';
		}
	
		$this->_orderBy[$orderByField] = $orderbyDirection;
		return $this;
	}
		
	/**
	 * This method allows you to specify multiple (method chaining optional) GROUP BY statements for SQL queries.
	 * @uses $MySqliDb->groupBy('name');
	 * @param string $groupByField
	 * @return \pinst\base\Db
	 */
	public function groupBy($groupByField)
	{
		$groupByField = preg_replace ("/[^-a-z0-9\.\(\),_\*]+/i",'', $groupByField);	
		$this->_groupBy[] = $groupByField;
		return $this;
	}
	
	public function limit($limit){
		$this->_limit = $limit;
		return $this;
	}
	
	protected function buildTableData ($columns) {
		if (!is_array ($columns))
			return;
		$isInsert = preg_match ('/^[INSERT|REPLACE]/', $this->_query);
		$dataColumns = array_keys ($columns);
		if ($isInsert)
			$this->_query .= ' (`' . implode ($dataColumns, '`, `') . '`)  VALUES (';
		else
			$this->_query .= " SET ";
	
		if($isInsert){
			foreach ($columns as $row){
				$row = $this->escapeString($row);
				$this->_query .= "'{$row}',";
			}
		}else{
			foreach ($columns as $key=>$val){
				$val = $this->escapeString($val);
				$this->_query .= "`{$key}`='{$val}',";
			}
		}
		$this->_query = rtrim($this->_query,",");
		if ($isInsert)
			$this->_query .= ')';
	}
	
	protected function buildTable () {
		if(empty($this->_query) && !empty($this->_tableName)){
			$this->_query = "SELECT {$this->_fields} FROM `{$this->_tableName}` ";
		}
	}
	
	/**
	 * build inser sql schema
	 * @param string $tableName
	 * @param mixed $columns
	 * @return mysqli_stmt
	 */
	protected function buildInsertSchema($tableName,$columns){
		$this->_query = "INSERT INTO `{$tableName}` ";
		$this->buildTableData($columns);
		return $this;
	}
	
	/**
	 *  build update sql schema
	 * @param string $tableName
	 * @param mixed $columns
	 * @return mysqli_stmt
	 */
	protected function buildUpdateSchema($tableName,$columns){
		$this->_query = "UPDATE `{$tableName}` ";
		$this->buildTableData($columns);
		return $this;
	}
	
	
	
	protected function buildJoin () {
		if (empty ($this->_join))
			return;
		foreach ($this->_join as $data) {
			list ($joinType,  $joinTable, $joinCondition) = $data;
			$this->_query .= " " . $joinType. " JOIN " . $joinTable ." on " . $joinCondition;
		}
	}
	
	protected function buildWhere(){
		$this->_query .= $this->parseWhere($this->_where);
	}
	
	protected function buildGroupBy () {
		if (empty ($this->_groupBy))
			return;
	
		$this->_query .= " GROUP BY ";
		foreach ($this->_groupBy as $key => $value)
			$this->_query .= $value . ", ";
	
		$this->_query = rtrim($this->_query, ', ') . " ";
	}
	
	protected function buildOrderBy () {
		if (empty ($this->_orderBy))
			return;
	
		$this->_query .= " ORDER BY ";
		foreach ($this->_orderBy as $prop => $value) {
			if (strtolower (str_replace (" ", "", $prop)) == 'rand()')
				$this->_query .= "rand(), ";
			else
				$this->_query .= $prop . " " . $value . ", ";
		}
	
		$this->_query = rtrim ($this->_query, ', ') . " ";
	}
	
	protected function buildLimit () {
		if (empty ($this->_limit))
			return;
	
		if (is_array ($this->_limit))
			$this->_query .= ' LIMIT ' . (int)$this->_limit[0] . ', ' . (int)$this->_limit[1];
		else
			$this->_query .= ' LIMIT ' . (int)$this->_limit;
	}
	
	protected $comparison = array (
			'eq' => '=',
			'neq' => '<>',
			'gt' => '>',
			'egt' => '>=',
			'lt' => '<',
			'elt' => '<=',
			'notlike' => 'NOT LIKE',
			'like' => 'LIKE'
	);
	
	public function escapeString($str) {
		return addslashes ( $str );
	}
	protected function parseKey(&$key) {
		return $key;
	}
	protected function parseValue($value) {
		if (is_string ( $value )) {
			$value = '\'' . $this->escapeString ( $value ) . '\'';
		} elseif (isset ( $value [0] ) && is_string ( $value [0] ) && strtolower ( $value [0] ) == 'exp') {
			$value = $this->escapeString ( $value [1] );
		} elseif (is_array ( $value )) {
			$value = array_map ( array (
					$this,
					'parseValue'
			), $value );
		} elseif (is_null ( $value )) {
			$value = 'null';
		}
	
		return $value;
	}
	
	public function parseWhere($where, $addWhere = true) {
		if (empty ( $where ))
			return NUll;
		$whereStr = '';
		if (is_string ( $where )) {
			$whereStr = $where;
		} else {
			if (isset ( $where ['_logic'] )) {
				$operate = ' ' . strtoupper ( $where ['_logic'] ) . ' ';
				unset ( $where ['_logic'] );
			} else {
				$operate = ' AND ';
			}
			foreach ( $where as $key => $val ) {
				if ($val === null) {
					continue;
				}
				$whereStr .= '( ';
				if (! preg_match ( '/^[A-Z_\|\&\-.a-z0-9]+$/', trim ( $key ) )) {
					return false;
				}
				$multi = is_array ( $val ) && isset ( $val ['_multi'] );
				$key = trim ( $key );
				if (strpos ( $key, '|' )) {
					$array = explode ( '|', $key );
					$str = array ();
					foreach ( $array as $m => $k ) {
						$v = $multi ? $val [$m] : $val;
						$str [] = '(' . $this->parseWhereItem ( $this->parseKey ( $k ), $v ) . ')';
					}
					$whereStr .= implode ( ' OR ', $str );
				} elseif (strpos ( $key, '&' )) {
					$array = explode ( '&', $key );
					$str = array ();
					foreach ( $array as $m => $k ) {
						$v = $multi ? $val [$m] : $val;
						$str [] = '(' . $this->parseWhereItem ( $this->parseKey ( $k ), $v ) . ')';
					}
					$whereStr .= implode ( ' AND ', $str );
				} else {
					$whereStr .= $this->parseWhereItem ( $this->parseKey ( $key ), $val );
				}
				$whereStr .= ' )' . $operate;
			}
	
			$whereStr = substr ( $whereStr, 0, - strlen ( $operate ) );
		}
		if ($addWhere)
			return empty ( $whereStr ) ? '' : ' WHERE ' . $whereStr;
		else
			return empty ( $whereStr ) ? '' : $whereStr;
	}
	
	
	protected function parseWhereItem($key, $val) {
		$whereStr = '';
		if (is_array ( $val )) {
	
			if (is_string ( $val [0] )) {
				if (preg_match ( '/^(EQ|NEQ|GT|EGT|LT|ELT|NOTLIKE|LIKE)$/i', $val [0] )) {
					$whereStr .= $key . ' ' . $this->comparison [strtolower ( $val [0] )] . ' ' . $this->parseValue ( $val [1] );
				} elseif ('exp' == strtolower ( $val [0] )) {
					$whereStr .= ' (' . $key . ' ' . $val [1] . ') ';
				} elseif (preg_match ( '/IN/i', $val [0] )) {
					if (isset ( $val [2] ) && 'exp' == $val [2]) {
						$whereStr .= $key . ' ' . strtoupper ( $val [0] ) . ' ' . $val [1];
					} else {
						if (is_string ( $val [1] )) {
							$val [1] = explode ( ',', $val [1] );
						}
						$zone = implode ( ',', $this->parseValue ( $val [1] ) );
						$whereStr .= $key . ' ' . strtoupper ( $val [0] ) . ' (' . $zone . ')';
					}
				} elseif (preg_match ( '/BETWEEN/i', $val [0] )) {
					$data = is_string ( $val [1] ) ? explode ( ',', $val [1] ) : $val [1];
					$whereStr .= ' (' . $key . ' ' . strtoupper ( $val [0] ) . ' ' . $this->parseValue ( $data [0] ) . ' AND ' . $this->parseValue ( $data [1] ) . ' )';
				} else {
					return false;
				}
			} else {
	
				$count = count ( $val );
				if (in_array ( strtoupper ( trim ( $val [$count - 1] ) ), array (
						'AND',
						'OR',
						'XOR'
				) )) {
					$rule = strtoupper ( trim ( $val [$count - 1] ) );
					$count = $count - 1;
				} else {
					$rule = 'AND';
				}
				for($i = 0; $i < $count; $i ++) {
					$data = is_array ( $val [$i] ) ? $val [$i] [1] : $val [$i];
					if ('exp' == strtolower ( $val [$i] [0] )) {
						$whereStr .= '(' . $key . ' ' . $data . ') ' . $rule . ' ';
					} else {
						$op = is_array ( $val [$i] ) ? $this->comparison [strtolower ( $val [$i] [0] )] : '=';
						$whereStr .= '(' . $key . ' ' . $op . ' ' . $this->parseValue ( $data ) . ') ' . $rule . ' ';
					}
				}
				$whereStr = substr ( $whereStr, 0, - 4 );
			}
		} else {
	
			$whereStr .= $key . ' = ' . $this->parseValue ( $val );
		}
		return $whereStr;
	}
	
	
	protected function buildQuery(){
		$this->buildTable();
		$this->buildJoin();
		$this->buildWhere();
		$this->buildGroupBy();
		$this->buildOrderBy();
		$this->buildLimit();
	}
	
	abstract public function connection();
	
	abstract public function close();
	
	/**
	 * start transaction
	 * @return boolean
	 */
	abstract public function beginTransaction();
	
	/**
	 * commit
	 * @return boolean
	 */
	abstract public function commit();
	
	/**
	 * rollback
	 * @return boolean
	 */
	abstract public function rollback();
	
	
	/**
	 * get one record
	 */
	abstract public function find();
	
	/**
	 * query some record
	 */
	abstract public function select();


	abstract public function query($sql);


	/**
	 * @param $tableName
	 * @return mixed|self
	 */
	abstract public function getTableSchema($tableName);
	

	/**
	 * @param $tableName
	 * @param $columns
	 * @return mixed|self
	 */
	abstract public function insert($tableName,$columns);
	
	/**
	 * update table data
	 * @param string $tableName
	 * @param array $columns
	 * @param string|array $where
	 */
	abstract public function update($tableName,$columns,$where);


	abstract public function delete($tableName,$where);
	
	/**
	 * execute none dataset 
	 */
	abstract public function execute();
	
	/**
	 * get pk field
	 * @param string $tableName
	 */
	protected function getPk($tableName){
		$data = $this->getTableSchema($tableName);
		if(empty($data))
			return false;
		foreach ($data as $row){
			if($row['Key'] == "PRI"){
				return $row['Field'];
			}
		}
		return false;
	}
	
	/**
	 * get dataset form pk
	 * @param string $tableName
	 * @param string $pk
	 */
	public function get($tableName,$pk){
		$field = $this->getPk($this->getTableName($tableName));
		return $this->from($tableName)->where([$field=>$pk])->find();
	}
	
	/**
	 * get field value 
	 * @param string $tableName
	 * @param string $field
	 * @param array $where
	 */
	public function getFieldValue($tableName,$field,$where){
		$data = $this->table($tableName)->fields($field)->where($where)->find();
		if(!empty($data) && isset($data[$field]))
			return $data[$field];
		return null;
	}
		
	/**
	 * set field value
	 * @param string $tableName
	 * @param string $field
	 * @param mixed $value
	 * @param array|string $where
	 * @return NULL
	 */
	public function setFieldValue($tableName,$field,$value,$where){
		return $this->update($tableName, [$field=>$value], $where)->execute();
	}

	/**
	 * get dataset count
	 * @param string $tableName
	 * @param array|string $where
	 * @param string $field
	 */
	public function getCount($tableName,$where,$field = "*"){
		$data = $data = $this->from($tableName)->fields("COUNT({$field}) as count")->where($where)->find();
		if(!empty($data) && isset($data['count']))
			return intval($data['count']);
		return 0;
	}
	
}

?>