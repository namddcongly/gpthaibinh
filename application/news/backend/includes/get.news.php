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
Class GetNews
{
	private static $dbNews;
	function __construct($user_name,$pass,$host,$db_name)
	{
		$config = array ('username' =>$user_name, 'password' =>$pass, 'host' =>$host,'host_reserve'=>'localhost', 'dbname' =>$db_name);
		self::$dbNews=new SingleDatabase($config);
	}
	public function getStoreOne($news_id)
	{
		settype($news_id,'int');
		if(!$news_id) return array();
		return self::$dbNews->selectOne('store','*','id='.$news_id);
	}
	/**
	 * Lấy nôi dung chi tiết của một bản tin
	 * @param $news_id
	 */
	public function getContentOne($news_id)
	{
		settype($news_id,'int');
		$row = self::$dbNews->selectOne('store_content','nw_id,content','nw_id='.$news_id);
		return $row['content'];
	}
	
	public function getListContent($news_ids)
	{
		if(!$news_ids) return array();
		return self::$dbNews->select('store_content','nw_id,content','nw_id IN("'.$news_ids.'")',null,null,'nw_id');
	} 
	/**
	 * Lấy danh sách các bản tin trong store
	 * @param $wh
	 * @param $order
	 * @param $limit
	 * @param $key
	 * @param $pagging
	 */
	public function getListStore($wh='',$order='',$limit='0,20',$key='')
	{
		$listField='*';
		global $TOTAL_ROWCOUNT;
		$where='';
		if($wh)
		$where="WHERE {$wh}";
		if($order)
		$sql="SELECT SQL_CALC_FOUND_ROWS {$listField} FROM store {$where} ORDER BY {$order} LIMIT {$limit}";
		else
		$sql="SELECT SQL_CALC_FOUND_ROWS {$listField} FROM store {$where} LIMIT {$limit}";
		self::$dbNews->query($sql);
		$result=self::$dbNews->fetchAll($key);
		self::$dbNews->query("SELECT FOUND_ROWS() AS total_rows");
		$row=self::$dbNews->fetch();
		$TOTAL_ROWCOUNT=(int)$row['total_rows'];
		return $result;
	}
	public function getListReview($wh='',$order='',$limit='0,20',$key='')
	{
		$listField='*';
		global $TOTAL_ROWCOUNT;
		$where='';
		if($wh)
		$where="WHERE {$wh}";
		if($order)
		$sql="SELECT SQL_CALC_FOUND_ROWS {$listField} FROM review {$where} ORDER BY {$order} LIMIT {$limit}";
		else
		$sql="SELECT SQL_CALC_FOUND_ROWS {$listField} FROM review {$where} LIMIT {$limit}";
		self::$dbNews->query($sql);
		$result=self::$dbNews->fetchAll($key);
		self::$dbNews->query("SELECT FOUND_ROWS() AS total_rows");
		$row=self::$dbNews->fetch();
		$TOTAL_ROWCOUNT=(int)$row['total_rows'];
		return $result;
	}
	/**
	 * Lay danh sach cac tin o bang khac nhau
	 * @param unknown_type $table
	 * @param unknown_type $listField
	 * @param unknown_type $wh
	 * @param unknown_type $order
	 * @param unknown_type $limit
	 * @param unknown_type $key
	 */
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
	function getListHit($table,$listField,$wh='',$order=null,$limit='0,30',$key = 'id')
	{
		return self::$dbNews->select($table, $listField, $wh,$order,$limit,$key);
	}
	/**
	 * Lấy danh sách các bản tin trong review
	 * @param $wh
	 * @param $order
	 * @param $limit
	 * @param $key
	 * @param $pagging
	 */
	public function getListView($wh='',$order='',$limit='0,20',$key='')
	{
		$listField='id,nw_id,cate_id,cate_path,title,description,tag,img1,img2,img3,img4,img5,property,time_public,time_created';
		global $TOTAL_ROWCOUNT;
		if($wh)
		$where="WHERE {$wh}";
		if($order)
		$sql="SELECT SQL_CALC_FOUND_ROWS {$listField} FROM store_view {$where} ORDER BY {$order} LIMIT {$limit}";
		else
		$sql="SELECT SQL_CALC_FOUND_ROWS {$listField} FROM store_view {$where} LIMIT {$limit}";
		self::$dbNews->query($sql);
		$result=self::$dbNews->fetchAll($key);
		self::$dbNews->query("SELECT FOUND_ROWS() AS total_rows");
		$row=self::$dbNews->fetch();
		$TOTAL_ROWCOUNT=(int)$row['total_rows'];
		return $result;
	}
	
	/**
	 * Lấy danh sách các bản tin home
	 * @param $wh
	 * @param $order
	 * @param $limit
	 * @param $key
	 * @param $pagging
	 */
	public function getListNewsHit($news_ids)
	{
		if(!$news_ids) return false;
		return self::$dbNews->select('store_hit','hit,nw_id',"nw_id IN ({$news_ids})",null,null,'nw_id');
	}
	/**
	 * Lấy đường dẫn của một tin khi biết id tin tưc hoặc nhóm tin
	 * @param unknown_type $cate_id
	 * @param unknown_type $news_id
	 */
	public function getOnePathNews($cate_id=0,$news_id=0)
	{
		settype($cate_id,'int');
		settype($news_id,'int');
		if(!($cate_id || $news_id)) return false;
		if($cate_id)
		{
			$cate=self::$dbNews->selectOne('category','name,cate_id1,cate_id2,cate_id3,cate_id4,cate_name1,cate_name2,cate_name3,cate_name4','id='.$cate_id);
			if($cate['cate_id4'])
			return $cate['cate_name1'].'<font color="#000000">&raquo;</font>'.$cate['cate_name2'].'<font color="#000000">&raquo;</font>'.$cate['cate_name3'].'<font color="#000000">&raquo;</font>'.$cate['cate_name4'].'<font color="#000000">&raquo;</font>'.$cate['name'];
			elseif($cate['cate_id3'])
			return $cate['name'].' <font color="#000000">&raquo;</font> '.$cate['cate_name1'].' <font color="#000000">&raquo;</font> '.$cate['cate_name2'].' <font color="#000000">&raquo;</font> '.$cate['cate_name3'].' <font color="#000000">&raquo;</font> '.$cate['name'];
			elseif($cate['cate_id2'])
			return $cate['name'].' <font color="#000000">&raquo;</font> '.$cate['cate_name1'].' <font color="#000000"> &raquo;</font> '.$cate['cate_name2'].' <font color="#000000">&raquo;</font> '.$cate['name'];
			elseif($cate['cate_id1'])
			return  $cate['cate_name1'].' &raquo; '.$cate['name'];
			else
			return $cate['name'];
		}
		else
		{
			$news=self::$dbNews->selectOne('store','id,cate_path','id='.$news_id);
			$cate_ids=trim($news['cate_path'],',');
			if(!$cate_ids) return false;
			$cate=self::$dbNews->select('category','name,id',"id IN ($cate_ids)");
			$path='';
			foreach($cate as $_temp)
			{
				$path.=$_temp['name'].'<font color="#000000">&raquo;</font>';
			}
			return trim($path,'<font color="#000000">&raquo;</font>');
		}
	}
	/**
	 * Lấy nhiều đường dẫn khi biết một danh sách các danh muc hoac mot danh sach cac id
	 * @param $cate_ids
	 * @param $news_ids
	 */
	public function getMultiPathNews($cate_ids='',$news_ids='')
	{

		if(!($cate_ids || $news_ids)) return false;
		$path=array();
		if($cate_ids)
		{
			$cates=self::$dbNews->select('category','id,name,cate_id1,cate_id2,cate_id3,cate_id4,cate_name1,cate_name2,cate_name3,cate_name4','id IN ('.$cate_ids.')');

			foreach($cates as $cate)
			{
				if($cate['cate_id4'])
				$path[$cate['id']]=$cate['cate_name1'].' <font color="#000000">&raquo;</font> '.$cate['cate_name2'].' <font color="#000000">&raquo;</font> '.$cate['cate_name3'].' <font color="#000000">&raquo;</font> '.$cate['cate_name4'].' <font color="#000000">&raquo;</font> '.$cate['name'];
				elseif($cate['cate_id3'])
				$path[$cate['id']]=$cate['cate_name1'].' <font color="#000000">&raquo;</font> '.$cate['cate_name2'].' <font color="#000000">&raquo;</font> '.$cate['cate_name3'].' <font color="#000000">&raquo;</font> '.$cate['name'];
				elseif($cate['cate_id2'])
				$path[$cate['id']] = $cate['cate_name1'].' <font color="#000000">&raquo;</font> '.$cate['cate_name2'].' <font color="#000000">&raquo;</font> '.$cate['name'];
				elseif($cate['cate_id1'])
				$path[$cate['id']]= $cate['cate_name1'].'<font color="#000000">&raquo;</font> <a title="'.$cate['id'].'">'.$cate['name'].'</a>';
				else
				$path[$cate['id']]= $cate['name'];
			}
		}
		else
		{
			$list_news=self::$dbNews->select('news','id,cate_path,cate_id','id IN('.$news_ids.')','','','id');
			$arr_cate_id_news_id=array();
			foreach($list_news as $_temp)
			{
				$arr_cate_id_news_id[$_temp['id']]= trim($_temp['cate_path'],',');
			}
			return $path;
		}
		return $path;
	}	
	/**
	 * Set thuộc tính của bản tin
	 * @param $table
	 * @param $wh
	 * @param $set_property
	 * @param $unset_property
	 * Can update lai cache
	 */
	public function updateData($table,$data,$where)
	{
		$this->log('Sửa bài trong bảng '.$table,$where);
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
		$this->log('Xóa bài trong bảng '.$table,$wh);
		return self::$dbNews->delete($table,$wh,$limit);
	}
	/**
	 * Get danh muc
	 * @param $w
	 * @param $order
	 * @param $limit
	 * @param $key
	 */
	public function getListCategory($wh='',$order='',$limit='0,200',$key='',$is_cache=true)
	{
		$listField='id,name,name_display,cate_id1,cate_id2,cate_id3,cate_id4,cate_name1,cate_name2,cate_name3,cate_name4,alias,title,keyword,description,arrange,level,property,icon,layout,block_home,order_cate,number';
		if($wh)
		$wh=$wh." AND property & 1=1";
		else
		$wh='property & 1 = 1';
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($wh.'category'.$limit),'',CACHE_FILE_PATH.'news'.DS,300);
			if($data){
				return $data;
			}
		}
		if($order)
			$sql="SELECT {$listField} FROM category ".($wh != "" ? "WHERE ".$wh : "")." ORDER BY {$order} LIMIT {$limit}";
		else
			$sql="SELECT {$listField} FROM category ".($wh != "" ? "WHERE ".$wh : "")." LIMIT {$limit}";
		self::$dbNews->query($sql);
		$result=self::$dbNews->fetchAll($key);
		if($is_cache){
			$Cache->set(md5($wh.'category'.$limit),$result,300,'',CACHE_FILE_PATH.'news'.DS);
		}
		return $result;
	}
	public function querySql($sql)
	{
		return self::$dbNews->query($sql);
	}
	public function countRecord($table,$where=null,$key='id')
	{
		return self::$dbNews->count($table,$where,$key);
	}
	
	public function getPathNews($time,$folder='cnn_496x279/')
	{
		return $folder.date('Y/n/j',$time).'/';
	}
	/**
	 * Xoa mot ban ghi o kho
	 * @param unknown_type $id
	 * Can update lai cache
	 */
	public function deleteStore($id)
	{
		$this->log('Xóa bài trong kho',$id);
		self::$dbNews->delete('store_content','nw_id='.$id,1);
		self::$dbNews->delete('store_hit','nw_id='.$id,1);
		self::$dbNews->delete('search','nw_id='.$id,1);
		self::$dbNews->delete('store','id='.$id,1);
		return true;
	}
	/**
	 * Set bai tren trang chu tu bang store
	 * @param unknown_type $id
	 * @param unknown_type $property
	 * Can update lai cache
	 */
	
	function getNews($table,$list_field,$wh=null,$order=null,$limit=null,$key=null,$is_cache=false)
	{
		
		if($table=='store')
		{
			if(!$wh) $wh='time_public < '.time();
			else $wh.=" AND time_public < ".time();				
		}
		
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($wh.$table),'',CACHE_FILE_PATH.'news'.DS,300);
			if($data){
				return $data;
			}
		}
		$result=self::$dbNews->select($table,$list_field,$wh,$order,$limit,$key);
		if($is_cache){
			$Cache->set(md5($wh.$table),$result,300,'',CACHE_FILE_PATH.'news'.DS);
		}
		return $result;	
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