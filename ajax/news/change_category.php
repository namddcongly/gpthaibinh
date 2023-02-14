<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

require(APPLICATION_PATH.'news'.DS.'includes'.DS."category_model.php");

$cateObj = new CategoryModel();

$id 	= SystemIO::get('id'	, 'int', 0);
$level 	= SystemIO::get('level'	, 'int', 1);
$cmd 	= SystemIO::get('cmd'	, 'int', 0);
if($id > 0)
{
	$categories = $cateObj->getList("cate_id$level=".$id.(($level+1) > 5 ? "" : " AND cate_id".($level+1)."=0"), "arrange asc");
	$select = "";

	if(is_array($categories) && count($categories) > 0)
	$select .= '<option value="0">Chọn danh mục cấp '.($level+1).'</option>'.SystemIO::selectBox($categories, array(0),"id","id","name");
	else {echo json_encode(array("code" => 1, "html" => ''));}
	//$select .= '<option value="0">Chọn danh mục cấp '.($level+1).'</option>';

	if($cmd > 0)
	$select = '<select name="data[cate_id'.($level+1).']" id="cate'.($level+1).'">'.$select.'</select>';

	echo json_encode(array("code" => 1, "html" => $select));
}
else
echo json_encode(array("code" => 1, "html" => ''));
?>