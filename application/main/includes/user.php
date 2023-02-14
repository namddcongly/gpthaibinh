<?php
class User extends DatabaseObject
{
	public static $current=false;
	public $data = false;
	public $listField='id,group_id,user_name,full_name,password,email,avatar,zone_name,gender,phone,mobile_phone,address,zone_id,active,total_article,article_type,last_login,CMTND,birthday,nick_skype,nick_yahoo,time_last_login,time_register';
	public $tableName='user';
	function __construct($user=false)
	{
		$this->setProperty('db','user');
	}
	function getData()
	{
		$arrNewData=array(
			'group_id'			=> SystemIO::post('group_id','def',''),
			'user_name'			=> SystemIO::post('user_name','def',''),
			'full_name'			=> SystemIO::post('full_name','def',''),
			'password'			=> SystemIO::post('password','def',''),
			'email'				=> SystemIO::post('email','def',''),
			'avatar'			=> SystemIO::post('avatar','def',''),
			'gender'			=> SystemIO::post('gender','def',''),
			'phone'				=> SystemIO::post('phone','def',''),
			'mobile_phone'		=> SystemIO::post('mobile_phone','def',''),
			'address'			=> SystemIO::post('address','def',''),
			'zone_id'			=> SystemIO::post('zone_id','def',''),
			'active'			=> SystemIO::post('active','def',''),
			'total_article'		=> SystemIO::post('total_article','def',''),
			'article_type'		=> SystemIO::post('article_type','def',''),
			'last_login'		=> SystemIO::post('last_login','def',''),
			'CMTND'				=> SystemIO::post('CMTND','def',''),
			'birthday'			=> SystemIO::post('birthday','def',''),
			'nick_skyper'		=> SystemIO::post('nick_skyper','def',''),
			'nick_yahoo'		=> SystemIO::post('nick_yahoo','def',''),
			'time_last_login'	=> SystemIO::post('time_last_login','def',''),
			'zone_name'			=> SystemIO::post('zone_name','def',''),
			'time_register'		=> time()
		);
	}
	function insertData($data)
	{
		$this->setNewData($data);
		return $this->insert();
	}
	function updateData($data, $id =0 , $condition="")
	{
		$this->setNewData($data);
		return $this->update($id, $condition);
	}
	function getList( $condition = "" , $order = "", $limit = "", $key = "id")
	{

		return $this->select($this->listField, $condition, $order, $limit, $key);


	}
	function readData($id,$cond='')
	{
		settype($id,'int');
		if(!$id && ($cond=='' || $cond==null)) return array();
		$row=$this->selectOne($this->listField,$id,$cond);
		return $row;
	}
	function delData($id){
		return $this->delete($id);
	}
	/**
		Chuyển Id thành user name
		@param $id
	 */
	public function userIdToName($ids)
	{
		if(!$ids) return array();
		$result= $this->select('id,user_name',"id IN ($ids)",'','','id');
		return $result;
	}
	public function userIdToNameAll()
	{

		$result= $this->select('id,user_name','','','','id');
		return $result;
	}
	public function userIdName($is_cache=true)// lấy toàn bộ user được active
	{
		require_once   UTILS_PATH.'cache.file.php';
		if($is_cache){
			$Cache=new CacheFile();
			$result=$Cache->get(md5('user_id_name'),'',CACHE_FILE_PATH.'user'.DS,3600);
			if($result){
				return $result;
			}
		}
		$result= $this->select('id,user_name','active=1','','','id');
		if($is_cache){
			$Cache->set(md5('user_id_name'),$result,3600,'',CACHE_FILE_PATH.'user'.DS);
		}
		return $result;
	}
	/**
	 * Chuyển tên thành Id
	 * @param unknown_type $list_name
	 */
	public function userNameToId($list_name)
	{
		if(!$list_name) return array();
		$list_name=str_replace(',',"','",$list_name);
		$list_name="'".$list_name."'";
		$result= $this->select('id,user_name',"user_name IN ($list_name)",'','','user_name');
		return $result;

	}
}
?>