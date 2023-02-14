<?php
/**
 *  Lop ket noi co so du lieu trong backend cua the thong tin tuc , thuc hien cac nghiep vu
 *	Class lam viec voi database su dung MySQLi
 *

 */
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once   ROOT_PATH.'system/kernel/includes/single.database.php';
require_once   UTILS_PATH.'cache.file.php';
Class AdministrationBaogiayNews
{
	private static $dbNews;
	function __construct()
	{
		//$config = array ('username' => 'vetinh', 'password' => 'V$^Tunh*(H23', 'host' => 'localhost','host_reserve'=>'localhost', 'dbname' => 'congly_news');
		$config = array ('username' => 'root', 'password' => 'DanGoiS@0ta', 'host' => 'localhost','host_reserve'=>'localhost', 'dbname' => 'congly_baogiay');
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
			$where="";
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
			$where="";
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
	public function countRecord($table,$where=null,$key='id')
	{
		return self::$dbNews->count($table,$where,$key);
	}

	
}
