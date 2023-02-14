<?php
//ini_set('display_errors',1);
require_once 'application/news/frontend/includes/frontend.news.php';
$newsObj = new FrontendNews();
$list_category=$newsObj->getCategory('','arrange asc',200,true);
$LIST_CATEGORY=$list_category;
$LIST_CATEGORY_ALIAS=SystemIO::arrayToOption($LIST_CATEGORY,'id','alias');
$list_news_all=$newsObj->getNews('store','id,title,description,time_public,cate_id,time_created,img1,img2','','time_public DESC',6,'id',true);
$arr_news=array();
foreach($list_news_all as $row)
{
	$href = Url::Link(array("id" => $row['id'],"title" => $row['title'],'cate_alias'=>$LIST_CATEGORY_ALIAS[$row['cate_id']]), "news", "detail");
	$arr_news[$row['id']]['title']=$row['title'];
	$arr_news[$row['id']]['description']=$row['description'];
	$arr_news[$row['id']]['img']='http://congly.com.vn/'.IMG::show($newsObj->getPathNews($row['time_created']),$row['img1'],'3');	
	$arr_news[$row['id']]['href']=$href;	
}
$txt=json_encode($arr_news);
echo $txt;