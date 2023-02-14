<?php
require_once 'application/news/includes/class.video.php';
$action=SystemIO::post('action','def','');
$id= SystemIO::post('id','int',0);
$user_info=UserCurrent::$current->data;
$obj=new ClassVideo();
switch($action)
{
	case 'delete':
		if($obj->deleteData($id))
		echo 1;
		else
		echo 0;
		break;

	case 'set_property'	:
		$set_property=SystemIO::post('set_property','int');
		$unset_property=SystemIO::post('unset_property','int');
		if($obj->updateProperty("id={$id}",$set_property,$unset_property))
		echo 1;
		else
		echo 0;
		//echo 'abc';
		break;

}