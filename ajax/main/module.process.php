<?php
require_once 'application/main/includes/module_model.php';
require_once 'application/main/includes/page_module_model.php';
$objModule=new ModuleModel();
$objPageModule=new PageModuleModel();
$id=SystemIO::post('id','int',0);
$user_info=UserCurrent::$current->data;
if($user_info['user_name']=='namdd'){	
	if($id > 0)
	{
		if($objModule->deleteModule($id)){
			$objPageModule->deletePageModule(0,'module_id='.$id);
			echo 1;
		}	
		else 
			echo 0;	
	}
}
else
	echo 0;