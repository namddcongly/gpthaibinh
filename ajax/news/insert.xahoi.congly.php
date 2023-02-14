<?php
ini_set('display_errors',1);
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/backend/includes/get.news.php';
require(APPLICATION_PATH . 'news'. DS . 'frontend' . DS . 'includes' . DS . 'frontend.news.php');
require_once 'application/news/backend/includes/define.config.database.php';
require_once UTILS_PATH.'convert.php';
$action=SystemIO::post('action','def','');
$news_id= SystemIO::post('nw_id','int',0);
$user_info=UserCurrent::$current->data;
$newsObj=new BackendNews();
$frontendObj=new FrontendNews();
$cate_id=SystemIO::get('cate_id','int',0);
$list_category=$frontendObj->getCategory();
if($list_category[$cate_id]['cate_id1'])
	$cate_path = ','.$list_category[$cate_id]['cate_id1'].','.$cate_id.',';
else	
	$cate_path = ','.$cate_id.',';
//ini_set('display_errors',1);
$href=SystemIO::get('link','def','');
$row=array();
$row_xahoi = json_decode(file_get_contents($href.'?mode=json'),true);
$row['user_id']  = $user_info['id'];
$row['censor_id']= $user_info['id'];
if(is_array($row_xahoi['tags']))
	$row['tag']=implode(',',$row_xahoi['tags']);
else
	$row['tag']='';
$row['relate']='';
$row['time_created']=time();
$row['cate_id']=$cate_id;
$row['cate_path']=$cate_path;
$row['origin'] = $row_xahoi['source_name'];
$row['type']=0;
$row['title']=$row_xahoi['news_title'];
$row['description']=str_replace('(Xã hội) -','',$row_xahoi['news_sapo']);
$arr = explode('/',$row_xahoi['news_image']);
$image_name = preg_replace('/[^a-zA-Z0-9]/','',$arr[count($arr)-1]);
$row['img1']=$image_name;
$row['img2']=$image_name;
$countRecord=$newsObj->countRecord('store','title LIKE "%'.str_replace('"','&quot;',$row['title']).'%"');
if($countRecord)
{
	echo 1;
	die;
}
$id=$newsObj->insertData('store',$row);
if($id){
	$content=$row_xahoi['news_content'];
	if($add_logo)
	$content = addLogo($content,$row);
	$newsObj->insertData('store_content',array('content'=>$content,'nw_id'=>$id));
	$newsObj->insertData('store_hit',array('nw_id'=>$id,'hit'=>1,'time_created'=>$row['time_created'],'cate_path'=>$row['cate_path']));
	$keyword=Convert::convertUtf8ToSMS($row['title'].' '.$row['description'].' '.$row['tag']).' '.Convert::convertUtf8ToTelex($row['title'].' '.$row['description'].' '.$row['tag']);
	$newsObj->insertData('search',array('nw_id'=>$id,'cate_id'=>$row['cate_id'],'keyword'=>$keyword,'cate_path'=>$row['cate_path'],'time_public'=>time(),'cate_path'=>$row['cate_path']));
	/*Lấy anh đại diện*/
	$path_img_save=NEWS_IMG_UPLOAD.date('Y/n/j',$row['time_created']);
	$img=file_get_contents($row_xahoi['news_image']);
	file_put_contents($path_img_save.'/'.$row['img2'],$img);
	$result['name']=$row['img2'];
	copy(ROOT_URL.'image.php?weight=135&height=90&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/cnn_135x90/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
	copy(ROOT_URL.'image.php?weight=63&height=63&cropratio=1:1&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/v_63x63/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
	copy(ROOT_URL.'image.php?weight=225&height=150&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/cnn_225x150/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
	copy(ROOT_URL.'image.php?weight=306&height=204&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/cnn_306x204/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
	copy(ROOT_URL.'image.php?weight=405&height=270&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/news/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
	echo 1;
}
else
	echo 0;	
	