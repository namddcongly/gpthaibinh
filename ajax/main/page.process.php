<?php
require_once(APPLICATION_PATH.'main/includes/page_module_model.php');
require_once(APPLICATION_PATH.'main/includes/page_model.php');

$page_id = SystemIO::post('page_id', 'int', 0 );
if($page_id > 0)
{
	$pageObj 		= new PageModel();

	$pagemoduleObj 	= new PageModuleModel();

	if($pageObj->deletePage($page_id) && $pagemoduleObj->deletePageModule(0, "page_id=$page_id"))
		echo 1;
	else
		echo 0;
}
else
	echo 0;
?>