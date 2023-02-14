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
Class CrawlNews
{
	private static $dbCrawl;
	public static $review_id;
	function __construct()
	{
		
		$config = array ('username' => 'duluan', 'password' => 'DUx@H#Lu#n&', 'host' => '192.168.0.4','host_reserve'=>'localhost', 'dbname' => 'duluan_crawl');
		self::$dbCrawl=new SingleDatabase($config);
	}
	public function getListData($table,$field="*",$wh='',$order='',$limit='0,20',$key='')
	{
		global $TOTAL_ROWCOUNT;
		$where='';
		if($wh)
		$where="WHERE {$wh}";
		if($order)
			$sql="SELECT SQL_CALC_FOUND_ROWS {$field} FROM {$table} {$where} ORDER BY {$order} LIMIT {$limit}";
		else
			$sql="SELECT SQL_CALC_FOUND_ROWS {$field} FROM {$table} {$where} LIMIT {$limit}";
            
		self::$dbCrawl->query($sql);
		$result=self::$dbCrawl->fetchAll($key);
		self::$dbCrawl->query("SELECT FOUND_ROWS() AS total_rows");
		$row=self::$dbCrawl->fetch();
		$TOTAL_ROWCOUNT=(int)$row['total_rows'];
		return $result;
	}
	public function getOneData($id)
	{
		settype($id,'int');
		if(!$id) return array();
		$field='title,cate_id1,cate_id2,description,content,image,date_created,tag,status,id,link,html';
		return self::$dbCrawl->selectOne('items',$field,'id='.$id);
	}
	public function countRecord($table,$where=null,$key='id')
	{
		return self::$dbCrawl->count($table,$where,$key);
	}
	/**
	 * path anh crawl ve
	 * @param unknown_type $date_created
	 */
	public function getPathImgCrawl($date_created)
	{
		return 'http://duluan.com.vn/data/crawl/'.date('Y/d-m/',$date_created).(date('H',$date_created)%24).'/';
	}
	public function writeFile($data,$path=LOG_PATH,$mode = 'a+'){
		if ( ! $fp = @fopen($path, $mode)){
			return false;
		}
		flock($fp, LOCK_EX);
		fwrite($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);
		return true;
	}
	public function log($action,$id,$ext='')
	{
		$user_info=UserCurrent::$current->data;
		if($ext=='')
		$data=$user_info['user_name'].' - '.$action.'. Bài có id: '.$id.' lúc: '.date('H:i:s d/n/Y',time()).'<br/>';
		else
		$data=$user_info['user_name'].' - '.$action.'. Bài có id: '.$id.' ('.$ext.') lúc: '.date('H:i:s d/n/Y',time()).'<br/>';
		$this->writeFile($data,LOG_PATH.'action.log.html');
	}

}