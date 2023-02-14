<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

require(APPLICATION_PATH.'news'.DS.'includes'.DS."category_region_model.php");

$regionObj = SystemIO::createObject('CategoryRegionModel');

$region_id 	 = SystemIO::get('region_id', 'int', 0);

$cate_id 	 = SystemIO::get('cate_id', 'int', 0);

$value 	 = SystemIO::get('value', 'int', 0);

if($region_id > 0 && $cate_id > 0)
{
	if($regionObj->updateData( array("property" => $value), 0, "region_id=".$region_id." AND cate_id=".$cate_id))
	echo json_encode(array("code" => 1, "html" => 'Cập nhật thành công'));
	else
	echo json_encode(array("code" => 0, "html" => 'Cập nhật không thành công'));
}
else
echo json_encode(array("code" => 0, "html" => 'Không tồn tại ID để cập nhật'));
?>