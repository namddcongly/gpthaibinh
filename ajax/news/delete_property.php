<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

require(APPLICATION_PATH.'news'.DS.'includes'.DS."category_property_model.php");

$cateObj = new CategoryPropertyModel();

$id = SystemIO::get('id', 'int', 0);

if($id > 0)
{
	if($cateObj->exist("groups=".$id))
	echo json_encode(array("code" => 0, "html" => "Trong nhóm vẫn còn các thuộc tính. Hãy xóa hết các thuộc tính trước khi xóa group"));
	else
	if($cateObj->deleteData($id))
	echo json_encode(array("code" => 1, "html" => "Xóa thành công"));
	else
	echo json_encode(array("code" => 0, "html" => "Có lỗi xảy ra khi xóa"));
}
else
echo json_encode(array("code" => 0, "html" => "Trang không hợp lệ"));
?>