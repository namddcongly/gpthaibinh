<?php 
require_once 'application/news/backend/includes/backend.news.php';
$newsObj=new BackendNews();

$title = SystemIO::post("title","str","");

if($title != "")
{
	$list_review = $newsObj->countRecord('review',"title='$title'");
	$list_store = $newsObj->countRecord('store',"title='$title'");
	
	if($list_review+$list_store > 0)
		echo json_encode(array("code" => 0));
	else
		echo json_encode(array("code" => 1));
}
else
	echo json_encode(array("code" => 1));
?>