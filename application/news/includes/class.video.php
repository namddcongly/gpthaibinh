<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class ClassVideo extends DatabaseObject
{
	public $tableName='admin_video';
	public $listField='id,title,description,cate_id,video_name,image_name,time_created,property';
	public $arrProperty = array('1' => 'Hiển thị','16'=>'Hiển thị trang chủ');
	function __construct()
	{
		$this->setProperty('news', $this->tableName);
	}

	function insertData($data)
	{
		$this->setNewData($data);
		return $this->insert();
	}
	function getArrayProperty(){
		return $this->arrProperty;
	}
	public function updateProperty($wh,$set_property,$unset_property=0)
	{
		if(!$wh) return false;
		settype($set_property,'int');
		settype($unset_property,'int');
		$sql="UPDATE {$this->tableName} SET property= (property | {$set_property})&~{$unset_property} WHERE {$wh}";
		//echo $sql;
		return $this->query($sql);
	}
	function updateData($data, $id = 0 , $condition = "")
	{
		$this->setNewData($data);
		return $this->update($id, $condition);
	}

	function getList( $condition = "" , $order = "", $limit = "", $key = "")
	{
		return $this->select($this->listField, $condition, $order, $limit, $key);
	}

	public function getListAdmin($wh='',$order='',$limit='0,20',$key='')
	{
		global $TOTAL_ROWCOUNT;
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
	function getPath($time_created){
		if($_SERVER['HTTP_HOST']=='xahoi.com.vn' || $_SERVER['HTTP_HOST']=='www.xahoi.com.vn')
				return 'http://image.xahoi.com.vn/video/'.date('Y/n/',$time_created);
		return ROOT_URL.'data/video/'.date('Y/n/',$time_created);
	}
	function getLink($time, $name){
		$url = '';
		if($name){
			$url = $this->getPath($time).$name;
		}
		return $url;
	}

}