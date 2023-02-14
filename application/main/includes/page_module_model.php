<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class PageModuleModel extends DatabaseObject
{
	function __construct()
	{
		$this->setProperty('db','page_module');
	}

	function addModule($data)
	{
		$this->setNewData($data);
		return $this->insert();
	}
	function getModulePage($field,$condition="",$order="", $limit="")
	{
		return $this->select($field, $condition, $order, $limit);
	}
	function getPossition($condition)
	{
		$sql = "SELECT max( arrange ) AS `arrage` FROM `page_module` WHERE ".$condition;

		$result = $this->query($sql);
		return $this->fetch();
	}

	function updatePageModule($data, $id, $condition = "")
	{
		$this->setNewData($data);
		return $this->update($id, $condition);
	}

	function deletePageModule($id = 0, $condition = "")
	{
		return $this->delete($id, $condition);
	}

}

?>