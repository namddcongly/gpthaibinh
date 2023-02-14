<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
class CategoryShopping extends DatabaseObject 
{
	public $tableName='category_shopping';
	public $listField='id,name,parent_id,arrange,property';
	
	function __construct()
	{
		$this->setProperty('news', $this->tableName);
	}	
	
	function insertData($data)
	{
		$this->setNewData($data);
		return $this->insert();
	}
	
	function updateData($data, $id = 0 , $condition = "")
	{
		$this->setNewData($data);
		return $this->update($id, $condition);
	}
	
	function getList( $wh = "" , $order = "", $limit = "", $key = "id")
	{
		if($is_cache){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($wh.'category'),'',CACHE_FILE_PATH.'news'.DS,300);
			if($data){
				return $data;
			}
		}
		$result=$this->select($this->listField, $wh, $order, $limit, $key);
		if($is_cache){
			$Cache->set(md5($wh.'category'),$result,300,'',CACHE_FILE_PATH.'news'.DS);
		}
		return $result;
	}
	
	function deleteData($id)
	{
		return $this->delete($id);
	}
	function readData($id = 0, $condition = "")
	{
		if($id == 0 && $condition == "")
			return null;
		return $this->selectOne($this->listField,$id, $condition);
	}
	
	function exist($condition)
	{
		$count = $this->count($condition,'*');
		return $count > 0 ? true : false;
	}
}
?>