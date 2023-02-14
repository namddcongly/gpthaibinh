<?php
class GroupPrivilege extends DatabaseObject
{

	public $listField='id,group_id,privilege_id';
	public $tableName='group_privilege';
	function __construct($user=false)
	{
		$this->setProperty('db','group_privilege');
	}
	function getData()
	{
		$arrNewData=array(
			'group_id'			=>SystemIO::post('group_id','def',''),
			'privilege_id'	=>SystemIO::post('privilege_id','def',''),
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
	function delMultiData($cond)
	{
		if($cond=='' || $cond ==null )
		return false;
		$sql="DELETE FROM {$this->tableName} WHERE {$cond}"	;
		return $this->query($sql);
	}

}
?>