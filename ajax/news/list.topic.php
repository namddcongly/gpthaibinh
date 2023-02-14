<?php
ini_set('display_errors',0);
require_once 'application/news/backend/includes/backend.news.php';
$newsObj= new BackendNews();
$id=SystemIO::post('id','int');
$cate_id=SystemIO::post('cate_id','int',0);
if($cate_id)
	$wh='cate_id='.$cate_id;
else
	$wh='';	
$wh='';	
$list_topic = $newsObj->getListData('topic','id,name,property,time_created',$wh,'property DESC,time_created DESC','0,100','id',false);
$txt='<div style="margin:10px 0px 0px 155px">';
if(count($list_topic))
{
	$k=1;
	foreach($list_topic as $row)
	{
		if($k > 15) $style="display:none;";
		else $style="";
		if($id== $row['id']) $c='checked="checked"';
		else $c='';
		
		if($row['property'] >0)
			$txt.='<p style="'.$style.'" id="topic_'.$k.'"><input '.$c.' type="radio" name="topic_id" value="'.$row['id'].'" /> '.$row['name'].'&nbsp;&nbsp;</p>';
		else
			$txt.='<p style="color:#CCC; '.$style.'" id="topic_'.$k.'"><input '.$c.' type="radio" name="topic_id" value="'.$row['id'].'" /> '.$row['name'].'&nbsp;&nbsp;[&nbsp;<a href="javascript:;" onclick="updateTopic('.$row['id'].',1)">Thiết lập hiển thị</a>&nbsp;]</p>';
					
		++$k;
	}
	$txt.='<p><a onclick="showMoreTopic()" href="javascript:void;"> Xem thêm </a></p>';
}
else
	$txt.='Không có chủ đề nào trong danh mục bạn đã chọn';
$txt.='</div>';
echo $txt;