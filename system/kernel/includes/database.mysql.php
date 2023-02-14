<?php
/**
 * MySQL Data Access
 * Object Data Access su dung MySQL
 *
 * @author 			NamDD <namdd@xahoinet.vn>
 * @since 			JOC
 * @version 		1.0
 * @package 		system
 * @subpackage 		database
 *
 */

defined('IN_JOC') or die ('Restricted Access');

class EMySQL extends EDatabase
{
	/**
	 * Resource
	 *
	 * @var MySQL Resource
	 */
	private $_resource;

	/**
	 * Database Name
	 *
	 * @var string
	 */
	private $_dbname;

	/**
	 * Result
	 * mysql query Result
	 *
	 */
	private $_result;

	/**
	 * Sql
	 * cau lenh SQL
	 *
	 * @var string
	 */
	private $_sql;

	/**
	 * Use Transaction
	 *
	 * @var boolean
	 */
	private $_transaction = false;

	/**
	 * Constructor
	 * Khoi tao ket noi
	 *
	 * @param Array $config
	 */
	public function __construct($config)
	{
		if (isset($config['option']) && $config['option']['persistent'] === true)
		{
			$this->_resource = @mysql_pconnect($config['host'], $config['username'], $config['password']);

		}
		else
		{
			$this->_resource = @mysql_connect($config['host'], $config['username'], $config['password']);
			if(!$this->_resource)
			{
				$this->_resource = @mysql_connect($config['host_reserve'], $config['username'], $config['password']);
				$config['host']=$config['host_reserve'];
			}

		}
			
		if (isset($config['dbname'])) {
			$this->selectDb($config['dbname']);
			$this->query('SET NAMES utf8');
		}

		if(mysql_errno > 0 || !$this->_resource) {
			throw new SystemException('Unable to open MySQL connection:' . mysql_error());
		}

		parent::__construct();
	}

	/**
	 * Set Transaction
	 *
	 * @param boolean $b
	 */
	public function setTransaction($b) {
		$this->_transaction = (boolean) $b;
	}

	/**
	 * Get Type
	 * Type of Object Connection
	 *
	 * @return int
	 */
	public function getType()
	{
		return parent::TYPE_MYSQL;
	}

	/**
	 * Get SQL String
	 *
	 * @return String cau lenh SQL
	 */
	public function getSql() {
		return $this->_sql;
	}

	/**
	 * Set SQL Query
	 *
	 * @param String $_sql
	 */
	public function setSql($sql) {
		$this->_sql = (string) $sql;
	}

	/**
	 * Select Db
	 *
	 * @param String $dbname Database Name
	 */
	public function selectDb($dbname) {
		if ($this->_dbname != $dbname)
		{
			$this->_dbname = $dbname;
			mysql_select_db($dbname, $this->_resource);
		}
	}

	/**
	 * @return unknown
	 */
	public function getDatabase() {
		return $this->_dbname;
	}

	/**
	 * Is Connected
	 * kiem tra viec connect
	 *
	 * @return boolean true neu viec connect thanh cong
	 */
	public function isConnected()
	{
		if(is_resource($this->_resource)) {
			return mysql_ping();
		}
		return false;
	}

	/**
	 * SQL Name Quote
	 *
	 * @param string $text
	 * @return string
	 */
	public function nameQuote($text)
	{
		//  $text = $this->_resource->real_escape_string($text);
		if (strpos( '.', $text ) === false and strpos( '`', $text ) === false and strpos( '*', $text ) === false)
		{
			return '`' . trim($text). '`';
		}elseif($text == '*')
		return $text;
		else {
			return $text;
		}
	}
	/**
	 * SQL Name Quote
	 * @author bangtd
	 * @param string $listField
	 * @return string
	 */
	protected function addListFieldQuote($listField)
	{
		$listField = trim($listField);
		if($listField !== '' and $listField !== '*')
		{
			$arrListField = explode(',', $listField);
			$listTmp = '';
			for($i = 0; $i < count($arrListField); $i++)
			{
				if($listTmp === '')
				$listTmp = $this -> nameQuote($arrListField[$i]);
				else
				$listTmp .= ','.$this -> nameQuote($arrListField[$i]);
			}
			$listField = $listTmp;
		}
		return $listField;
	}

	/**
	 * SQL Name Quote
	 * @author bangtd
	 * @param string $arrData
	 * @return string
	 */
	protected function addArrFieldQuote($arrData)
	{
		$arrTmp = array();
		if(is_array($arrData) and count($arrData) > 0)
		{
			foreach($arrData as $key => $value)
			{
				if(!is_array($value))
				{
					$key = $this -> nameQuote($key);
					$arrTmp[$key] = $value;
				}
				else
				{
					foreach($value as $key1 => $value1)
					{
						$key1 = $this -> nameQuote($key1);
						$arrTmp[$key][$key1] = $value1;
					}
				}
			}
			$arrData = array();
			$arrData = $arrTmp;
		}
		return $arrData;
	}
	/**
	 * Quote
	 *
	 * @param string $text
	 * @return string
	 */
	public function quote($text, $escaped = true)
	{
		if($text === null)
		return 'null';
		elseif($text === 0)
		return $text;
		else
		return '\''.($escaped ? mysql_real_escape_string($text, $this->_resource) : $text).'\'';
	}

	/**
	 * Get Affected Rows
	 *
	 * @return int
	 */
	public function affectedRows() {
		if(mysql_affected_rows($this->_resource))
		return mysql_affected_rows($this->_resource);
		else
		return $this -> _result;
	}

	/**
	 * Last Insert Id
	 *
	 * @return int
	 */
	public function lastInsertId()
	{
		if(mysql_insert_id($this->_resource))
		return mysql_insert_id($this->_resource);
		else
		return $this -> _result;
	}

	/**
	 * Query
	 *
	 * @param string $sql
	 * @return result
	 */
	public function query($sql = null) {
		if ($sql == null)
		{
			$sql = $this->_sql;
		}
		else
		{
			$this->_sql = (string) $sql;
		}

		if ($sql == '' || $sql == null)
		throw new SystemException('SQL query string is NULL');

		$begin = $this->getMicrotime();
		$this->_result = mysql_query($sql, $this->_resource);
		$end = $this->getMicrotime();

		if (IN_DEBUG)
		{
			$this->log($sql, ($end - $begin));
		}

		if(!$this->_result) {
			$message = mysql_error($this->_resource) . ' SQL= ' . $sql . ' - ' . mysql_errno($this->_resource);
			throw new SystemException($message);
		}

		return $this->_result;
	}

	/**
	 * Diagnostic function
	 *
	 * @return	string
	 */
	public function explain($sql = null)
	{
		if ($sql === null) {
			$sql = $this->_sql;
		}

		$temp = $sql;
			
		$sql = 'EXPLAIN ' . $sql;

		if (!($cur = $this->query($sql))) {
			return null;
		}
		$first = true;

		$buffer = '<table id="explain-sql">';
		$buffer .= '<thead><tr><td colspan="99"> ' . $temp . ' </td></tr>';
		while ($row = mysql_fetch_assoc($cur)) {
			if ($first) {
				$buffer .= '<tr>';
				foreach ($row as $k=>$v) {
					$buffer .= '<th>'.$k.'</th>';
				}
				$buffer .= '</tr>';
				$first = false;
			}
			$buffer .= '</thead><tbody><tr>';
			foreach ($row as $k=>$v) {
				$buffer .= '<td>'.$v.'</td>';
			}
			$buffer .= '</tr>';
		}
		$buffer .= '</tbody></table>';

		mysql_free_result( $cur );

		$this->_sql = $temp;

		return $buffer;
	}

	/**
	 * Free Result
	 *
	 */
	public function freeResult() {
		mysql_free_result($this->_result);
	}

	/**
	 * Fetch
	 * fetch ket qua dau tien cua cau lenh SELECT
	 *
	 * @return Mixed array/ null
	 */
	public function fetch()
	{
		$result = mysql_fetch_assoc($this->_result);
		mysql_free_result($this->_result);
		return $result;
	}

	/**
	 * Fetch All
	 *
	 * @param string $key
	 * @return Array
	 */
	public function fetchAll($key = '')
	{
		$values = array();
		while ($row = mysql_fetch_assoc($this->_result)) {
			if ($key != '') {
				$values[$row[$key]] = $row;
			}
			else {
				$values[] = $row;
			}
		}

		mysql_free_result($this->_result);
		return $values;
	}

	/**
	 * Select
	 *
	 * @param string $table DBTable Name
	 * @param string $fields Table Columns
	 * @param string $where SQL Query WHERE clause
	 * @param string $order SQL Query ORDER clause
	 * @param string $limit SQL Query LIMT
	 * @return array
	 */
	public function select($table, $fields, $where = null, $order = null, $limit = null, $key = '')
	{
		$fields = $this -> addListFieldQuote($fields);
		$sql = 'SELECT ' . $fields .' FROM ' . $this->nameQuote($table)
		.(($where != null)? ' WHERE ' . $where : '')
		.(($order != null)? ' ORDER BY ' .$order : '')
		.(($limit != null)? ' LIMIT ' . $limit : '');

		$this->query($sql);
		return $this->fetchAll($key);
	}

	/**
	 * Select One
	 *
	 * @param string $table DBTable
	 * @param string $fields Table columns
	 * @param string $where SQL Query WHERE clause
	 * @return mixed/array
	 *
	 * @throws SystemException
	 */
	public function selectOne($table, $fields, $where = null)
	{
		$fields = $this -> addListFieldQuote($fields);
		$sql = 'SELECT ' . $fields .' FROM ' . $this->nameQuote($table)
		.(($where != null)? ' WHERE ' . $where : '');
		$sql .= ' LIMIT 1';

		$this->query($sql);
		return $this->fetch();
	}

	/**
	 * Insert
	 *
	 * @param string $table DBTable name
	 * @param array $data
	 * @return int Last Insert Id
	 *
	 * @throws SystemException
	 */
	public function insert($table, $data)
	{
		if (!is_array($data) || sizeof($data) == 0)
		throw new SystemException('Empty insert data or data not valid');
			
		$datas = array();
		$datas[] = $data;

		return $this->insertMulti($table, $datas);
	}

	/**
	 * Insert many records in single query
	 *
	 * @param string $table DBTable name
	 * @param array $data
	 * @return int Last Insert Id
	 *
	 * @throws SystemException
	 */
	public function insertMulti($table, $datas)
	{
		if (!is_array($datas) || sizeof($datas) == 0 )
		throw new SystemException('Empty insert data or data not valid');
			
		$table = $this->nameQuote($table);
		$sql = 'INSERT INTO ' . $table .' (';
		$records = sizeof($datas);

		/**
		 * TODO Build danh sach cac truong can insert
		 */
		$columns = array_keys($datas[0]);
		$sizeOfColumns = sizeof($columns);
		$sql .= '`' . implode('`, `', $columns) . '`) VALUES ';

		/**
		 * TODO Build gia tri cua cac truong can insert
		 */
		for ($i = 0; $i < $records; ++$i)
		{
			$value = array_values($datas[$i]);
			$sql .= "\n(";

			for ($index = 0; $index < $sizeOfColumns; ++$index)
			{
				$sql .= $this->quote($value[$index]);

				if ($index != ($sizeOfColumns-1))
				{
					$sql .= ', ';
				}
			}

			if ($i != ($records - 1))
			{
				$sql .= '), ';
			}
			else
			{
				$sql .= ")";
			}
		}
		$this->query($sql);
		return $this->lastInsertId();
	}
	/**
	 * Insert Duplicate
	 * @param unknown_type $table
	 * @param unknown_type $datas
	 */
	public function insertDuplicate($table, $datas,$queryUpdate="")
	{
		if (!is_array($datas) || sizeof($datas) == 0 )
		throw new SystemException('Empty insert data or data not valid');
			
		$table = $this->nameQuote($table);
		$sql = 'INSERT INTO ' . $table .' (';
		$records = sizeof($datas);

		/**
		 * TODO Build danh sach cac truong can insert
		 */
		$columns = array_keys($datas[0]);
		$sizeOfColumns = sizeof($columns);
		$sql .= '`' . implode('`, `', $columns) . '`) VALUES ';

		/**
		 * TODO Build gia tri cua cac truong can insert
		 */
		for ($i = 0; $i < $records; ++$i)
		{
			$value = array_values($datas[$i]);
			$sql .= "\n(";

			for ($index = 0; $index < $sizeOfColumns; ++$index)
			{
				$sql .= $this->quote($value[$index]);

				if ($index != ($sizeOfColumns-1))
				{
					$sql .= ', ';
				}
			}

			if ($i != ($records - 1))
			{
				$sql .= '), ';
			}
			else
			{
				$sql .= ")";
			}
		}
		if($queryUpdate  ==='' || $queryUpdate  ===null)
		throw new SystemException('Empty codition update key');
		$sql .= ' ON DUPLICATE KEY UPDATE '. $queryUpdate ;
		$this->query($sql);
		return $this->lastInsertId();
	}
	/**
	 * Insert Ignore
	 * @param unknown_type $table
	 * @param unknown_type $datas
	 * @author HanVanLoi
	 */
	public function insertIgnore($table, $datas)
	{
		if (!is_array($datas) || sizeof($datas) == 0 )
		throw new SystemException('Empty insert data or data not valid');
			
		$table = $this->nameQuote($table);
		$sql = 'INSERT IGNORE INTO ' . $table .' (';
		$records = sizeof($datas);

		/**
		 * TODO Build danh sach cac truong can insert
		 */
		$columns = array_keys($datas[0]);
		$sizeOfColumns = sizeof($columns);
		$sql .= '`' . implode('`, `', $columns) . '`) VALUES ';

		/**
		 * TODO Build gia tri cua cac truong can insert
		 */
		for ($i = 0; $i < $records; ++$i)
		{
			$value = array_values($datas[$i]);
			$sql .= "\n(";

			for ($index = 0; $index < $sizeOfColumns; ++$index)
			{
				$sql .= $this->quote($value[$index]);

				if ($index != ($sizeOfColumns-1))
				{
					$sql .= ', ';
				}
			}

			if ($i != ($records - 1))
			{
				$sql .= '), ';
			}
			else
			{
				$sql .= ")";
			}
		}
		$this->query($sql);
		return $this->lastInsertId();
	}
	/**
	 * Update
	 *
	 * @param string $table Db Table Name
	 * @param string $data Du lieu update voi key la ten truong va value la gia tri can update
	 * @param string $where SQL query WHERE clause
	 *
	 * @return affected rows
	 * @throws SystemException
	 */
	public function update($table, $data, $where)
	{
		if(!is_array($data) || ($size = sizeof($data)) == 0) {
			throw new SystemException('Empty update data or data not valid');
		}
		$sql = 'UPDATE ' . $this->nameQuote($table) .' SET ';
		$index = 1;

		foreach ($data as $column => $value)
		{
			$column = $this->nameQuote($column);

			$sql .= $column . '=' . $this->quote($value);

			if ($index !== $size)
			$sql .= ', ';

			++$index;
		}

		$sql .= ' WHERE ' . $where;

		var_dump($this->query($sql));
		return $this->affectedRows();
	}

	/**
	 * Delete
	 *
	 * @param string $table Db Table Name
	 * @param string $where SQL query WHERE clause
	 * @param string $limit SQL query LIMIT
	 *
	 * @return affected rows
	 * @throws SystemException
	 */
	public function delete($table, $where, $limit = null)
	{
		$sql = 'DELETE FROM ' . $this->nameQuote($table) . ' WHERE ' . $where
		.(($limit != null)? ' LIMIT ' .$limit:'');

		$this->query($sql);
		return $this->affectedRows();
	}

	/**
	 * Delete By Id
	 * delete record by primary key
	 *
	 * @param string $table
	 * @param int $id
	 * @return int affectd rows
	 *
	 * @throws SystemException
	 */
	public function deleteById($table, $id)
	{
		return $this->delete($table, 'id=' . $id);
	}

	/**
	 * Count number of records by key
	 *
	 * @param string $table DBTable Name
	 * @param string $where SQL Query Where Clause
	 * @param string $key column name to count
	 *
	 * @return int result
	 */
	public function count($table, $where = null, $key = 'id')
	{
		$key = $this->nameQuote($key);
		$table = $this->nameQuote($table);

		$sql = 'SELECT COUNT(' . $key . ') AS rows FROM ' . $table
		.(($where != null)? ' WHERE ' . $where : '');
		$this->query($sql);
		$result = $this->fetch();
		return (int) $result['rows'];
	}

	/**
	 * Begin Transaction
	 *
	 * @return boolean
	 */
	public function beginTransaction() {
		if (!$this->_transaction)
		return false;
		return mysql_query('START TRANSACTION');
	}

	/**
	 * Commit
	 *
	 * @return boolean
	 *
	 */
	public function commit() {
		if (!$this->_transaction)
		return false;
		return mysql_query('COMMIT');
	}

	/**
	 * Roll Back
	 *
	 * @return boolean
	 *
	 */
	public function rollBack() {
		if (!$this->_transaction)
		return false;
		return mysql_query('ROLLBACK');
	}

	/**
	 * Close
	 * close connection
	 *
	 * @return boolean
	 *
	 */
	public function close()
	{
		if (is_resource($this->_resource))
		{
			return mysql_close($this->_resource);
		}
		return false;
	}

	/**
	 * Destructor
	 * close connection
	 */
	public function __destruct()
	{
		if (is_resource($this->_resource))
		{
			mysql_close($this->_resource);
		}
	}
}

