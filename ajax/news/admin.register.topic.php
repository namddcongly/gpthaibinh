<?php
//ini_set("display_errors", 1);
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/includes/category_model.php';
require_once UTILS_PATH.'convert.php';
$action=SystemIO::post('action','def',1);
if($action==1) return false;
$id= SystemIO::post('id','int',0);
$user_info=UserCurrent::$current->data;
$newsObj=new BackendNews();
switch($action)
{

	case 'censor':

		$censor = SystemIO::post('censor');
		$arr = array('censor_id'=>$user_info['id'],'date_censor'=>date('Y-m-d H:i:s'),'status'=>1,'censor_noties' => $censor);
		if($newsObj->updateData('register_topic',$arr,'id='.$id))
		{
			echo 1;
		}
		else
			echo 0;
		break;
	case 'delete':

		if($newsObj->delData('register_topic','id='.$id))
		{
			echo 1;
		}
		else
			echo 0;

		break;
	case 'no-censor':
		$reason = SystemIO::post('reason','def');
		$arr = array('censor_id'=>$user_info['id'],'date_censor'=>date('Y-m-d H:i:s'),'status'=>0,'reason_no_censor'=>$reason);
		if($newsObj->updateData('register_topic',$arr,'id='.$id))
		{
			echo 1;
		}
		else
			echo 0;
		break;



}


