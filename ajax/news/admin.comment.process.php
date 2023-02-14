<?php
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/user/includes/comment.php';
$action=SystemIO::post('action','def','');
$news_id= SystemIO::post('nw_id','int',0);
$user_info=UserCurrent::$current->data;
$newsObj=new BackendNews();
switch($action)
{
	case 'view-content-to-comment':
		$commentObj = new Comment();
		$comment_id=SystemIO::post('cmt_id','int','');
		$row = $commentObj->selectOne('title,content',$comment_id);
		$html .= '<p style="margin-left:5px;font-weight:bold;">'.$row['title'].'</p>';
		$html .= '<br />';
		$html .= $row['content'];

		echo $html;
		break;
	case 'delete-from-comment':
		$id=SystemIO::post('cmt_id','int',0);
		$commentObj = new Comment();
		if($commentObj->deleteData($id))
		echo 1;
		else
		echo 0;
		break;
	case 'do-comment-censor':
		$id=SystemIO::post('cmt_id','int',0);
		$public=SystemIO::post('public','int',0);
		$user_info=UserCurrent::$current->data;
		$commentObj = new Comment();
		if ($public)
		{
			$time = time();
			$censor_id = $user_info['id'];
		}
		else
		{
			$time = 0;
			$censor_id = 0;
		}
		$comment = array();
		$comment['id'] = $id;
		$comment['time_public'] = $time;
		$comment['censor_id'] = $censor_id;
		if ($commentObj->updateData($comment,$id))
		echo 1;
		else
		echo 0;
		break;
	case 'do-comment-censor-multi':
		$ids = SystemIO::post('cmt_ids','string','');
		$public=SystemIO::post('public','int',0);
		$user_info=UserCurrent::$current->data;
		$commentObj = new Comment();
		if ($public)
		{
			$time = time();
			$censor_id = $user_info['id'];
		}
		else
		{
			$time = 0;
			$censor_id = 0;
		}
		$comment = array();
		$comment['time_public'] = $time;
		$comment['censor_id'] = $censor_id;
		if ($ids)
		{
			if ($commentObj->censorMultiData($comment,$ids))
			echo 1;
			else
			echo 0;
		}
		else
		echo 0;
		break;
	case 'delete-comment-multi':
		$ids = SystemIO::post('cmt_ids','string','');
		$commentObj = new Comment();
		if ($ids)
		{
			if($commentObj->deleteMultiData($ids))
			echo 1;
			else
			echo 0;
		}
		else
		echo 0;
		break;

}