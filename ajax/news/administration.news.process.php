<?php
require_once 'application/news/backend/includes/administration.baogiay.news.php';
$action=SystemIO::post('action','def','');
$news_id= SystemIO::post('nw_id','int',0);
$user_info=UserCurrent::$current->data;
$newsObj= new AdministrationBaogiayNews();
switch($action)
{
	case 'pv_post_news': // PV day bai len cho  ban bien tap
		if($news_id){
			if($newsObj->updateData('store',array('status'=>2,'time_post'=>time()),'id='.$news_id)){
				
				echo 1;
			}
			else echo 0;
		}	
		break;
	case 'bt_post_news': // tkbt day bai len cho  truong ban bien tap
		if($news_id){
			if($newsObj->updateData('store',array('status'=>3,'time_post'=>time()),'id='.$news_id)){
				
				echo 1;
			}
			else 
				echo 0;
		}	
		break;

	case 'tbbt_post_news': // tbbt day bai cho thu ky bien tap
		if($news_id){
			if($newsObj->updateData('store',array('status'=>4,'time_post'=>time()),'id='.$news_id)){
				
				echo 1;
			}
			else 
				echo 0;
		}	
		break;
	case 'tkbt_post_news': // tkbt day bai cho truong ban thu ky
		if($news_id){
			if($newsObj->updateData('store',array('status'=>5,'time_post'=>time()),'id='.$news_id)){
				
				echo 1;
			}
			else 
				echo 0;
		}	
		break;
	case 'tbtk_post_news': // truong ban thu ky post bai vao kho du lieu cho tong bt duyet hoac tu su
		if($news_id){
			if($newsObj->updateData('store',array('status'=>6,'time_post'=>time(),'censor_id'=>$user_info['id']),'id='.$news_id)){
				
				echo 1;
			}
			else 
				echo 0;
		}	
		break;
	case 'tongbientap_censor': //
		if($news_id){
			if($newsObj->updateData('store',array('status'=>7,'time_post'=>time(),'censor_id'=>$user_info['id']),'id='.$news_id)){
				
				echo 1;
			}
			else 
				echo 0;
		}	
		break;
	case 'news_return':
		$reason=SystemIO::post('reason','def');
		$reason=str_replace(array('"','"'),array("",""),$reason);
		$status=SystemIO::post('status','int');
		if(UserCurrent::havePrivilege('IS_TRUONGBANBIENTAP') && $status == 1)
			$is_return =2;
		else
			$is_return =1;	
		
		$txt_reason=$user_info['user_name'].' - '.$reason.' ('.date('H:i j/n/y',time()).')<br/>';
		$sql='UPDATE store SET reasons =  CONCAT("'.$txt_reason.'",reasons),status='.$status.',is_return = '.$is_return.' WHERE id='.$news_id;
		if($newsObj->querySql($sql))
			echo 1;
		else
			echo 0;	
		break;
	case 'send_message':
		$content=SystemIO::post('content','def');
		if($content){
			$arrNewData=array('nw_id'=>0,'recipients_id'=>0,'user_id'=>$user_info['id'],'content'=>$content,'property'=>1,'time_created'=>time());
			if($newsObj->insertData('message',$arrNewData))
				echo 1;
			else
				echo 0;		
		}
		else
			echo 0;			
		break;
	case 'delete':
		$list_data= $newsObj->getListData('store','*','id='.$news_id,'','0,1','',false);
		$row=current($list_data);
		$row['nw_id']=$row['id'];
		unset($row['id']);
		if($newsObj->insertData('store_deleted',$row)){
			$newsObj->delData('store','id='.$news_id,1);
			echo 1;
		}
		else
			echo 0;	
		
		
		break;
	case 'post_maket':
		if($news_id){
			if($newsObj->updateData('store',array('property'=>2),'id='.$news_id)){
				
				echo 1;
			}
			else 
				echo 0;
		}	
		break;
												
	
}
