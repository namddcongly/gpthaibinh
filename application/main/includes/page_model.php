<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class PageModel extends DatabaseObject
{
	public $tableName='page';
	function __construct()
	{
		$this->setProperty('db',$this->tableName);
	}

	function addPage($data)
	{
		$this->setNewData($data);
		return $this->insert();
	}
	function getPage($field = "*", $condition = "" , $order = "id desc", $limit = "", $key = "id")
	{
		return $this->select($field, $condition, $order, $limit, $key);
	}
	function getList($field = "*", $condition = "" , $order = "", $limit = "", $key = "id")
	{
		return $this->select($field, $condition, $order, $limit, $key);
	}
	function getOnePage($field, $id = 0, $condition = "")
	{
		if($condition == "" && $id == 0) return;
		return $this->selectOne($field, $id, $condition);
	}
	function updatePage($data, $page_id, $condition = "")
	{
		$this->setNewData($data);
		return $this->update($page_id, $condition);
	}
	function deletePage($id = 0, $condition = "")
	{
		return $this->delete($id, $condition);
	}
	function existPage($condition = "",$is_cache=true)
	{
		
		if($condition=="") return 0;
		require_once   UTILS_PATH.'cache.file.php';
		if($is_cache){
			$Cache=new CacheFile();
			$row=$Cache->get(md5('page'.$condition),'',CACHE_FILE_PATH.'page'.DS,3600);
			if($row['total'] > 0){
				return true;
			}
		}
		$sql="SELECT COUNT(id) AS total FROM {$this->tableName} WHERE {$condition}";
		$this->query($sql);
		$row=$this->fetch('');
		if($is_cache){
			$Cache->set(md5('page'.$condition),$row,3600,'',CACHE_FILE_PATH.'page'.DS);
		}
		if($row['total'] > 0) return true; 	
		return false;
	}
}

?>