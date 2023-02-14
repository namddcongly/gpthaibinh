<?php
ini_set('display_errors',1);
require_once 'application/news/backend/includes/backend.news.php';
require_once UTILS_PATH.'convert.php';
$newsObj=new BackendNews();
$action= SystemIO::post('action','def',0);
$id= SystemIO::post('id','int',0);
$title=htmlspecialchars(SystemIO::post('title'));
$description=htmlspecialchars(SystemIO::post('description'));

switch($action)
{
	case 'write_seo':
		$total=$newsObj->countRecord('store_seo','id='.$id);
		
		if($total)
		{
			if($newsObj=$newsObj->updateData('store_seo',array('title'=>$title,'description'=>$description),'id='.$id))
				echo 1;
			else
				echo 0;	
		}
		else{	
			if(strlen($title) > 15 && strlen($description) && $id)
				if($newsObj->insertSeo(array('id'=>$id,'title'=>$title,'description'=>$description))){
					echo 1;
				}
				else {
					echo 0;	
				}
			else
				echo 0;
		}
		break;
	case 'add_link':
		$link=SystemIO::post('link','def','');
		$check = $newsObj->countRecord('link_exchange',"nw_id=".$id);
		if($check)
		{
			if($newsObj=$newsObj->updateData('link_exchange',array('link'=>$link),'nw_id='.$id))
				echo 1;
			else 
				echo 0;	
		}
		else{
			$id = $newsObj->insertData('link_exchange',array('nw_id'=>$id,'link'=>$link,'time_created'=>time()));
			if($id)
				echo 1;
			else 
				echo 0;
		}		
			
		break;		
}