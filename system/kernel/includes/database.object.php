<?php
/*
 * Lop Object base thuc hien cac thao tac len database
 */
class DatabaseObject {
	/** Mang chua du lieu moi duoc lay duoc tu he thong */
	protected  $arrNewData = array();
	/** Mang chua du lieu cu dung de so sanh su thay doi du lieu  trong qua trinh update*/
	protected  $arrOldData = array();

	/** Co check xem co yeu cau check su thay doi du lieu truoc khi update hay khong (true la phai check, fail khong can check)*/
	public  $isCheckModify = false;
	public $tableName = '';
	public $databaseName = '';
	// mang chua cac con tro db duoc config
	protected static $arrDbConf = array('db' => 1,'news'=>1, 'xahoi' => 1,'raovat'=>1,'bds'=>1,'com'=>1);
	/**
	 * Ham khoi tao
	 **/
	public function __construct()
	{

	}
	/**
	 * Ham unset cac gia tri cua property
	 *
	 */
	public function unsetProperty()
	{
		$this -> tableName = '';
		$this -> databaseName = '';
		$this -> arrNewData = array();
		$this -> arrOldData = array();
	}
	/**
	 * Ham set cac gia tri cho cac property
	 *
	 * @param string $dbName - con tro database
	 * @param string $tableName - ten bang
	 */
	public function setProperty($dbName = '', $tableName = '')
	{
		if($dbName != '')
		$this -> databaseName = $dbName;
		if($tableName != '')
		$this -> tableName = $tableName;
	}
	/**
	 * Ham Insert
	 *
	 * @return int
	 */
	public function insert() {
		// loi khong ton tai con tro db hoac khong ton tai bang
		$this -> error();
		$dbName = $this -> databaseName;
		if(!is_array($this -> arrNewData) or count($this -> arrNewData) == 0)
		throw new SystemException('Không tồn tại mảng dữ liệu để insert');
		$id = $dbName() -> insert($this -> tableName, $this -> arrNewData);
		return $id;
	}
	/**
	 * Insert Duplicate
	 * @author Han Van Loi
	 */
	public function insertDuplicate($queryUpdate  = '') {
		// loi khong ton tai con tro db hoac khong ton tai bang
		//ON DUPLICATE KEY UPDATE
		$this -> error();
		$dbName = $this -> databaseName;
		if(!is_array($this -> arrNewData) or count($this -> arrNewData) == 0)
		throw new SystemException('Không tồn tại mảng dữ liệu để insert');

		$res = $dbName() -> insertDuplicate($this -> tableName, $this -> arrNewData,$queryUpdate );
		return $res;
	}
	/**
	 * Insert Duplicate
	 * @author Han Van Loi
	 */
	public function insertIgnore() {
		// loi khong ton tai con tro db hoac khong ton tai bang
		//ON DUPLICATE KEY UPDATE
		$this -> error();
		$dbName = $this -> databaseName;
		if(!is_array($this -> arrNewData) or count($this -> arrNewData) == 0)
		throw new SystemException('Không tồn tại mảng dữ liệu để insert');

		$res = $dbName() -> insertIgnore($this -> tableName, $this -> arrNewData );
		return $res;
	}
	/**
	 * Ham insert nhieu ban ghi cung luc
	 *
	 * @return int
	 */
	public  function insertMutile()
	{
		$this -> error();
		if(!is_array($this -> arrNewData) or count($this -> arrNewData) == 0)
		throw new SystemException('Không tồn tại mảng dữ liệu để insert');
		$dbName = $this -> databaseName;
		$lasId = $dbName() -> insertMulti($this -> tableName, $this -> arrNewData);
		return $lasId;
	}
	/**
	 * Ham update
	 *
	 * @param $id - ma id cua bang ghi
	 * @param dieu kien update
	 * @return boolean
	 */
	public function  update($id = 0, $condition = '') {
		// TODO: implement
		$this -> error();
		$id = (int) $id;
		if($id === 0 and $condition === '')
		throw new SystemException('Không tồn tại điều kiện update!');
		elseif($id !== 0 and $condition === '')
		$condition = ' id = '.$id;
		elseif($id !== 0 and $condition !== '')
		$condition .= ' and id = '.$id;
		$update = false;

		if($this -> isCheckModify)
		{
			$this -> checkDataChange($id, $condition);
		}
		elseif(sizeof($this -> arrNewData) === 0 or !is_array($this -> arrNewData))
		throw new SystemException('Không tồn tại mảng dữ liệu update');
		if(sizeof($this -> arrNewData) > 0)
		{
			$dbName = $this -> databaseName;
			$update = $dbName() -> update($this -> tableName, $this -> arrNewData, $condition);
		}
		else
		{
			$update = true;
		}
		return $update;
	}

	/**
	 * Ham xoa
	 *
	 * @param int $id - ma ban ban ghi
	 * @param string $condition - dieu kien xoa
	 * @return boolean
	 */
	public function delete($id = 0, $condition = '') {
		// TODO: implement
		$delete = false;
		$this -> error();
		$id = (int) $id;
		$dbName = $this -> databaseName;
		if($id === 0 and $condition === '')
		throw new SystemException('Không tồn tại điều kiện xóa!');
		elseif($id !== 0 and $condition === '')
		$condition = ' id = '.$id;
		elseif($id !== 0 and $condition !== '')
		$condition .= ' and id = '.$id;
		$delete = $dbName() -> delete($this -> tableName, $condition);
		return $delete;
	}

	/**
	 * Ham lay mot ban ghi
	 *
	 * @param string $listField - danh sach cac truong can lay du lieu (truyen * la lay toan bo)
	 * @param int $id - Ma ban ghi default = 0
	 * @param string $condition - dieu kien select default = ''
	 * @return array()
	 */
	public function selectOne($listField, $id = 0, $condition = '') {
		// TODO: implement
		$this -> error();
		$id = (int) $id;
		$dbName = $this -> databaseName;
		if($id === 0 and $condition === '')
		throw new SystemException('Không tồn tại điều kiện select!');
		elseif($id !== 0 and $condition === '')
		$condition_ec = ' id = '.$id;
		elseif($id !== 0 and $condition !== '')
		$condition_ec = "id = $id and $condition";
		elseif ($id === 0 and $condition !== '')
		$condition_ec = $condition;
		$row = $dbName() -> selectOne($this -> tableName,$listField, $condition_ec);
		return $row;
	}

	/**
	 * Ham lay nhieu ban ghi cung mot luc
	 *
	 * @param string $listField - danh sach cac truong lay du lieu cach nha boi dau phay
	 * @param string $condition - dieu kien lay du lieu
	 * @param string $order - dieu kien sap xep du lieu default = ''
	 * @param string $limit
	 * @param string $key - chi dinh truong lay du lieu lam key cho array du lieu tra ve
	 * @return array()
	 */
	public function select($listField, $condition=null, $order = '', $limit = '', $key = '') {
		// TODO: implement
		$this -> error();
		$dbName = $this -> databaseName;
		
		if($listField !== '')
		$arrRow = $dbName() -> select($this -> tableName,$listField, $condition, $order, $limit, $key);
		else
		throw new SystemException('Không tồn tại điều danh sách các trường lấy dữ liệu');
		return $arrRow;
	}

	/**
	 * Ham truy van cau lenh sql
	 *
	 * @param string $sql - cau lenh sql cau truy van
	 * @return query id
	 */
	public function query($sql) {
		// TODO: implement
		//$this -> error();
		if($sql == '')
		throw new SystemException('Không tồn tại câu lệnh sql');
		else
		{
			$dbName = $this -> databaseName;
			$query = $dbName() -> query($sql);
			return $query;
		}
	}
	/**
	 * Ham fetch all tao bo du lieu da truy van duuoc
	 *
	 * @param string $key
	 * @return array
	 */
	public function fetchAll($key = '')
	{
		//$this -> error();
		$dbName = $this -> databaseName;
		return $dbName() -> fetchAll($key);
	}
	/**
	 * Ham fetch mot ban ghi
	 *
	 * @return array
	 */
	public  function fetch()
	{
		//$this -> error();
		$dbName = $this -> databaseName;
		return $dbName() -> fetch();
	}
	/** Ham check su thay doi du lieu cu va du lieu moi truoc khi update */
	public function checkDataChange($id, $condition) {
		// TODO: implement
		// mang tam thoi luu gia tri can update
		$arrTmp = array();
		// mang du lieu moi nhan duoc
		$arrNew = $this -> arrNewData;
		// mang du lieu cu
		$arrOld = $this -> arrOldData;
		if(count($arrNew) > 0)
		{
			if(count($arrOld) == 0 or !is_array($arrOld))
			{
				// lay danh sach cac truong du lieu de lay lai du lieu cu
				$listField = '';
				foreach($arrNew as $key => $value)
				{
					if($listField == '')
					$listField = $key;
					else
					$listField .= ','.$key;
				}
				$listField = $listField;
				// lay du lieu
				$arrOld = $this -> selectOne($listField, $id, $condition);
			}
			foreach($arrNew as $key => $value)
			{
				if($value !== $arrOld[$key])
				$arrTmp[$key] = $value;
			}
		}
		else
		throw new SystemException('Không tồn tại mảng để so sánh!');
		// gan mang can update bang mang da duoc kiem tra
		$this -> arrNewData = $arrTmp;
	}

	/**
	 * Ham set du lieu moi can inset hoac update
	 * @access 	public
	 * @param 	$arrNewData - Mang lua gia tri can set
	 **/
	public function setNewData($arrNewData) {
		// TODO: implement
		$this -> arrNewData = $arrNewData;
	}

	/**
	 * Ham set du lieu cu muon sua
	 * @access 	public
	 * @param 	$arrOldData - Mang lua gia tri can set    *
	 **/
	public function setOldData($arrOldData) {
		// TODO: implement
		$this -> arrOldData = $arrOldData;
	}
	/**
	 * Ham den tong so ban ghi
	 *
	 * @param string $condition - dieu kien count du lieu
	 * @return int
	 */
	public  function count($condition = null, $field = '*')
	{
		$this -> error();
		$dbName = $this -> databaseName;
		$tbName = $this -> tableName;
		return $dbName() -> count($tbName, $condition,$field);
	}
	/**
	 * Ham check loi khong ton tai dbName va tableName
	 *
	 * @return true - neu khong gap loi
	 */
	protected function error()
	{		
		if(self::$arrDbConf[$this -> databaseName] !== 1)
		throw new SystemException('Không tồn tại con trỏ database!');
		elseif($this -> tableName ==='')
		throw new SystemException('Không tồn tại tên bảng!');
		else
		return true;
	}

	public function getSql(){
		$dbName = $this -> databaseName;
		return  $dbName()->getSql();
	}
	/**
	 * Ham huy toan bo propery cua object
	 *
	 */
	public function  __destruct()
	{
		unset($this -> arrNewData);
		unset($this -> arrOldData);
		unset($this -> isCheckModify);
		unset($this -> tableName);
		unset($this -> databaseName);
			
	}
}
?>