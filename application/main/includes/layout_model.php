<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once   UTILS_PATH.'cache.file.php';
class LayoutModel extends DatabaseObject
{
	function __construct()
	{
		$this->setProperty('db','layout');
	}

	function addLayout($data)
	{
		$this->setNewData($data);
		return $this->insert();
	}
	function updateLayout($data, $id =0 , $condition="")
	{
		$this->setNewData($data);
		return $this->update($id, $condition);
	}
	function getLayout($field = "*", $condition = "" , $order = "", $limit = "", $key = "id")
	{
		if(isset($is_cache)){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($condition.$order.$limit.$key.'layout'),'',CACHE_FILE_PATH.'main'.DS,3600);
			if($data)
			return $data;
			else
			return $this->select($field, $condition, $order, $limit, $key);
		}
		$result = $this->select($field, $condition, $order, $limit, $key);

		if(isset($is_cache)){
			$Cache->set(md5($condition.$order.$limit.$key.'layout'),$result,3600,'',CACHE_FILE_PATH.'main'.DS);
		}
		return $result;
	}
	function getOneLayout($field,$id = 0, $condition = "",$is_cache=true)
	{
		if($condition == "" && $id == 0) return;

		if(isset($is_cache)){
			$Cache=new CacheFile();
			$data=$Cache->get(md5($id.$condition.'layout'),'',CACHE_FILE_PATH.'main'.DS,3600);
			if($data)
			return $data;
			else
			return $this->selectOne($field, $id, $condition);
		}
		$result = $this->selectOne($field, $id, $condition);
		if(isset($is_cache)){
			$Cache->set(md5($id.$condition.'layout'),$result,3600,'',CACHE_FILE_PATH.'main'.DS);
		}
		return $result;

	}
	function existLayout($layout_name, $layouts)
	{
		$layout_name = str_replace(".php", "", $layout_name);

		if(is_array($layouts) && count($layouts) > 0)
		foreach ($layouts as $layout)
		if($layout['name'] == $layout_name)
		return true;
		return false;
		//return $this->count("name='$layout_name'");
	}
	/**
	 * xoa layout khi khong ton tai file vat ly tren server
	 *
	 * @param int $id
	 * @param string $condition
	 * @return bool;
	 */
	function deleteLayout($id = 0, $condition = "")
	{
		if($id == 0 && $condition == "")
		return false;
		else
		return $this->delete($id, $condition);
	}
}

?>