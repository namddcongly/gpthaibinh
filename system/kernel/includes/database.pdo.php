<?php
/**
 * Doi tuong Data Access su dung PDO
 * @author 			NamDD <namdd@xahoinet.vn>
 * @since 			JOC
 * @version 		1.0
 */

class EPDO extends EDatabase
{
	private $_sql;
	private $_dbname;
	private $pdo;
	private $stmt;

	/**
	 * Use Transaction
	 *
	 * @var boolean
	 */
	private $_transaction = false;

	public function __construct($config)
	{
		$server = $config['host'];
		$server = explode(':', $server);
		$driver = (isset($config['driver'])? $config['driver'] : 'mysql');
		$dsn =  $driver . ':host=' . $server[0]
		.(isset($server[1])? ';port=' . $server[1] : '');

		if (isset($config['dbname']))
		{
			$this->_dbname = $config['dbname'];
			$dsn .= ';dbname=' . $config['dbname'];
		}

		$driverOptions = array();
		if (isset($config['option']))
		{
			$this->processDriverOptions($config['option'], $driverOptions);
			$this->query('SET NAMES utf8');
		}
		try
		{
			$this->pdo = new PDO($dsn, $config['username'], $config['password'], $driverOptions);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			try{ // co gang chuyen sang server du phong

				$driver = (isset($config['driver'])? $config['driver'] : 'mysql');
				$dsn =  $driver . ':host='.$config['host_reserve'];
				if (isset($config['dbname']))
				{
					$this->_dbname = $config['dbname'];
					$dsn .= ';dbname=' . $config['dbname'];
				}
				$config['host']=$config['host_reserve'];
				$this->pdo = new PDO($dsn, $config['username'], $config['password'], $driverOptions);
				$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch(PDOException $e){
				throw new SystemException('Unable to open PDO connection', $e);
			}
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
	 * Process Driver Options
	 *
	 * @param Array $options
	 * @param Array &$writeOn
	 */
	private function processDriverOptions($options, &$writeOn)
	{
		foreach ($options as $attr => $value)
		{
			if (is_string($attr) && strpos($attr, 'PDO::') !== false)
			{
				$attr = strtoupper($attr);
			}
			else {
				$attr = 'PDO::ATTR_' . strtoupper($attr);
			}

			$attr =  constant($attr);
			$writeOn[$attr] = $value;
		}
	}

	/**
	 * Get Type
	 * Type of Object Connection
	 *
	 * @return int
	 */
	public function getType()
	{
		return parent::TYPE_PDO;
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
	 * Select Database
	 *
	 * @param string $dbname Database Name
	 */
	public function selectDb($dbname)
	{
		$this->_dbname = $dbname;
	}

	/**
	 * Get Database
	 * get current DB name
	 *
	 * @return string
	 */
	public function getDatabase()
	{
		return $this->_dbname;
	}

	/**
	 * Is Connected
	 *
	 * @return boolean
	 */
	public function isConnected()
	{
		return !is_null($this->pdo);
	}

	/**
	 * SQL Name Quote
	 *
	 * @param string $text
	 * @return string
	 */
	public function nameQuote($text)
	{
		return '`' . $text . '`';
	}

	/**
	 * Quote
	 *
	 * @param string $text
	 * @return string
	 */
	public function quote($text)
	{
		return $this->pdo->quote($text);
	}

	/**
	 * Affected Rows
	 * the affected rows of the last query
	 *
	 * @return int
	 */
	public function affectedRows()
	{
		if ($this->stmt == null)
		return 0;

		return $this->stmt->rowCount();
	}

	/**
	 * Last Insert Id
	 *
	 * @return int
	 */
	public function lastInsertId()
	{
		return $this->pdo->lastInsertId();
	}

	/**
	 * Query
	 *
	 * @param string $sql
	 */
	public function query($sql = null)
	{
		if ($sql === null)
		{
			$sql = $this->_sql;
		}
		else
		{
			$this->_sql = (string) $sql;
		}

		if ($sql == '' || $sql == null) {
			throw new SystemException('SQL query string is Null');
		}
		$begin = $this->getMicrotime();

		$this->prepare($sql);
		$this->execute();

		$end = $this->getMicrotime();

		if (IN_DEBUG)
		{
			$this->log($sql, ($end - $begin));
		}

		return $this->stmt;
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
		while ($row = $cur->fetch(PDO::FETCH_ASSOC))
		{
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
		$this->freeResult();

		$this->_sql = $temp;

		return $buffer;
	}

	/**
	 * prepare
	 *
	 * @param string $sql
	 */
	private function prepare($sql = null)
	{
		try
		{
			$this->stmt = $this->pdo->prepare($sql);
		}
		catch (PDOException $e)
		{
			throw new SystemException('Error prepare query =' . $sql, $e);
		}

	}

	/**
	 * Execute
	 *
	 */
	private function execute()
	{
		try
		{
			$this->stmt->execute();
		}
		catch (PDOException $e)
		{
			throw new SystemException('Error execute query = ' . $this->_sql, $e);
		}
	}

	/**
	 * Free Result
	 */
	public function freeResult() {
		$this->stmt->closeCursor();
	}

	/**
	 * Fetch
	 * fetch ket qua dau tien cua cau lenh SELECT
	 *
	 * @return Array
	 */
	public function fetch()
	{
		$result = $this->stmt->fetch(PDO::FETCH_ASSOC);
		$this->freeResult();
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
		$result = array();

		while ($row = $this->stmt->fetch(PDO::FETCH_ASSOC))
		{
			if ($key != '')
			{
				$result[$row[$key]] = $row;
			}
			else
			{
				$result[] = $row;
			}
		}
		$this->freeResult();
		return $result;
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
	 *
	 * @throws SystemException
	 */
	public function select($table, $fields, $where = null, $order = null, $limit = null, $key = '')
	{
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
		$sql = 'SELECT ' . $fields .' FROM ' . $this->nameQuote($table)
		.(($where != null)? ' WHERE ' . $where : '');
		$sql .= ' LIMIT 1';

		$this->query($sql);
		return $this->fetch();
	}

	/**
	 * Insert Single Record
	 *
	 * @param string $table DBTable name
	 * @param array $data
	 * @return int Last Insert Id
	 *
	 * @throws SystemException
	 */
	public function insert($table, $data)
	{
		if (!is_array($data))
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
		$sql = $this->buildInsertMutipleRecords($table, $datas);
		/**
		 * TODO tao ra mot mang 1 chieu chua cac gia tri danh cho viec bind data
		 */
		$databind = array();
		for ($i = 0, $size = sizeof($datas); $i < $size; ++$i)
		{
			$databind = array_merge($databind, array_values($datas[$i]));
		}

		$begin = $this->getMicrotime();

		$this->prepare($sql);
		$this->populateStmtValues($databind);
		$this->execute();

		$end = $this->getMicrotime();

		if (IN_DEBUG)
		{
			$this->log($sql, ($end-$begin));
		}
		return $this->lastInsertId();
	}
	/**
	 * insert Duplicate
	 *
	 * @param unknown_type $table
	 * @param unknown_type $datas
	 */
	public function insertDuplicate($table, $datas, $queryUpdate="")
	{
		$sql = $this->buildInsertMutipleRecords($table, $datas);
		if($queryUpdate  ==='' || $queryUpdate  ===null)
		throw new SystemException('Empty codition update key');
		$sql .= ' ON DUPLICATE KEY UPDATE '. $queryUpdate ;
		/**
		 * TODO tao ra mot mang 1 chieu chua cac gia tri danh cho viec bind data
		 */
		$databind = array();
		for ($i = 0, $size = sizeof($datas); $i < $size; ++$i)
		{
			$databind = array_merge($databind, array_values($datas[$i]));
		}

		$begin = $this->getMicrotime();

		$this->prepare($sql);
		$this->populateStmtValues($databind);
		$this->execute();

		$end = $this->getMicrotime();

		if (IN_DEBUG)
		{
			$this->log($sql, ($end-$begin));
		}
		return $this->lastInsertId();
	}
	/**
	 * insert Duplicate
	 *
	 * @param unknown_type $table
	 * @param unknown_type $datas
	 */
	public function insertIgnore($table, $datas)
	{
		$sql = $this->buildInsertMutipleRecords($table, $datas);
		/**
		 * TODO tao ra mot mang 1 chieu chua cac gia tri danh cho viec bind data
		 */
		$databind = array();
		for ($i = 0, $size = sizeof($datas); $i < $size; ++$i)
		{
			$databind = array_merge($databind, array_values($datas[$i]));
		}

		$begin = $this->getMicrotime();

		$this->prepare($sql);
		$this->populateStmtValues($databind);
		$this->execute();

		$end = $this->getMicrotime();

		if (IN_DEBUG)
		{
			$this->log($sql, ($end-$begin));
		}
		return $this->lastInsertId();
	}
	/**
	 * Build Insert Mutiple Records
	 *
	 * @param string $table ten table
	 * @param array $data : Moi ban ghi can insert se la mot phan tu cua mang nay
	 *
	 * @return string sql
	 */
	private function buildInsertMutipleRecords($table, $data,$ignore = false)
	{
		$columns = array_keys($data[0]);
		if(!$ignore){
			$sql = "INSERT INTO " . $table
			." (" .implode(', ', $columns)
			.") VALUES \n";
		}else{
			$sql = "INSERT IGNORE INTO " . $table
			." (" .implode(', ', $columns)
			.") VALUES \n";
		}
		$sizeOfColumn = sizeof($columns);

		for ($i= 1, $numberRecord = sizeof($data); $i <= $numberRecord; $i++)
		{
			$sql .= '(';
			for($p = 1; $p <= $sizeOfColumn; $p++) {
				$sql .= ':p'.($p + ($i-1)*$sizeOfColumn);
				if ($p !== $sizeOfColumn) $sql .= ',';
			}
			$sql .= ")\n";

			if ($i !== $numberRecord) $sql .= ',';
		}
		return $sql;
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
		$sql = $this->buildUpdateSql($table, $data, $where);
		$begin = $this->getMicrotime();
		$this->prepare($sql);
		$this->populateStmtValues(array_values($data));
		$this->execute();
		$end = $this->getMicrotime();

		if (IN_DEBUG) {
			$this->log($sql, ($end - $begin));
		}

		return $this->affectedRows();
	}

	/**
	 * Build Update Sql
	 * Ham lam nhiem vu gen ra cau lenh sql thuc hien viec update
	 *
	 * @param array $data, mang cac column va gia tri can thay doi
	 * @param string $table, ten table
	 * @param string $whereClause, mang dieu kieu
	 * @return string sql
	 */
	private function buildUpdateSql($table, $data, $whereClause) {
		$sql = 'UPDATE ' . $this->nameQuote($table) .' SET ';
		$modifiedColumns = array_keys($data);


		for($i = 1, $size = sizeof($modifiedColumns); $i <= $size; $i++) {
			$sql .= $this->nameQuote($modifiedColumns[$i-1]) . '= :p' .$i;

			if($i !== $size)
			{
				$sql .= ', ';
			}
		}

		$sql .= ' WHERE ' . $whereClause;
		return $sql;
	}

	/**
	 * Populate Stmt Values
	 *
	 * @param PDOStatement $stmt
	 * @param array $data mang chua du lieu
	 * @param array $pdoTye mang chua cac type
	 */
	private function populateStmtValues($data)
	{
		for ($i=0, $size = sizeof($data); $i<$size; ++$i)
		{
			if($data[$i] === null) {
				$this->stmt->bindParam('p:'. ($i + 1), null, PDO::PARAM_NULL);
			}
			else {
				$this->stmt->bindValue(':p' . ($i + 1), $data[$i]);
			}
		}
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
			
		$this->pdo->beginTransaction();
		return true;
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
			
		$this->pdo->commit();
		return true;
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
			
		$this->pdo->rollBack();
		return true;
	}

	/**
	 * Close
	 * close connection
	 *
	 * @return boolean
	 */
	public function close()
	{
		$this->pdo = null;

		return is_null($this->pdo);
	}

	/**
	 * Destructor
	 * close connection
	 *
	 */
	public function __destruct()
	{
		$this->pdo = null;
	}
}