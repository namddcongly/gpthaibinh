<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once   UTILS_PATH.'cache.file.php';
class MenuModel extends DatabaseObject
{
	public $listField='id,name,info,url,privilege_name,parent_id,page_id,page_name,portal_name,level,position';
	public $tableName='system_menu';
	function __construct()
	{
		$this->setProperty('db',$this->tableName);
	}
	function getData()
	{
		$arrNewData=array(
			'name'				=> SystemIO::post('name','def',''),
			'info'				=> SystemIO::post('info','def',''),
			'url'				=> trim(SystemIO::post('url','def',''),'/'),
			'privilege_name'	=> SystemIO::post('privilege_name','def',''),
			'parent_id'			=> SystemIO::post('parent_id','def',''),
			'page_id'			=> SystemIO::post('page_id','def',''),
			'page_name'			=> SystemIO::post('page_name','def',''),
			'portal_name'		=> SystemIO::post('portal_name','def',''),
			'level'				=> SystemIO::post('level','def',0),
			'position'			=> SystemIO::post('position','def','')
		);
		return $arrNewData;
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
	function getList( $condition = "" , $order = "", $limit = "", $key = "id",$is_cache=true)
	{
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($condition.$order.$limit.$key.'menu'),'',CACHE_FILE_PATH.'menu'.DS,3600);
			if($data){
				return $data;
			}
		}
		$result=$this->select($this->listField, $condition, $order, $limit, $key);
		if($is_cache){
			$Cache->set(md5($condition.$order.$limit.$key.'menu'),$result,3600,'',CACHE_FILE_PATH.'menu'.DS);
		}
		return $result;


	}
	function readData($id,$cond='',$is_cache=true)
	{
		settype($id,'int');
		if(!$id && ($cond=='' || $cond==null)) return array();
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($id.$cond.'menu'),'',CACHE_FILE_PATH.'menu'.DS,3600);
			return $data;
		}
		$row=$this->selectOne($this->listField,$id,$cond);
		if($is_cache){
			$Cache->set(md5($id.$cond.'menu'),$row,3600,'',CACHE_FILE_PATH.'menu'.DS);
		}
		return $row;
	}

	function delData($id){
		return $this->delete($id);
	}
	function existMenu($name)
	{
		if(!$name) return 0;
		$sql="SELECT COUNT(*) AS total FROM {$this->tableName} WHERE name='{$name}'";
		$this->query($sql);
		$row=$this->fetch('');
		return (int)$row['total'];
	}
}

?>