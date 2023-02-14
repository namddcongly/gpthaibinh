<?php
//ini_set("display_errors", 1);
require_once 'application/news/backend/includes/backend.news.php';
require_once UTILS_PATH.'convert.php';
$user_info=UserCurrent::$current->data;
$newsObj=new BackendNews();
$content = SystemIO::post('edit_content','def','');
$content = base64_decode($content);
/*
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
			if(strpos($images[$i],"youtube")) continue;
			$text = @file_get_contents(str_replace(" ","%20",$images[$i]));
			$file_size=(int)@filesize(str_replace('http://cms.congly.com.vn/','',$images[$i]));
			if($text != "")
			{
				$arr = explode('/', $images[$i]);
				$image_name = preg_replace('/[^a-zA-Z0-9]/','',$arr[count($arr)-1]);
				//if(strpos($image_name, "jpg") === FALSE && strpos($image_name, "jpeg") === FALSE && strpos($image_name, "gif") === FALSE && strpos($image_name, "png") === FALSE)
				if(strpos($image_name, "flv") === FALSE)
				{
					$image_name = str_replace('jpg','',$image_name);
					$image_name = str_replace(' ','-',$image_name);
					$image_name .= ".jpg";
				}
				if(!is_dir(NEWS_IMG_UPLOAD.date('Y',time()).'/'.date('n',time()).'/'.date('j',time()).DS.$user_info['id']))
					@mkdir(NEWS_IMG_UPLOAD.date('Y',time()).'/'.date('n',time()).'/'.date('j',time()).DS.$user_info['id']);
				@file_put_contents(NEWS_IMG_URL.$user_info['id'].DS.$image_name, $text);
				$content = str_replace($images[$i],NEWS_IMG_URL.$user_info['id'].DS.$image_name, $content);
			}

		}
	}
}
*/
$arrData = array(
	'title' => SystemIO::post('title'),
	'user_id' => $user_info['id'],
	'description' => SystemIO::post('description'),
	'content' => $content,
	'news_id' => SystemIO::post('news_id'),
	'is_used' => 0,
	'tag' => SystemIO::post('tag'),
	'time_updated' => time()
);

echo $newsObj->insertData('autosave', $arrData);


