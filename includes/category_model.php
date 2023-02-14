<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class CategoryModel extends DatabaseObject
{
	public $tableName='category';
	public $listField='id,name,name_display,cate_id1,cate_id2,cate_id3,cate_id4,cate_name1,cate_name2,cate_name3,cate_name4,title,keyword,description,alias,arrange,level,property,icon,number,order_cate,layout,block_home';

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

	function getList( $condition = "" , $order = "arrange asc", $limit = "", $key = "id")
	{
		return $this->select($this->listField, $condition, $order, $limit, $key);
	}

	function deleteData($id)
	{
		return $this->delete($id);
	}

	function CategoryOne($id = 0, $condition = "")
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
	function updateBits($value, $condition = "")
	{
		$sql = "UPDATE ".$this->tableName." SET ".$value.($condition != "" ? " WHERE ".$condition : "");

		return $this->query($sql);
	}
}
?>