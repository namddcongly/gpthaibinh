<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

require(APPLICATION_PATH.'news'.DS.'includes'.DS."category_model.php");

$cateObj = SystemIO::createObject('CategoryModel');

$id 	 = SystemIO::get('id', 'int', 0);

$value 	 = SystemIO::get('value', 'int', 0);

$level 	 = SystemIO::get('level', 'int', 3);

$cmd 	 = SystemIO::get('cmd', 'int', 0);

if($id > 0)
{
	$sql = ($cmd == 0 ? "property=property&~$value" : "property=property|$value");

	if($cateObj->updateBits( $sql, "id=".$id))
	{
		if($value == 0 )
		{
			if($level == 1) $cateObj->updateBits($sql, "cate_id1=".$id);
			if($level == 2) $cateObj->updateBits($sql, "cate_id2=".$id);
		}
		echo json_encode(array("code" => 1, "html" => 'Cập nhật thành công'));
	}
	else
	echo json_encode(array("code" => 1, "html" => 'Cập nhật không thành công'));
}
else
echo json_encode(array("code" => 0, "html" => 'Không tồn tại ID để cập nhật'));
?>