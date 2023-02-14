<?php
class Privilege extends DatabaseObject
{

	public $listField='id,name,description';
	public $tableName='user';
	function __construct($user=false)
	{
		$this->setProperty('db','privilege');
	}
	function getData()
	{
		$arrNewData=array(
			'name'			=>SystemIO::post('name','def',''),
			'description'	=>SystemIO::post('description','def',''),
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