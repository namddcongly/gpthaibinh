<?php
require(APPLICATION_PATH . 'news'. DS . 'frontend' . DS . 'includes' . DS . 'frontend.news.php');
$frontendObj=new FrontendNews();
$id= (int)$_GET['id'];
if($id)
{
	
	$detail= $frontendObj->newsOne($id);
    $detail_content =$frontendObj->detail($id);
    $detail['content'] = $detail_content;
    echo  json_encode($detail);	
}
else
{
	$cate_id = 327;
	$date= $_GET['date'];//d-m-y;
	$time_public = (int)strtotime($date);
	if($time_public) $wh ='time_public >'.$time_public.' AND cate_id = '.$cate_id;
	else $wh= 'cate_id = '.$cate_id;	
	$list_news=$frontendObj->getNews('store','id,title,description,cate_id,img1,time_public,time_created',$wh,'time_public DESC',20);
	$array=array();
	foreach($list_news as $row)
	{
		$array[]=array(	
			'id'=>$row['id'],
			'title'=>$row['title'],
			'time_public'=>$row['time_public'],
			'description'=>$row['description'],
			'img'=>IMG::thumb($row['time_created'],$row['img1'],'cnn_225x150'),
			'link'=>Url::Link(array('id'=>$row['id'],'title'=>$row['title'],'cate_alias'=>'kinh-doanh/chung-khoan'),'news','congly_detail'),	
		);
	}
	echo json_encode($array);
}