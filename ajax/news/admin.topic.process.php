<?php
require_once 'application/news/backend/includes/backend.news.php';
$newsObj=new BackendNews();
$action=SystemIO::post('action','def');
$id=SystemIO::post('id','int');
switch($action)
{
	case 'update-property':
		
		$property=SystemIO::post('property','int');
		if($newsObj->updateData('topic',array('property'=>$property),'id='.$id))
			echo 1;
		else
			echo 0;	
		break;
	case 'delete':
		if($newsObj->delData('topic','id='.$id))
			echo 1;
		else
			echo 0;
		break;		
	case 'add':		
		$name=SystemIO::post('name','def');
		$sql="INSERT INTO topic (name,property,time_created) VALUES('".$name."','1',".time().")";
		if($newsObj->querySql($sql))
			echo 1;
		else
			echo 0;	
		break;				
}

