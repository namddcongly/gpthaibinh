<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
class CategoryPropertyModel extends DatabaseObject
{
	public $tableName='category_property';
	public $listField='id,name,value,type,groups,styles,alias';

	function __construct()
	{
		$this->setProperty('news', $this->tableName);
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

	function deleteData($id)
	{
		return $this->delete($id);
	}
	function PropertyOne($id = 0, $condition = "")
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
	function groupProperty()
	{
		$properties = $this->getList("", "id asc");

		$prop = array();
		if(count($properties) > 0)
		{
			foreach ($properties as $p)
			{
				if($p['groups'] == 0)
				{
					$prop[$p['id']]['curl'] = $p;

					foreach ($properties as $pr)
					if($pr['groups'] == $p['id'])
					$prop[$p['id']]['items'][] = $pr;
				}
			}
		}

		return $prop;
	}
}
?>