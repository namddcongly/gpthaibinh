<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class ModuleModel extends DatabaseObject
{
	function __construct()
	{
		$this->setProperty('db','module');
	}
	/**
	 * them module moi
	 *
	 * @param array $data
	 * @return bool
	 */
	function addModule($data)
	{
		$this->setNewData($data);
		return $this->insert();
	}
	/**
	 * lay thong tin cua module
	 *
	 * @param string $field
	 * @param string $condition
	 * @param string $order
	 * @param string $limit
	 * @param string $key
	 * @return array
	 */
	function getInfoModule($field = "*", $condition = "", $order = "", $limit = "", $key=null)
	{
		return $this->select($field, $condition, $order, $limit, $key);
	}
	/**
	 * lay cac module cua page
	 *
	 * @param int $page_id
	 * @param int $master_id
	 * @return array
	 */
	function getModuleOfPage($page_id, $master_id = 0,$portal="main")
	{
		if($master_id > 0)
		$condition = "a.id=b.module_id AND (b.page_id =".$page_id." OR b.page_id=".$master_id.")";
		else
		$condition = "a.id=b.module_id AND b.page_id =".$page_id;
			
		$sql = "SELECT a.id,a.name,a.path,a.portal_name,b.possition,b.arrange FROM module as a, page_module as b WHERE $condition ORDER BY b.arrange ASC";

		$result = $this->query($sql);

		return $this->fetchAll();
	}
	/**
	 * xoa module khi khong ton tai file vat ly
	 *
	 * @param int $id
	 * @param string $condition
	 * @return bool;
	 */
	function deleteModule($id = 0, $condition = "")
	{
		if($id == 0 && $condition == "")
		return false;
		else
		return $this->delete($id, $condition);
	}
}

?>