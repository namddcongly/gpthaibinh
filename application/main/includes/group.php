<?php
class Group extends DatabaseObject
{

	public $listField='id,parent_id,name,description,total_user,time_created';
	public $tableName='group';
	function __construct($user=false)
	{
		$this->setProperty('db',$this->tableName);
	}
	function getData()
	{
		$arrNewData=array(
			'parent_id'		=>SystemIO::post('parent_id','int',0),
			'name'			=>SystemIO::post('name','def',''),
			'description'	=>SystemIO::post('description','def',''),
			'total_user'	=>0,
			'time_created'	=>time()
		);
		return $arrNewData;
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
	function readData($id,$cond='')
	{
		settype($id,'int');
		if(!$id && ($cond=='' || $cond==null)) return array();
		$row=$this->selectOne($this->listField,$id,$cond);
		return $row;
	}
	function delData($id){
		return $this->delete($id);
	}
}
?>