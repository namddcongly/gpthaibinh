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
$cate_id=SystemIO::post('cate_id','int',0);
$add_logo=SystemIO::post('add_logo','int',0);
$list_category=$frontendObj->getCategory();
if($cate_id){
	if($list_category[$cate_id]['cate_id1'])
		$cate_path = ','.$list_category[$cate_id]['cate_id1'].','.$cate_id.',';
	else	
		$cate_path = ','.$cate_id.',';
}
else {

	$cate_id=370;
	$cate_path=',370,';
}
switch($action)
{
	case 'get_news':
		$src=SystemIO::post('src','def');		
		if($src=='ngoisao.vn')
		{
			$siteNewsObj=new GetNews(NGOISAO_USER_NAME,NGOISAO_PASSWORD,NGOISAO_HOSTING,NGOISAO_DB_NAME);
			$row=$siteNewsObj->getStoreOne($news_id);
			unset($row['img5']);
			unset($row['topic_id']);
			unset($row['record_id']);
			$row['user_id']  = $user_info['id'];
			$row['censor_id']= $user_info['id'];
			$row['editor_id']= $user_info['id'];
			$row['description']=str_replace('(Ngoisao.vn) - ','',$row['description']);
			$row['description']=str_replace(array('(Ngoisao.vn) –','Ngoisao.vn','()','ngoisao.vn'),array('','','',''),$row['description']);
			$row['time_public']=time();
			$content=$siteNewsObj->getContentOne($news_id);
			$row['img1']=$row['img3'];
			$row['cate_id']=$cate_id;
			$row['cate_path']=$cate_path;
			$news_id_ngoisao=$row['id'];
			unset($row['id']);
			$row['type']=0;
			$countRecord=$newsObj->countRecord('store','title LIKE "%'.str_replace('"','&quot;',$row['title']).'%"');
			if($countRecord)
			{
				
				$siteNewsObj->updateData('store',array('time_public'=>time()),'id='.$news_id_ngoisao);
				echo 1;
				die;
			}
			$id=$newsObj->insertData('store',$row);
			if($id){
				$content=str_replace('src="data','src="http://img.ngoisao.vn',$content);
				$content=str_replace('_have_logo_','',$content);
				if($add_logo)
				$content = addLogo($content,$row);
				
				$newsObj->insertData('store_content',array('content'=>$content,'nw_id'=>$id));
				$newsObj->insertData('store_hit',array('nw_id'=>$id,'hit'=>1,'time_created'=>$row['time_created'],'cate_path'=>$row['cate_path']));
				$keyword=Convert::convertUtf8ToSMS($row['title'].' '.$row['description'].' '.$row['tag']).' '.Convert::convertUtf8ToTelex($row['title'].' '.$row['description'].' '.$row['tag']);
				$newsObj->insertData('search',array('nw_id'=>$id,'cate_id'=>$row['cate_id'],'keyword'=>$keyword,'cate_path'=>$row['cate_path'],'time_public'=>time(),'cate_path'=>$row['cate_path']));
				/*Lấy anh đại diện*/
				$path_img_save=NEWS_IMG_UPLOAD.date('Y/n/j',$row['time_created']);
				$img=file_get_contents('http://backend.ngoisao.vn/data/news/'.date('Y/n/j',$row['time_created']).'/'.$row['img3']);
				file_put_contents($path_img_save.'/'.$row['img3'],$img);
				$result['name']=$row['img3'];
				copy(ROOT_URL.'image.php?weight=135&height=90&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/cnn_135x90/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
				copy(ROOT_URL.'image.php?weight=63&height=63&cropratio=1:1&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/v_63x63/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
				copy(ROOT_URL.'image.php?weight=225&height=150&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/cnn_225x150/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
				copy(ROOT_URL.'image.php?weight=306&height=204&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/cnn_306x204/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
				copy(ROOT_URL.'image.php?weight=405&height=270&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/news/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
				$siteNewsObj->updateData('store',array('time_public'=>time()),'id='.$news_id_ngoisao);
				echo 1;
			}
			else
				echo 0;	
		}
		elseif($src=='xahoi.com.vn')
		{
			$href=SystemIO::post('link','def','');
			$row=array();
			$href= 'http://xahoi.com.vn/preview/preview-'.$news_id.'.html?mode=preview&type=json';
			$row_xahoi = json_decode(file_get_contents($href),true);
			$row['user_id']  = $user_info['id'];
			$row['censor_id']= $user_info['id'];
			$row['editor_id']= $user_info['id'];
			if(is_array($row_xahoi['tags']))
				$row['tag']=implode(',',$row_xahoi['tags']);
			else
				$row['tag']='';
			$row['relate']='';
			$row['time_created']=time();
			if(!$cate_id) {
				$cate_id= 370;
				$cate_path=',370,';	
			}
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
				$content=str_replace(array('stamp2.','stamp1.','stamp3.'),array('','',''),$content);
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
				$data 				= array();
				$data['news_id']    = $news_id;
				$data['token']      = 'ea77546e6469b95521e8cc256818fa82';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'http://xahoi.com.vn/api?post=congly');
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				$response = array('data' 	=> curl_exec($ch),
				                'error' 	=> curl_error($ch));
				curl_close ($ch);
				echo 1;
			}
			else
				echo 0;	
		}
		elseif($src=='duluan.com.vn')
		{
			ini_set('display_errors',1);
			$siteNewsObj=new GetNews(DULUAN_USER_NAME,DULUAN_PASSWORD,DULUAN_HOSTING,DULUAN_DB_NAME);
			$row=$siteNewsObj->getStoreOne($news_id);
			$content=$siteNewsObj->getContentOne($news_id);
            $content = stripcslashes($content);
            $partern = '/src=\"([^\"]*)\"/';
            preg_match_all($partern, $content, $m);
            $images = $m[1];
            $leng = count($images);
            if($leng > 0)

            {
                for($i=0;$i<$leng;$i++)
                {
                    if(strpos($images[$i],"data/news/") === FALSE || strpos($images[$i],"data/news/") != 0)
                    {
                        $text = @file_get_contents(str_replace(" ","%20",$images[$i]));
                        if($text != "")
                        {
                        
                            $arr = explode('/', $images[$i]);
                            
                            $image_name = preg_replace('/[^a-zA-Z0-9]/','',$arr[count($arr)-1]);
                            
                            //if(strpos($image_name, "jpg") === FALSE && strpos($image_name, "jpeg") === FALSE && strpos($image_name, "gif") === FALSE && strpos($image_name, "png") === FALSE)
                            if(strpos($image_name, "flv") === FALSE)
                                $image_name .= time().".jpg";
                            
                            if(!is_dir(NEWS_IMG_UPLOAD.date('Y',time()).'/'.date('n',time()).'/'.date('j',time()).DS.$user_info['id']))
                                @mkdir(NEWS_IMG_UPLOAD.date('Y',time()).'/'.date('n',time()).'/'.date('j',time()).DS.$user_info['id']);
                            @file_put_contents(NEWS_IMG_URL.$user_info['id'].DS.$image_name, $text);
                            $content = str_replace($images[$i],NEWS_IMG_URL.$user_info['id'].DS.$image_name, $content);
                        }
                        
                    }		    	
                }
            }
            
            
            
			$row['cate_id']=$cate_id;
			unset($row['id']);
			//`cate_id1`, `cate_id2`, `title`, `description`, `relate`, `img`, `cm_id`, `is_home`, `origin`, `tag`, `is_video`, `file`, `is_img`, `censor_id`, `time_public`, `time_created`
			//`id`, `user_id`, `editor_id`, `cate_id`, `cate_path`, `cate_other`, `title`, `description`, `relate`, `img1`, `img2`, `img3`, `img4`, `img5`, `province_id`, `district_id`, `comment_id`, `poll_id`, `status`, `type`, `type_post`, `origin`, `author`, `tag`, `file`, `is_video`, `is_img`, `arrange`, `censor_id`, `time_public`, `time_created`
			$arrNewData=array(
				'user_id'		=>$row['censor_id'],
				'cate_id'		=>$cate_id,
				'cate_path'		=>','.$cate_id.',',
				'title'			=>$row['title'],
				'description'	=>$row['description'],
				'img1'			=>$row['img'],
				//'origin'		=>$row['origin'],
				'type_post'		=>1,
				'author'		=>'duluan.com.vn',
				'time_public'	=>$row['time_public'],
				'time_created'	=>$row['time_created'],
				'tag'			=>$row['tag']
			);			
			$countRecord=$newsObj->countRecord('store','title LIKE "%'.str_replace('"','&quot;',$row['title']).'%"');
			if($countRecord)
			{
				echo 1;
				die;
			}
			$id=$newsObj->insertData('store',$arrNewData);
			if($id){
				//$content=str_replace('src="data/news/','src="http://image2.xahoi.com.vn/news/',$content);
				$newsObj->insertData('store_content',array('content'=>$content,'nw_id'=>$id));
				$newsObj->insertData('store_hit',array('nw_id'=>$id,'hit'=>1));
				$keyword=Convert::convertUtf8ToSMS($arrNewData['title'].' '.$arrNewData['description'].' '.$arrNewData['tag']).' '.Convert::convertUtf8ToTelex($row['title'].' '.$arrNewData['description'].' '.$arrNewData['tag']);
				$newsObj->insertData('search',array('nw_id'=>$id,'cate_id'=>$arrNewData['cate_id'],'keyword'=>$keyword,'cate_path'=>$arrNewData['cate_path'],'cate_path'=>$arrNewData['cate_path']));
				/*Lấy anh đại diện*/
				$path_img_save=NEWS_IMG_UPLOAD.date('Y/n/j',$row['time_created']);
				$img=file_get_contents('http://duluan.com.vn/data/cnn_395x263/'.date('Y/n/j',$row['time_created']).'/'.$row['img']);
				file_put_contents($path_img_save.'/'.$row['img'],$img);
				$result['name']=$row['img'];
				copy(ROOT_URL.'image.php?weight=135&height=90&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/cnn_135x90/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
				copy(ROOT_URL.'image.php?weight=225&height=150&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/cnn_225x150/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
				copy(ROOT_URL.'image.php?weight=306&height=204&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/cnn_306x204/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
				copy(ROOT_URL.'image.php?weight=405&height=270&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/news/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
               echo 1;
			}
			else
				echo 0;	
		}
		elseif($src=='other.net')
		{
		
				dbObject()->unsetProperty();
				dbObject()->setProperty('crawl','items');
				
				$sql="SELECT * FROM items WHERE id = ".$news_id;
				dbObject()->query($sql);
				$rows=dbObject()->fetchAll();
				$row=$rows['0'];
				
				$arrNewData=array(
					'user_id'		=>$user_info['id'],
					'cate_id'		=>$cate_id,
					'cate_path'		=>','.$cate_id.',',
					'title'			=>strip_tags($row['title']),
					'description'	=>strip_tags($row['description']),
					'img1'			=>$row['image'],
					//'origin'		=>@$row['origin'] ? @$row['origin'] : 'retien.net',
					'type_post'		=>1,
					//'author'		=>'retien.net',
					'time_public'	=>time(),
					'time_created'	=>time(),
					'tag'			=>$row['tag']
				);
			
			$countRecord=$newsObj->countRecord('store','title LIKE "%'.str_replace('"','&quot;',$row['title']).'%"');
			if($countRecord)
			{
				echo 1;
				die;
			}
			$id=$newsObj->insertData('store',$arrNewData);
			if($id){
				dbObject()->query("UPDATE items SET status_insert=1 WHERE id=".$news_id);
				$newsObj->insertData('store_content',array('content'=>$row['content'],'nw_id'=>$id));
				$newsObj->insertData('store_hit',array('nw_id'=>$id,'hit'=>1,'time_created'=>$arrNewData['time_created'],'cate_path'=>$arrNewData['cate_path']));
				$keyword=Convert::convertUtf8ToSMS($arrNewData['title'].' '.$arrNewData['description'].' '.$arrNewData['tag']).' '.Convert::convertUtf8ToTelex($row['title'].' '.$arrNewData['description'].' '.$arrNewData['tag']);
				$newsObj->insertData('search',array('nw_id'=>$id,'cate_id'=>$arrNewData['cate_id'],'keyword'=>$keyword,'cate_path'=>$arrNewData['cate_path'],'time_public'=>time(),'cate_path'=>$arrNewData['cate_path']));
				$url_image = 'http://duluan.com.vn/data/crawl/'.date('Y/d-m/',$row['date_created']).(date('H',$row['date_created'])%24).'/'.$row['image'];				
				$img=file_get_contents($url_image);
				$path_img_save=NEWS_IMG_UPLOAD.date('Y/n/j',time());
				file_put_contents($path_img_save.'/'.$row['image'],$img);
				$result['name']=$row['image'];
				copy(ROOT_URL.'image.php?weight=135&height=90&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/cnn_135x90/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
				copy(ROOT_URL.'image.php?weight=225&height=150&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/cnn_225x150/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
				copy(ROOT_URL.'image.php?weight=306&height=204&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/cnn_306x204/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
				copy(ROOT_URL.'image.php?weight=405&height=270&image='.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_save.'/'.$result['name']),ROOT_PATH.'data/news/'.date('Y/n/j',$row['time_created']).'/'.$result['name']);
				echo 1;
			}
			else
				echo 0;			
						
		}
		
}
function addLogoIntoImage($logo_file,$image_file,$position=2,$image_file_have_logo=null)
{
	if($image_file_have_logo===null) $image_file_have_logo=$image_file;
	$photo = imagecreatefromjpeg($image_file);	
	$fotoW = imagesx($photo);
	$fotoH = imagesy($photo);
	$logoImage = imagecreatefrompng($logo_file);
	$logoW = imagesx($logoImage);
	$logoH = imagesy($logoImage);
	$photoFrame = imagecreatetruecolor($fotoW,$fotoH);
	$dest_x = $fotoW - $logoW;
	$dest_y = $fotoH - $logoH;
	imagecopyresampled($photoFrame, $photo, 0, 0, 0, 0, $fotoW, $fotoH, $fotoW, $fotoH);
	if($position==3)
		imagecopy($photoFrame, $logoImage, $dest_x/2, 2*$dest_y/3, 0, 0, $logoW, $logoH);
	else
		imagecopy($photoFrame, $logoImage, $dest_x/$position, $dest_y/$position, 0, 0, $logoW, $logoH);
	imagejpeg($photoFrame, $image_file_have_logo,95); 
}
function addLogo($content,$row)
{
	$user_info=UserCurrent::$current->data;
	$content = stripcslashes($content);
	$partern = '/src=\"([^\"]*)\"/';
	preg_match_all($partern, $content, $m);
	$images = $m[1];
	$leng = count($images);
	if($leng > 0)
	{
	    for($i=0;$i<$leng;$i++)
	    {
	    	if(strpos($images[$i],"data/news/") === FALSE || strpos($images[$i],"data/news/") != 0)
	    	{
		        $text = @file_get_contents(str_replace(" ","%20",$images[$i]));
		        if($text != "")
		        {
    		        $arr = explode('/', $images[$i]);
    		        $image_name = preg_replace('/[^a-zA-Z0-9]/','',$arr[count($arr)-1]);
    		        
    		        //if(strpos($image_name, "jpg") === FALSE && strpos($image_name, "jpeg") === FALSE && strpos($image_name, "gif") === FALSE && strpos($image_name, "png") === FALSE)
    		        if(strpos($image_name, "flv") === FALSE)
    		            $image_name .= time().".jpg";
    		        
    		        if(!is_dir(NEWS_IMG_UPLOAD.date('Y',time()).'/'.date('n',time()).'/'.date('j',time()).DS.$user_info['id']))
    		            @mkdir(NEWS_IMG_UPLOAD.date('Y',time()).'/'.date('n',time()).'/'.date('j',time()).DS.$user_info['id']);
    		            
    		        @file_put_contents(NEWS_IMG_URL.$user_info['id'].DS.$image_name, $text);
    	        	addLogoIntoImage('webskins/skins/news/images/logo_congly.png',NEWS_IMG_URL.$user_info['id'].DS.$image_name,2);
    		        $content = str_replace($images[$i],NEWS_IMG_URL.$user_info['id'].DS.$image_name, $content);
		        }
		        
	    	}		    	
	    }
	}
	return $content;
}