<?php
require_once(APPLICATION_PATH.'main/includes/page_module_model.php');
require_once(APPLICATION_PATH.'main/includes/page_model.php');
require_once(UTILS_PATH.'cache.file.php');

$cmd = SystemIO::get('cmd', 'str', "");

$page_id = SystemIO::get('page_id', 'int', 0);

$module_id = SystemIO::get('module_id', '', "");

if($cmd != "" && $page_id > 0 && $module_id != "")
{
	$packObj = new PackageProcess();

	switch ($cmd)
	{
		case "all":
			$packObj->all($page_id, $module_id);
			break;
		case "one":
			$packObj->one($page_id, $module_id);
			break;
		case "up":
			$packObj->up($page_id, $module_id);
			break;
		case "down":
			$packObj->down($page_id, $module_id);
			break;
		default:
			break;
	}

	$pageObj = new PageModel();
	$page = $pageObj->getOnePage("name,portal_name", $page_id);
	@unlink(CACHE_PATH.'system'.DS.$page['portal_name'].DS.$page['name'].'.php');
	CacheFile::requestUrl('?portal='.$page['portal_name'].'&page='.$page['name'].'&cached=1');

}
else
echo json_encode(array("code" => 0,"html"=>"Không có Module nào được xóa"));



class PackageProcess
{
	function __construct()
	{

	}
	function all($page_id, $module_id)
	{
		$pagemoduleObj = new PageModuleModel();

		if($pagemoduleObj->deletePageModule(0, "page_id=".$page_id." AND module_id IN(".$module_id.")"))
		echo json_encode(array("code" => 1, "html" => "Xóa thành công"));
		else
		echo json_encode(array("code" => 0, "html" => "Xóa không thành công"));
	}
	function one($page_id, $module_id)
	{
		$pagemoduleObj = new PageModuleModel();

		if($pagemoduleObj->deletePageModule(0, "page_id=".$page_id." AND module_id=".$module_id))
		echo json_encode(array("code" => 1, "html" => "Xóa thành công"));
		else
		echo json_encode(array("code" => 0, "html" => "Xóa không thành công"));
	}
	function up($page_id, $module_id)
	{
		$region 	= SystemIO::get('region'	, 'str', "");
		$arrange 	= SystemIO::get('arrange'	, 'int', 0);

		$pagemoduleObj = new PageModuleModel();

		$modules = $pagemoduleObj->getModulePage("*", "page_id=".$page_id." AND possition='".$region."' AND arrange < ".$arrange." AND module_id != ".$module_id, "arrange ASC");

		if(count($modules) > 0 && is_array($modules))
		{
			$temp = $modules[count($modules) - 1];
			$check = $pagemoduleObj->updatePageModule(array("arrange" => $arrange), $temp['id']);
			if($check && $pagemoduleObj->updatePageModule(array("arrange" => $temp['arrange']), 0, "page_id=".$page_id." AND possition='".$region."' AND module_id = ".$module_id))
			echo json_encode(array("code" => 1, "html" => "Cập nhật thành công"));
			else
			echo json_encode(array("code" => 0, "html" => "Cập nhật không thành công"));
		}
		else
		echo json_encode(array("code" => 0, "html" => "Cập nhật không thành công"));
	}
	function down($page_id, $module_id)
	{
		$region 	= SystemIO::get('region'	, 'str', "");
		$arrange 	= SystemIO::get('arrange'	, 'int', 0);

		$pagemoduleObj = new PageModuleModel();

		$modules = $pagemoduleObj->getModulePage("*", "page_id=".$page_id." AND possition='".$region."' AND arrange > ".$arrange." AND module_id != ".$module_id, "arrange ASC");

		if(count($modules) > 0 && is_array($modules))
		{
			$temp = $modules[count($modules) - 1];
			$check = $pagemoduleObj->updatePageModule(array("arrange" => $arrange), $temp['id']);
			if($check && $pagemoduleObj->updatePageModule(array("arrange" => $temp['arrange']), 0, "page_id=".$page_id." AND possition='".$region."' AND module_id = ".$module_id))
			echo json_encode(array("code" => 1, "html" => "Cập nhật thành công"));
			else
			echo json_encode(array("code" => 0, "html" => "Cập nhật không thành công"));
		}
		else
		echo json_encode(array("code" => 0, "html" => "Cập nhật không thành công"));
	}
}
?>