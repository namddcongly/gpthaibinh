<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
class Shopping extends DatabaseObject 
{
	public $tableName='store_shopping';
	public $listField='id,cate_id,title,img,content,tag,address,province_id,province_name,district_id,district_name,phone,email,website,price,status,user_id,censor_id,editor_id,author,origin,time_public,time_created';
	
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
		global $TOTAL_ROWCOUNT;
		$where='';
		if($wh)
		$where="WHERE {$wh}";
		if($order)
			$sql="SELECT SQL_CALC_FOUND_ROWS {$this->listField} FROM {$this->tableName} {$where} ORDER BY {$order} LIMIT {$limit}";
		else
			$sql="SELECT SQL_CALC_FOUND_ROWS {$this->listField} FROM {$this->tableName} {$where} LIMIT {$limit}";
		$this->query($sql);
		$result=$this->fetchAll($key);
		$this->query("SELECT FOUND_ROWS() AS total_rows");
		$row=$this->fetch();
		$TOTAL_ROWCOUNT=(int)$row['total_rows'];
		return $result;
	}
	
	function deleteData($id)
	{
		return $this->delete($id);
	}
	function readData($id = 0, $condition = "")
	{
		settype($id,'int');
		if($id == 0 && $condition == "")
			return null;
		return $this->selectOne($this->listField,$id, $condition);
	}
	function getPath($cate_ids='',$ids='')
	{
		if(!($cate_ids || $ids)) return false;
		
		
	}
	
	
	function exist($condition)
	{
		$count = $this->count($condition,'*');
		return $count > 0 ? true : false;
	}
}
?>