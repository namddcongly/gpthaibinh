<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

require(APPLICATION_PATH.'news'.DS.'includes'.DS."region_model.php");

require(APPLICATION_PATH.'news'.DS.'includes'.DS."category_region_model.php");

require(APPLICATION_PATH.'news'.DS.'includes'.DS."region_store_model.php");

$regionObj 	 	 = SystemIO::createObject('RegionModel');

$cateRegionObj 	 = SystemIO::createObject('CategoryRegionModel');

$newsRegionObj 	 = SystemIO::createObject('RegionStoreModel');

$region_id 	 = SystemIO::get('region_id', 'int', 0);

$cate_id 	 = SystemIO::get('cate_id'	, 'int', 0);

$value 	 	 = SystemIO::get('value'	, 'int', 0);

if($region_id > 0 && $cate_id > 0)
{
	$newsRegionObj->deleteData(0,"region_id=".$region_id);

	$count = $cateRegionObj->exist("region_id=".$region_id);
	if($count > 1)
	{
		if($cateRegionObj->deleteData(0,"region_id=".$region_id." AND cate_id=".$cate_id))
		echo json_encode(array("code" => 1, "html" => 'Xóa thành công'));
		else
		echo json_encode(array("code" => 0, "html" => 'Xóa không thành công'));
	}
	else
	{
		if($count == 1 && $cateRegionObj->deleteData(0,"region_id=".$region_id." AND cate_id=".$cate_id) && $regionObj->deleteData($region_id))
		echo json_encode(array("code" => 1, "html" => 'Xóa thành công'));
		else
		echo json_encode(array("code" => 0, "html" => 'Xóa không thành công'));
	}
}
else
echo json_encode(array("code" => 0, "html" => 'Không tồn tại ID để cập nhật'));
?>