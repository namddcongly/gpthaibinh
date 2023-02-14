<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class ContactOfUser extends DatabaseObject
{
	public $tableName='contact_of_user';
	public $listField='id,title,content,user_name,email,time_public,time_created';

	function __construct()
	{
		$this->setProperty('com', $this->tableName);
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

	function getList( $condition = "" , $order = "", $limit = "", $key = "",$paging=false)
	{
		global $TOTAL_ROWCOUNT;
		if($paging){
			if($order)
			$sql="SELECT SQL_CALC_FOUND_ROWS {$this->listField} FROM ".$this->tableName." WHERE {$condition} ORDER BY {$order} LIMIT {$limit}";
			else
			$sql="SELECT SQL_CALC_FOUND_ROWS {$this->listField} FROM ".$this->tableName." WHERE {$condition} LIMIT {$limit}";
			$this->query($sql);
			$result=$this->fetchAll($key);
			$this->query("SELECT FOUND_ROWS() AS total_rows");
			$rows=$this->fetchAll('');
			$TOTAL_ROWCOUNT=(int)$rows[0]['total_rows'];
		}else{
			$result = $this->select($this->listField,$condition,$order,$limit,$key);
			$TOTAL_ROWCOUNT=count($result);
		}
		return $result;
	}
	function readData($id,$cond='')
	{
		settype($id,'int');
		if(!$id && ($cond=='' || $cond==null)) return array();
		$row=$this->selectOne($this->listField,$id,$cond);
		return $row;
	}
	function deleteData($id)
	{
		return $this->delete($id);
	}
}