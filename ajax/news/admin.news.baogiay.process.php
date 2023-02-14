<?php
//ini_set('display_errors',1);
require_once 'application/news/backend/includes/baogiay.news.php';
$newsObj= new BaogiayNews();
$action=SystemIO::post('action','def','');
$news_id= SystemIO::post('nw_id','int',0);
$year=(int)date('Y',time());
switch($action)
{
	case 'delete-from-store':
		if($newsObj->delData('baogiay','id='.$news_id))
			echo 1;
		else
			echo 0;	
		break;
	case 'update-page':
		$page=SystemIO::post('page','int');
		if($newsObj->updateData('baogiay',array('page'=>$page),'id='.$news_id))
			echo 1;
		else
			echo 0;	
		break;
	case 'add-cate':
		$cate_name=SystemIO::post('cate_name','int',0);
		$c=$newsObj->getListData('cate_baogiay','id','name='.$cate_name.' AND time_created = '.$year);
		if(count($c))
		{
			echo 1;
			die;
		}
		if($cate_name)
			if($newsObj->insertData('cate_baogiay',array('name'=>$cate_name,'time_created'=>$year)))	
				echo 1;
			else
				echo 0;
		else
			echo 0;			
			
		break;				
}

