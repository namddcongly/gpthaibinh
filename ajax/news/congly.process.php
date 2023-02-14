<?php
ini_set('display_errors',1);
require_once 'application/news/backend/includes/backend.news.php';
$action=SystemIO::post('action','def','');
$news_id= SystemIO::post('nw_id','int',0);
$user_info=UserCurrent::$current->data;
$newsObj=new BackendNews();
switch($action)
{
	case 'do-censor':
		$newsObjXahoi=new GetNews(XAHOI_USER_NAME,XAHOI_PASSWORD,XAHOI_HOSTING,XAHOI_DB_NAME);
		$review_id=SystemIO::post('review_id','def','');
		$cate_id=SystemIO::post('cong_ly_cate_id','int',0);
		$rows=$newsObjXahoi->getListReview('id='.$review_id);
		$row=$rows['0'];
		$result_xahoi=$newsObjXahoi->insertReviewToStore($review_id,$user_info['id'],$time,0);// insert vào trang xahoi
		$result_congly=$newsObj->insertToStoreFromXahoiOrNgoisao($row,$user_info['id'],time(),0);
		break;
	case 'news-return-to-congly':
		$reason=SystemIO::post('reason','def','');
		$news_id=SystemIO::post('nw_id','int');
		if($reason){
			if($newsObj->updateData('review',array('reason_return'=>'Công lý: '.$reason,'status'=>1),'id='.$news_id))
			{
				echo 1;
			}
			else echo 0;
		}
		else echo 0;
		break;
}