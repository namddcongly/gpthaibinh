<?php

require(APPLICATION_PATH.'news'.DS.'includes'.DS."category_region_model.php");
require(APPLICATION_PATH.'news'.DS.'includes'.DS."region_model.php");

$regioncateObj = new CategoryRegionModel();
$regionObj = new RegionModel();
		
$cate_id 	 = SystemIO::get('cate_id', 'int', 0);

if($cate_id > 0)
{	
	$region_ids = $regioncateObj->getSelected("region_id", "cate_id=$cate_id");
	if(count($region_ids) > 0)
	{
		$reg_id = "0";
		foreach ($region_ids as $ri)		
			$reg_id .= ",".$ri["region_id"];
		$regions = $regionObj->getList("id IN($reg_id)");
		
		if(count($regions))
		{
			$option = '<option value="0">Chọn Vùng</option>';
			foreach ($regions as $r)	
				$option .= '<option value="'.$r["id"].'">'.$r['name'].'</option>';
			
			echo json_encode(array("code" => 1, "html" => $option));
		}
	}
}
else 
	echo json_encode(array("code" => 0, "html" => 'Không thỏa mãn điều kiện'));
?>