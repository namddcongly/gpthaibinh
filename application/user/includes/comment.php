<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once   ROOT_PATH.'system/kernel/includes/single.database.php';
require_once   UTILS_PATH.'cache.file.php';

class Comment extends DatabaseObject 
{
	public $tableName='comment';
	public $listField='id, nw_id, full_name, email, content, time_post, ip_address, status, user_name';
	private static $dbNews;
	function __construct()
	{
		$this->setProperty('news', $this->tableName);
	}	
	
	function insertData($data)
	{
		$this->setNewData($data);
		return $this->insert();
	}
	public function updateData($table,$data,$where)
	{
		return $this->update($table,$data,$where);
	}	
	function getList( $wh = "" , $order = "", $limit = "", $key = "")
	{
		global $TOTAL_ROWCOUNT;
		$where='';
		if($wh)
			$where="WHERE {$wh}";
        else
            $where = '';    
		if($order)	
			$sql="SELECT SQL_CALC_FOUND_ROWS {$this->listField} FROM $this->tableName {$where} ORDER BY {$order} ";
		else
			$sql="SELECT SQL_CALC_FOUND_ROWS {$this->listField} FROM $this->tableName {$where}";
        if($limit)
            $sql .= "LIMIT $limit";  
		$this->query($sql);
		$result=$this->fetchAll($key);
		$this->query("SELECT FOUND_ROWS() AS total_rows");
		$row=$this->fetch();
		$TOTAL_ROWCOUNT=(int)$row['total_rows'];
		return $result;	
		
	}
	
	function getNewsTitle($nw_ids='')
	{
		$result = array();
		$this->setProperty('news', $this->tableName);
		if ($nw_ids)
			$sql = "SELECT id,title FROM store WHERE id IN ($nw_ids)";
		$this->query($sql);
		$arr_title= array();
		$result=$this->fetchAll();
		foreach ($result as $row)
		{
			$arr_title[$row['id']] = $row['title'];
		}
		$this->setProperty('com', $this->tableName);
		return	$arr_title;
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
	
	function censorMultiData($cmt_data,$cmt_ids)
	{
		$sql="UPDATE $this->tableName SET time_public='".$cmt_data['time_public']."',censor_id='".$cmt_data['censor_id']."'  WHERE id in ($cmt_ids)";
		
		return $this->query($sql);
	} 
	function deleteMultiData($cmt_ids)
	{
		$sql="DELETE FROM $this->tableName WHERE id in ($cmt_ids)";
		return $this->query($sql);
		
	}
}