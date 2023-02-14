<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
//ini_set('display_errors',1);
require_once UTILS_PATH.'paging.php';
require_once 'application/news/includes/class.video.php';
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/main/includes/user.php';
require_once UTILS_PATH.'image.upload.php';
class VideoAdmin extends Form
{
	function __construct()
	{
		Form::__construct($this);
	}
	function on_submit()
	{

		$user_info=UserCurrent::$current->data;
		$obj = new ClassVideo();

		$news_id=SystemIO::get('id','int');
		if($news_id){
			$news = $obj->readData($news_id);
			$dir = date('Y/n',$news['time_created']);
		}
		else
		{
			$dir = date('Y/n',time());
		}
		$img1='';
		$video='';

		$path_img_upload=VIDEO_UPLOAD.$dir;
		if($_FILES['img1']['name']){
			$uploader=new Uploader();
			$uploader->setPath($path_img_upload);
			$uploader->setMaxSize(2000);
			$uploader->setFileType('custom',array('jpg','jpeg','png','gif'));
			$result=$uploader->doUpload('img1');
			$img1=(string)$result['name'];
		}
		if($_FILES['video']['name']){
			$uploader=new Uploader();
			$uploader->setPath($path_img_upload);
			$uploader->setMaxSize(30000000000000);
			$uploader->setFileType('custom',array('flv','mp4'));
			$result=$uploader->doUpload('video');
			$video = (string)$result['name'];
			//var_dump($result);
		}

		$title=SystemIO::post('title','def');
		$description=SystemIO::post('description','def');
		$user_id=$user_info['id'];
		$time_created=time();
		$cate_id = SystemIO::post('cate_id','int', 0);
		$arrNewData=array(
				'description'=>$description,
				'title'=>$title,
				'cate_id'	=>$cate_id,
				'user_id'=>$user_id,
		);
		if($img1){
			$arrNewData['image_name']=$img1;
			//$this->addLogoIntoImage('webskins/skins/news/images/movie.png','data/video/'.$dir.'/'.$img1,2);
		}
		if($video)
			$arrNewData['video_name']=$video;

		if($news_id){
			if($obj->updateData($arrNewData, $news_id)) {
				Url::redirectUrl(array(),'?app=news&page=admin_video');
			}
		}
		else
		{
			$arrNewData['time_created']=$time_created;
			if($obj->insertData($arrNewData))
			{

				if(SystemIO::post('continue','int')){
					$_SESSION['news_continue']=array('cate_id'=>$cate_id,'continue'=>1);
					Url::redirectUrl(array(),'?app=news&page=admin_video&cmd=create');
				}
				else
				{
					unset($_SESSION['news_continue']);
					Url::redirectUrl(array(),'?app=news&page=admin_video');
				}
			}
		}

	}
	function addLogoIntoImage($logo_file,$image_file,$position=2,$image_file_have_logo=null){
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
	function index()
	{
		$cmd=SystemIO::get('cmd','def','intro');
		$news_id=SystemIO::get('id','int',0);
		switch($cmd)
		{

			case 'store':
				if(!UserCurrent::havePrivilege('NEWS_CREATE'))
				{
				    Url::urlDenied();
				}
				return $this->adminStore();
				break;
			case 'create':
				if(!UserCurrent::havePrivilege('NEWS_CREATE'))
				{
				    Url::urlDenied();
				}
				return $this->adminAddAndEdit($news_id);
				break;

			default:
				if(!UserCurrent::havePrivilege('NEWS_CREATE'))
				{
				    Url::urlDenied();
				}
				return $this->adminStore();
				break;
		}
	}

	function adminStore()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/video_admin.htm");
		Page::setHeader("Quản trị video", "Quản trị video", "Quản trị video");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');
		$user_info=UserCurrent::$current->data;
		//joc()->set_var('url_current', Url::Link(array('cmd' => 'store')));
		joc()->set_var('url_current','?app=news&page=admin_video&cmd=store');
		joc()->set_var('link_add',  '?app=news&page=admin_video&cmd=create');

		$obj=new ClassVideo();
		$userObj=new User();
//		$bdsObj->convertStoreToReview(16);
		/*Tìm kiếm*/
		$newsObj=new BackendNews();
		$list_category=$newsObj->getListCategory('cate_id1=0','',100,'id');

		$item_per_page=10;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');

		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$cate_id=SystemIO::get('cate_id','int',0);
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_category,'id','name'),$cate_id));

		if(UserCurrent::havePrivilege('VIEW_ALL_VIDEO'))
			$wh='1 = 1';
		else
			$wh='user_id='.	$user_info['id'];
		if($q) $wh.=" AND (title LIKE '%{$q}%')";
		if($cate_id) $wh.=" AND cate_id = {$cate_id}";

		$date_begin=SystemIO::get('date_begin','def');
		joc()->set_var('date_begin',$date_begin);
		if($date_begin)
		{
			$date_begin=strtotime(str_replace('/','-',$date_begin));
			$wh.= " AND time_created >= {$date_begin}";

		}

		$date_end=SystemIO::get('date_end','def');
		joc()->set_var('date_end',$date_end);
		if($date_end)
		{
			$date_end=strtotime(str_replace('/','-',$date_end));
			$wh.= " AND time_created <= {$date_end}";
		}

		$btv_name=SystemIO::get('btv_name','def');
		joc()->set_var('btv_name',$btv_name);
		if($btv_name)
		{
			$_user_id_search= $userObj->userNameToId($btv_name);
			$list_user_id_search='0';
			if(count($_user_id_search))
			{
				foreach($_user_id_search as $_temp)
					$list_user_id_search.=','.$_temp['id'];
			}

			$wh.=' AND user_id IN ('.$list_user_id_search.')';
		}
		/*Lay thông tin news*/

		$list_news=$obj->getListAdmin($wh,'id desc',$limit);
		$user_ids=',';
		$censor_ids=',';
		foreach($list_news as $_temp)
		{
			if(array_key_exists('user_id', $_temp)){
				if(!substr_count($user_ids,','.$_temp['user_id'].','))
						$user_ids.=$_temp['user_id'].',';
			}
		}
		$user_ids=trim($user_ids,',');

		if($user_ids)
			$list_censor_user_name_and_name_btv=$userObj->userIdToName($user_ids);

		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		$regions = $obj->getArrayProperty();
		foreach($list_news as $row)
		{
			joc()->set_var('title',$row['title']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('link_edit','?app=news&page=admin_video&cmd=create&id='.$row['id']);
			joc()->set_var('path',$list_category[$row['cate_id']]['name']);
			joc()->set_var('time_public',date('H:i d-n-Y',$row['time_created']));
			if(array_key_exists('user_id', $row)){
				joc()->set_var('name_btv',$list_censor_user_name_and_name_btv[$row['user_id']]['user_name']);
			}
			joc()->set_var('stt',$stt);

			$property='';
			foreach($regions as $p=>$desc)
			{
				if(($row['property']&$p) != $p)
					$property.='Sét: <a href="javascript:;" onclick="setProperty('.$row['id'].','.$p.', 0)">'.$desc.'</a><br/>';
				else
					$property.='Hủy: <a href="javascript:;" onclick="setProperty('.$row['id'].',0,'.$p.')">'.$desc.'</a><br/>';
			}
			joc()->set_var('property',$property);
			++$stt;
			joc()->set_var('video', str_replace('cms.congly.com.vn','congly.vn',$obj->getLink($row['time_created'], $row['video_name'])));
			joc()->set_var('image', $obj->getLink($row['time_created'], $row['image_name']));
			$text_html.=joc()->output('ListRow');
		}

		joc()->set_var('ListRow',$text_html);
		global $TOTAL_ROWCOUNT;
		$totalRecord=$TOTAL_ROWCOUNT;
		joc()->set_var('total_rowcount',$totalRecord);
		joc()->set_var('paging','<li>Tổng số: '.$totalRecord.'</li>'.Paging::paging ($totalRecord,$item_per_page,10));
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;
	}
	function adminAddAndEdit($id=0)
	{

		joc()->set_file('AdminNews', Module::pathTemplate()."backend/video_add_edit.htm");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('ckeditor.js'	, 'webskins/richtext/ckeditor/ckeditor.js', 'footer', 'js');
		Page::registerFile('jquery.adapter.js' , 'webskins/richtext/ckeditor/adapters/jquery.adapter.js', 'footer', 'js');
		Page::setHeader("Quản trị video", "Quản trị video", "Quản trị video");
		Page::registerFile('admin_news.js', Module::pathJS().'admin_bds.js' , 'footer', 'js');
		joc()->set_var('begin_form' , Form::begin(false, "POST", ' enctype="multipart/form-data" onsubmit="return checkForm()"'));
		joc()->set_var('end_form' 	, Form::end());
		joc()->set_var('url_current', '?app=news&page=admin_video&cmd=store');
		joc()->set_var('link_add',  '?app=news&page=admin_video&cmd=create');

		$obj=new ClassVideo();
		$row=isset($_SESSION['news_continue']) ? $_SESSION['news_continue'] : "";
		if($id)
			$row=$obj->readData($id);

		$newsObj=new BackendNews();
		$list_category=$newsObj->getListCategory('cate_id1=0','',100,'id');

		joc()->set_var('option_cate1',SystemIO::getOption(SystemIO::arrayToOption($list_category,'id','name'),$row['cate_id']));

		joc()->set_var('news_id',$id);
		if($row['image_name'])
			joc()->set_var('img1', $obj->getLink($row['time_created'], $row['image_name']));
		else
			joc()->set_var('img1', 'webskins/icons/100x100.jpg');
		joc()->set_var('title',$row['title']? htmlspecialchars($row['title']) :'');
		joc()->set_var('description',$row['description']? htmlspecialchars($row['description']) :'');

		if($row['continue'] && (!$row['id']))
		{
			joc()->set_var('check-disabled','checked="checked"');
		}
		elseif($row['id'])
			joc()->set_var('check-disabled','disabled="disabled"');
		else
			joc()->set_var('check-disabled','');
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;
	}

}
