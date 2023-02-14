<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
class ConvertModel extends DatabaseObject 
{
	public $tableName='convert_store';
	public $listField='*';
	
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
	
	function getList( $condition = "" , $order = "", $limit = "", $key = "id")
	{
		return $this->select($this->listField, $condition, $order, $limit, $key);
	}
	
	function deleteData($id)
	{
		return $this->delete($id);
	}
	
	function RegionOne($id = 0, $condition = "")
	{
		if($id == 0 && $condition == "")
			return null;
		return $this->selectOne($this->listField,$id, $condition);
	}
	
	function exist($condition)
	{
		$count = $this->count($condition);
		return $count > 0 ? true : false;
	}
	function total($cond)
	{
		return $this->count($cond);
	}
	function updateBits($value, $condition = "")
	{
		$sql = "UPDATE ".$this->tableName." SET ".$value.($condition != "" ? " WHERE ".$condition : "");
	
		return $this->query($sql);		
	}
	function query($sql)
	{
		return $this->query($sql);
	}
}





