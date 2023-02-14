<?php
ini_set("display_errors", 1);
require_once   ROOT_PATH.'system/kernel/includes/single.database.php';

if($_SERVER['HTTP_HOST']=='192.168.1.110:8009' || $_SERVER['HTTP_HOST']=='192.168.1.112')
	$config = array ('username' => 'root', 'password' => '', 'host' => '192.168.1.110','host_reserve'=>'localhost', 'dbname' => 'ngoisao_news');
else
	$config = array ('username' => 'root', 'password' => 'd@t@j0c#345', 'host' => 'localhost','host_reserve'=>'localhost', 'dbname' => 'ngoisao_news');
		
$object = new SingleDatabase($config);

$type = isset($_GET["type"]) ? $_GET["type"] : "";
$ngoisao = isset($_POST["ngoisao"]) ? $_POST["ngoisao"] : 0;
$xahoi = isset($_POST["xahoi"]) ? $_POST["xahoi"] : 0;
$news_id = isset($_POST["news_id"]) ? $_POST["news_id"] : "";

//convert toan bo danh muc nay sang danh muc khac

$arr = explode(",",$ngoisao);

$list_field = 'id,
				user_id,
				editor_id,
				title,
				description,
				relate,
				img1,
				img2,
				img3,
				img4,
				province_id,
				district_id,
				comment_id,
				`type`,
				type_post,
				origin,
				author,
				tag,
				`file`,
				is_video,
				is_img,
				arrange,
				censor_id,
				time_public,
				time_created';

if($type == "all")
{
	$sql1 = "INSERT IGNORE INTO ngoisao_news.convert_store 
					($list_field) 
				SELECT 
					$list_field
				FROM joc_news.store 
				WHERE joc_news.store.cate_path like '%,$xahoi,%'";
	
	$object->query($sql1);
	
	$sql2 = "UPDATE ngoisao_news.convert_store set 
				ngoisao_news.convert_store.cate_id=".($arr[count($arr)-1]).",
				ngoisao_news.convert_store.cate_path=',$ngoisao,'				 
				WHERE ngoisao_news.convert_store.status=2";
	
	$object->query($sql2);
	
	$sql3 = "INSERT IGNORE INTO ngoisao_news.store 
					($list_field,cate_id,cate_path) 
				SELECT 
					$list_field,cate_id,cate_path 
				FROM ngoisao_news.convert_store 
				WHERE ngoisao_news.convert_store.status =2";
	
	$object->query($sql3);	
	
	$sql4 = "Insert IGNORE into ngoisao_news.store_content 
				(Select * from
				joc_news.store_content 		 
				WHERE joc_news.store_content.nw_id IN (select ngoisao_news.convert_store.id from ngoisao_news.convert_store where ngoisao_news.convert_store.status=2))";
	$object->query($sql4);
	$sql5 = "UPDATE ngoisao_news.convert_store set 
				ngoisao_news.convert_store.status=3				 
				WHERE ngoisao_news.convert_store.status=2";
	$object->query($sql5);
	
}
else
{
	$sql1 = "INSERT IGNORE INTO ngoisao_news.convert_store 
					($list_field) 
				SELECT 
					$list_field 
				FROM joc_news.store 
				WHERE joc_news.store.id in(".$news_id."0)";
	
	$object->query($sql1);
	
	$sql2 = "UPDATE ngoisao_news.convert_store set 
				ngoisao_news.convert_store.cate_id=".($arr[count($arr)-1]).",
				ngoisao_news.convert_store.cate_path=',$ngoisao,' 
				WHERE ngoisao_news.convert_store.status=2";
		
	$object->query($sql2);	
	
	$sql3 = "INSERT IGNORE INTO ngoisao_news.store 
					($list_field,cate_id,cate_path) 
				SELECT 
					$list_field,cate_id,cate_path 
				FROM ngoisao_news.convert_store 
				WHERE ngoisao_news.convert_store.status =2";
	
	$object->query($sql3);
	$sql4 = "Insert IGNORE into ngoisao_news.store_content 
				(Select * from 
				joc_news.store_content 		 
				WHERE joc_news.store_content.nw_id IN (select ngoisao_news.convert_store.id from ngoisao_news.convert_store where ngoisao_news.convert_store.status=2))";
	$object->query($sql4);
	$sql5 = "UPDATE ngoisao_news.convert_store set 
				ngoisao_news.convert_store.status=3				 
				WHERE ngoisao_news.convert_store.status=2";
	$object->query($sql5);
}

?>