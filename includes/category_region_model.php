<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
class CategoryRegionModel extends DatabaseObject
{
	public $tableName	= 'region_category';
	public $tableRegion	= 'region';

	public $listField='cate_id,region_id,skins_type,number_record,arrange';

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
		$sql = "SELECT SQL_CALC_FOUND_ROWS a.id,a.cate_id,a.region_id,a.skins_type,a.number_record,a.property,a.arrange,b.name,b.description FROM ".$this->tableName." as a, ".$this->tableRegion." as b WHERE a.region_id=b.id ".($condition != "" ? " AND ".$condition : "").($limit != "" ? " LIMIT ".$limit : "");

		$this->query($sql);
		$result = $this->fetchAll();

		global $TOTAL;

		$this->query("SELECT FOUND_ROWS() AS total_rows");
		$rows = $this->fetchAll('');

		$TOTAL	= (int)$rows[0]['total_rows'];

		return $result;
	}
	function getSelected($field, $condition = "" , $order = "", $limit = "", $key = "id")
	{
		return $this->select($field, $condition, $order, $limit, $key);
	}

	function deleteData($id, $condition="")
	{
		return $this->delete($id,$condition);
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
		return $count;
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