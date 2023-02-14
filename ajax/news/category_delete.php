<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

require(APPLICATION_PATH.'news'.DS.'includes'.DS."category_model.php");

$cateObj = SystemIO::createObject('CategoryModel');

$id = SystemIO::get('id', 'int', 0);

$level = SystemIO::get('level', 'int', 3);

if($id > 0)
{
	$cond = ($level == 1 ? "cate_id1=".$id : "cate_id2=".$id); // kiem tra xem co danh mục con hay ko

	if($cateObj->exist($cond))
	echo json_encode(array("code" => 0, "html" => 'Danh mục này vẫn còn danh mục con. Xóa hết danh mục con rồi mới xóa danh mục cấp cha'));
	else
	{
		if($cateObj->deleteData($id))
		echo json_encode(array("code" => 1, "html" => 'Xóa thành công'));
		else
		echo json_encode(array("code" => 0, "html" => 'Xóa không thành công'));
	}
}
else
echo json_encode(array("code" => 0, "html" => 'Không tồn tại ID nhận dạng xóa'));
?>