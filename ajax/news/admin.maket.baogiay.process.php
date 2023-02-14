<?php
//ini_set('display_errors',1);
require_once 'application/news/backend/includes/administration.baogiay.news.php';
$newsObj= new AdministrationBaogiayNews();
$action=SystemIO::post('action','def','');
$news_id= SystemIO::post('nw_id','int',0);
switch($action)
{
	case 'delete-from-store':
		if($newsObj->delData('maket','id='.$news_id))
			echo 1;
		else
			echo 0;	
		break;
	case 'update-page':
		$page=SystemIO::post('page','int');
		if($newsObj->updateData('maket',array('page'=>$page),'id='.$news_id))
			echo 1;
		else
			echo 0;	
		break;
	case 'property':
		$property=SystemIO::post('property','int');
		if($newsObj->updateData('maket',array('property'=>$property),'id='.$news_id))
			echo 1;
		else
			echo 0;	
		break;	
	case 'add-cate':
		$cate_name=SystemIO::post('cate_name','int',0);
		$c=$newsObj->getListData('cate','id','name='.$cate_name);
		if(count($c))
		{
			echo 1;
			die;
		}
		if($cate_name)
			if($newsObj->insertData('cate',array('name'=>$cate_name,'time_created'=>time())))	
				echo 1;
			else
				echo 0;
		else
			echo 0;			
			
		break;				
}

