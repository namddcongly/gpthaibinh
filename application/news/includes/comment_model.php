<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class CommentModel extends DatabaseObject 
{
	public $tableName='comments';
	public $listField='full_name,email,content,time_post';
	
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
	
	function getList( $condition = "" , $order = "", $limit = "", $key = "id",$paging=true)
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
	
	function deleteData($id,$cond="")
	{
		return $this->delete($id, $cond);
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
	
}
?>