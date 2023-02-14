<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class QuestionOfUser extends DatabaseObject
{
	public $tableName='question_of_user';
	public $listField='id,title,content,user_name,email,time_public,time_created';

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

	function readData($id = 0, $condition = "")
	{
		if($id == 0 && $condition == "")
		return null;
		return $this->selectOne($this->listField,$id, $condition);
	}

}
?>