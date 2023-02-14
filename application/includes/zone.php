<?php
// CLass Zone
require_once   UTILS_PATH.'cache.file.php';
class Zone extends DatabaseObject
{
	public $cachePath;

	function __construct()
	{
		if (!is_dir(CACHE_FILE_PATH.'zone/'))
		mkdir(CACHE_FILE_PATH.'zone/', 0777);
		$this -> cachePath = CACHE_FILE_PATH.'zone/';
		$this->databaseName = 'com';
	}
	/**
	 * Lay ve thong tin 1 tinh
	 *
	 * @param unknown_type $id
	 * @return unknown
	 */
	public function getOneProvince($id)
	{
		if($id > 0)
		{
			$zones = $this->getAllProvince();
			return $zones[$id];
		}
		return array();
	}

	/**
	 * Lay ve toan bo cac tinh
	 *
	 * @return array
	 */
	function getAllProvince()
	{
		$Cache=new CacheFile();
		$data=$Cache->get(md5('province'),'',$this->cachePath,36000);
		if($data)
		return $data;

		$this->tableName = 'province';
		$result = $this->select('id,name',null,null,null,'id');
		$Cache->set(md5('province'),$result,36000,'',$this->cachePath);
		return $result;
	}

	/**
	 * Lay danh sach cac tinh thanh dang option
	 *
	 * @param int $valueSelected
	 * @return string
	 */
	function getProvinceOption($valueSelected = 0)
	{
		$zoneOptions = SystemIO::arrayToOption($this->getAllProvince(), 'id', 'name');
		return SystemIO::getOption($zoneOptions, $valueSelected);
	}

	/**
	 * Xoa cache cua tỉnh
	 *
	 */
	function delCacheProvince(){
		$Cache=new CacheFile();
		$Cache->delete(md5('province'),'',$this->cachePath);
	}

	/**
	 * Lay ve cac huyen theo ma tinh
	 *
	 * @param int $provinceId
	 * @return array
	 */
	public function getDistrictByProvince($provinceId){
		$Cache=new CacheFile();
		$data=$Cache->get(md5('district'.$provinceId),'',$this->cachePath,36000);
		if($data)
		return $data;

		$this->tableName = 'district';
		$result = $this->select('id,name,province_id','province_id='.$provinceId,null,null,'id');
		$Cache->set(md5('district'.$provinceId),$result,36000,'',$this->cachePath);
		return $result;
	}

	/**
	 * Lay ve thong tin 1 huyen
	 *
	 * @param int $districtId ma huyen
	 * @param int $provinceId ma tinh
	 * @return array
	 */
	public function getOneDistrict($districtId, $provinceId = 0){
		if($id > 0)
		{
			if($provinceId){
				$zones = $this->getDistrictByProvince($provinceId);
				return $zones[$id];
			}
			else {
				$this->tableName = 'district';
				$result = $this->selectOne('id,name,province_id',$districtId);
				return $result;
			}
		}
		return array();
	}

	/**
	 * Lay danh sach cac huyen dang option
	 *
	 * @param int $valueSelected
	 * @return string
	 */
	function getDistrictOption($provinceId, $valueSelected = 0)
	{
		$zoneOptions = SystemIO::arrayToOption($this->getDistrictByProvince($provinceId), 'id', 'name');
		return SystemIO::getOption($zoneOptions, $valueSelected);
	}
}

?>