<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class EntertainConfigModel extends DatabaseObject 
{
	public $tableName='entertainment';
		
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
	
	function getList($field, $condition = "" , $order = "", $limit = "", $key = "id")
	{
		return $this->select($field, $condition, $order, $limit, $key);
	}
	
	function deleteData($id,$cond="")
	{
		return $this->delete($id, $cond);
	}
	
	function EnterOne($field,$id = 0, $condition = "")
	{
		if($id == 0 && $condition == "")
			return null;
		return $this->selectOne($field,$id, $condition);
	}
	
	function exist($condition)
	{
		$count = $this->count($condition);
		return $count > 0 ? true : false;
	}
	
}
?>