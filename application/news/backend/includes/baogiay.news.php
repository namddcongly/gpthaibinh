<?php
/**
 *  Lop ket noi co so du lieu trong backend cua the thong tin tuc , thuc hien cac nghiep vu
 *	Class lam viec voi database su dung MySQLi
 *

 */
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once   ROOT_PATH.'system/kernel/includes/single.database.php';
require_once   UTILS_PATH.'cache.file.php';
require_once   UTILS_PATH.'convert.php';
Class BaogiayNews
{
	private static $dbNews;
	function __construct()
	{
		$config = array ('username' => 'congly', 'password' => 'qTKCMLdxzx7mjX7G', 'host' => 'localhost','host_reserve'=>'localhost', 'dbname' => 'congly_news');
		self::$dbNews=new SingleDatabase($config);
	}
	/*
	 * Cong tac vien insert vao bang review
	 * */

    public function insertData($table,$data)
	{
		return self::$dbNews->insert($table,$data);
	}
	public function updateData($table,$data,$where)
	{
		return self::$dbNews->update($table,$data,$where);
	}
	/**
	 *
	 * @param unknown_type $table
	 * @param unknown_type $wh
	 * @param unknown_type $limit
	 * Can update lai cache
	 */
	public function delData($table,$wh,$limit=1)
	{
		return self::$dbNews->delete($table,$wh,$limit);
	}
	/**
	 * Get danh muc
	 * @param $w
	 * @param $order
	 * @param $limit
	 * @param $key
	 */
	
	public function querySql($sql)
	{
		return self::$dbNews->query($sql);
	}
	public function getListData($table,$listField,$wh='',$order='',$limit='0,20',$key='',$paging=true)
	{
		if($paging){
			global $TOTAL_ROWCOUNT;
			if($wh)
				$where="WHERE {$wh}";
			if($order)
				$sql="SELECT SQL_CALC_FOUND_ROWS {$listField} FROM {$table} {$where} ORDER BY {$order} LIMIT {$limit}";
			else
			$sql="SELECT SQL_CALC_FOUND_ROWS {$listField} FROM {$table} {$where} LIMIT {$limit}";
			self::$dbNews->query($sql);
			$result=self::$dbNews->fetchAll($key);
			self::$dbNews->query("SELECT FOUND_ROWS() AS total_rows");
			$row=self::$dbNews->fetch();
			$TOTAL_ROWCOUNT=(int)$row['total_rows'];
		}
		else
		{
			if($wh)
				$where="WHERE {$wh}";
			if($order)
				$sql="SELECT {$listField} FROM {$table} {$where} ORDER BY {$order} LIMIT {$limit}";
			else
				$sql="SELECT {$listField} FROM {$table} {$where} LIMIT {$limit}";
			self::$dbNews->query($sql);
			$result=self::$dbNews->fetchAll($key);
		}
		return $result;
	}

	
}
