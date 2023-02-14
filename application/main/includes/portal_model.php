<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
class PortalModel extends DatabaseObject
{
	public $tableName='portal';
	public $listField='id,name,description,alias';
	function __construct()
	{
		$this->setProperty('db','portal');
	}
	function getData()
	{
		$arrNewData=array(
			'name'			=> SystemIO::post('name','def',''),
			'description'	=> SystemIO::post('description','def',''),
			'alias'			=> SystemIO::post('alias','def','')
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
	function readData($id)
	{
		settype($id,'int');
		if(!$id) return array();
		$row=$this->selectOne($this->listField,$id);
		return $row;
	}
	function delData($id){
		return $this->delete($id);
	}
	function existPortal($name)
	{
		if(!$name) return 0;
		require_once   UTILS_PATH.'cache.file.php';
		if($is_cache){
			$Cache=new CacheFile();
			$row=$Cache->get(md5('portal'.$name),'',CACHE_FILE_PATH.'main'.DS,3600);
			if($row){
				return $row;
			}
		}
		$sql="SELECT COUNT(id) AS total FROM {$this->tableName} WHERE name='{$name}'";
		$this->query($sql);
		$row=$this->fetch('');
		if($is_cache){
			$Cache->set(md5('portal'.$name),$row,3600,'',CACHE_FILE_PATH.'main'.DS);
		}
		return (int)$row['total'];
	}

}

?>