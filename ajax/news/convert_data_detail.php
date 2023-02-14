<?php
ini_set('display_errors',1);

require_once 'application/news/includes/convert_model.php';
require_once 'application/news/backend/includes/backend.news.php';
require_once UTILS_PATH.'image.resize.php'; 
require_once UTILS_PATH.'convert.php'; 

if($_SERVER['HTTP_HOST']=='192.168.1.110:8009' || $_SERVER['HTTP_HOST']=='192.168.1.112')
	$config = array ('username' => 'root', 'password' => '', 'host' => '192.168.1.110','host_reserve'=>'localhost', 'dbname' => 'joc_star');
else
	$config = array ('username' => 'root', 'password' => '', 'host' => 'localhost','host_reserve'=>'localhost', 'dbname' => 'joc_star');
		
$object = new BackendNews();

$convert = new ConvertModel();

$news = $convert->getList("status=5","id desc","0,1","");
if(count($news) > 0)
$news = $news[0];

if(isset($news["title"]))
{
	$content = $object->getContentOne($news["id"]);
	
	preg_match_all('/\<img(.*?)src=\"(.*?)\"/',$content,$img);

	if(count($img[2]) > 0)
	{
		$i=0;
		foreach ($img[2] as $im)
		{
			$_img = str_replace("data/","http://image.xahoi.com.vn:8001/",$im);
			
			$path = substr($im,0,strrpos($im,"/"))."/";
			

			$temp = file_get_contents(str_replace(" ","%20",$_img));
			
			$image_name = Convert::convertUtf8ToSMS($news["title"]);
			$image_name = preg_replace('/[^a-zA-Z0-9]/',' ',$image_name);
			$image_name = preg_replace('/(\s+)/','-',$image_name)."-$i.jpg";
			
			
			@mkdir(ROOT_PATH.$path);
			
			file_put_contents(ROOT_PATH.$path.$image_name,$temp);
			
			$content = str_replace($im,$path.$image_name,$content);
			
			$i++;
		}	
		$object->updateData("store_content",array("content" => $content),"nw_id=".$news["id"]);
	}
	
	$convert->updateData(array("status"=>"6"), "","id=".$news["id"]);
	echo '<meta http-equiv="refresh" content="1;url=ajax.php?path=news&fnc=convert_data_detail">';
}
?>