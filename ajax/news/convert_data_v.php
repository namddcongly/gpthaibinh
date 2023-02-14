<?php
ini_set('display_errors',1);
require_once   ROOT_PATH.'system/kernel/includes/single.database.php';
require_once 'application/news/includes/convert_model.php';
require_once UTILS_PATH.'image.resize.php'; 

if($_SERVER['HTTP_HOST']=='192.168.1.110:8009' || $_SERVER['HTTP_HOST']=='192.168.1.112')
	$config = array ('username' => 'root', 'password' => '', 'host' => '192.168.1.110','host_reserve'=>'localhost', 'dbname' => 'joc_star');
else
	$config = array ('username' => 'root', 'password' => '', 'host' => 'localhost','host_reserve'=>'localhost', 'dbname' => 'joc_star');
		
$object = new SingleDatabase($config);

$convert = new ConvertModel();

$news = $convert->getList("status=4","id desc","0,1");

if(count($news) > 0)
{
	$update_id = "0";
	
	$imageResize=new ImageResize();
	
	foreach ($news as $n)
	{
		$update_id .= ",".$n["id"];
		
		//lay anh hinh chu nhat 347x255 thumb ve 300x180 va 210x126
		//$tem = @file_get_contents('http://image.xahoi.com.vn:8001/news/'.date("Y/n/j", $n["time_created"]).'/'.$n["img2"]);
		
		//@file_put_contents(ROOT_PATH."data/temp/image.jpg",$tem);
		//thumb ve 300x180
		//@$imageResize->createThumb(300,180,ROOT_PATH."data/temp/image.jpg",ROOT_PATH.'data/cnn_300x180/'.date('Y/n/j',$n["time_created"]).'/'.$n['img2']);
		//thumb ve 210x126
		//@$imageResize->createThumb(210,126,ROOT_PATH."data/temp/image.jpg",ROOT_PATH.'data/cnn_210x126/'.date('Y/n/j',$n["time_created"]).'/'.$n['img2']);

		//lay anh hinh chu nhat 143x143 thumb ve 100x100
		$tem = @file_get_contents('http://image.xahoi.com.vn:8001/news/'.date("Y/n/j", $n["time_created"]).'/'.str_replace(" ","%20",$n["img1"]));
		
		@file_put_contents(ROOT_PATH."data/temp/image1.jpg",$tem);
		//copy ảnh 143x143 sang 150x150
		@file_put_contents(ROOT_PATH."data/news/".date("Y/n/j", $n["time_created"])."/".$n["img1"],$tem);
		//thumb ve 100x100
		@$imageResize->createThumb(100,100,ROOT_PATH."data/temp/image1.jpg",ROOT_PATH.'data/v_100x100/'.date('Y/n/j',$n["time_created"]).'/'.$n['img1'],true);
		//thumb ve 63x63
		
		$tem = file_get_contents('http://image.xahoi.com.vn:8001/v_63x63/'.date("Y/n/j", $n["time_created"]).'/'.str_replace(" ","%20",$n["img1"]));
		
		file_put_contents(ROOT_PATH."data/v_63x63/".date("Y/n/j", $n["time_created"])."/".$n["img1"],$tem);
		echo date('Y/n/j',$n["time_created"]);
	}
	
	$convert->updateData(array("status"=>"5"), "","id IN($update_id)");
	echo '<meta http-equiv="refresh" content="2;url=ajax.php?path=news&fnc=convert_data_v">';
}
?>
