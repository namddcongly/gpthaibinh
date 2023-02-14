<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/includes/property_news.php';
require_once 'application/main/includes/user.php';
require_once UTILS_PATH.'paging.php';
require_once UTILS_PATH.'image.upload.php'; 
class AdminNews extends Form
{
	function __construct()
	{
		Form::__construct($this);
	}
	function delete_files($path, $del_dir = FALSE, $level = 0){	
	// Trim the trailing slash
		$path = preg_replace("|^(.+?)/*$|", "\\1", $path);
		if ( ! $current_dir = @opendir($path))	return;
		while(FALSE !== ($filename = @readdir($current_dir))){
			if ($filename != "." and $filename != ".."){
				if (is_dir($path.'/'.$filename)){
					// Ignore empty folders
					if (substr($filename, 0, 1) != '.'){
						delete_files($path.'/'.$filename, $del_dir, $level + 1);
					}
				}else{
					unlink($path.'/'.$filename);
				}
			}
		}
		@closedir($current_dir);
		if ($del_dir == TRUE AND $level > 0){
			@rmdir($path);
		}
	}
	function on_submit()
	{
		require_once UTILS_PATH.'image.resize.php';
		require_once   UTILS_PATH.'convert.php';
		$user_info=UserCurrent::$current->data;	
		$newsObj=new BackendNews();
		$imageResize=new ImageResize();
		$news_id=SystemIO::post('news_id','int',0);
		$from=SystemIO::post('from','def','review');
		$img1='';
		$img2='';
		$img3='';
		$img4='';
		
		if($news_id){
			if($from=='review')
				$row=$newsObj->getReviewOne($news_id);
			elseif($from=='store')
			{
				$row=$newsObj->getStoreOne($news_id);
				$row['content']=$newsObj->getContentOne($news_id);
			}	
		}	
		if(!$row['id']){
			// tạo mới	
			$path_img_upload=NEWS_IMG_UPLOAD.date('Y/n/j',time());
			$path_img_crop='/data/news/'.date('Y/n/j',time());
		}	
		else{
			$path_img_upload=NEWS_IMG_UPLOAD.date('Y/n/j',$row['time_created']);
			$path_img_crop='/data/news/'.date('Y/n/j',$row['time_created']);
		}	
			
		
		if($news_id==0)
			$time_created=time();
		else
			$time_created=$row['time_created'];	
		/*su ly anh thumb cua*/
		/*anh ngang 252x420*/
		if($_FILES['img1']['name']){
			$uploader=new Uploader();
			$uploader->setPath($path_img_upload);
			$uploader->setMaxSize(20000);
			$uploader->setFileType('custom',array('jpg','jpeg','png','gif'));
			$result=$uploader->doUpload('img1');
			$img1=(string)$result['name'];
			/*tạo ảnh kich thuoc 180x300*/
			copy(ROOT_URL.'image.php?weight=225&height=150&image=/'.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_upload).'/'.$result['name'],ROOT_PATH.'data/cnn_225x150/'.date('Y/n/j',$time_created).'/'.$result['name']);
			copy(ROOT_URL.'image.php?weight=135&height=90&image=/'.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_upload).'/'.$result['name'],ROOT_PATH.'data/cnn_135x90/'.date('Y/n/j',$time_created).'/'.$result['name']);
			copy(ROOT_URL.'image.php?weight=306&height=204&image=/'.str_replace($_SERVER['DOCUMENT_ROOT'],'',$path_img_upload).'/'.$result['name'],ROOT_PATH.'data/cnn_306x204/'.date('Y/n/j',$time_created).'/'.$result['name']);
			$this->delete_files(ROOT_PATH.'imagecache');
		}
	
		$title=SystemIO::post('title','def');
		$poll_id=SystemIO::post('poll','int',0);
		$description=SystemIO::post('description','def');
		$tag=SystemIO::post('tag','def');
		$tag=str_replace(array(', ',' ,'),array(',',','),$tag);
		$arr_relate=@$_POST['relate'];
		$list_news_ids='';
		for($i=0;$i < count($arr_relate);++$i)
		{
			$list_news_ids.=$arr_relate[$i].',';
		}
		$list_news_ids=rtrim($list_news_ids,',');
		$author=SystemIO::post('author','def');
		$origin=SystemIO::post('origin','def');
		$content=SystemIO::post('content','def');
		$is_video=SystemIO::post('is_video','int',0);
		$file=SystemIO::post('file','def','');
		$is_img=SystemIO::post('is_img','int',0);		
		$province_id=SystemIO::post('province_id','int');
		/*Dat lich public*/
		$hour_public=SystemIO::post('hour_public','int',0);
		$date_public=SystemIO::post('date_public','str',date('d/m/Y',time()));
		$minutes=SystemIO::post('minutes','int','0');
		$time_public=0;
		if($date_public){
			if( $minutes > 60 || $minutes < 0) $minutes='00';
			$str_time=$hour_public.':'.$minutes.' '.str_replace('/','-',$date_public);
			$time_public=strtotime($str_time);
		}	
		
		$user_id=$user_info['id'];
		$arr_cate_id=SystemIO::post('data','arr');
		$cate_path=',';
		$cate_other='';
		$cate_id=0;
		foreach($arr_cate_id as $cate_ids)
		{
			if((int)$cate_ids && is_numeric($cate_ids))
			{
				$cate_path.=$cate_ids.',';
			}
			elseif(is_array($cate_ids))
			{
				$cate_path.=(int)$cate_ids['0'].',';
				for($n=1; $n < count($cate_ids);++$n)
				{
					$cate_other.=(int)$cate_ids[$n].',';
				}
			}
		}
		$cate_end_id=end($arr_cate_id);
		$cate_id=$cate_end_id['0'];
		/*doan nay de phu dinh doanh cate other doan tren ki xac dinh chinh phu ro rang*/
		
		if($cate_other) $cate_other=','.$cate_other;
		$cate_second_id=SystemIO::post('cate_second_1','int',0);
		$cate_second_level1_id=SystemIO::post('cate_second_level1','int',0);
		if($cate_second_id){
			$cate_other=','.$cate_second_id.',';
			if($cate_second_level1_id) $cate_other.=$cate_second_level1_id.',';	
		}
		/* ket thuc phụ*/
		$content = stripcslashes($content);
		$content=str_replace('http://img-hn.24hstatic.com:8008','http://24h.com.vn',$content);
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
		$arrNewData=array(
				'title'=>$title,
				'description'=>$description,
				'is_video'	=>$is_video,
				'cate_id'	=>$cate_id,
				'cate_path'=>$cate_path,
				'cate_other'=>$cate_other,
				'tag'=>$tag,
				'file'=>$file,
				'relate'=>$list_news_ids,
				'author'=>$author,
				'origin'=>$origin,
				'content'=>$content,
				'user_id'=>$user_id,
				'time_created'=>$time_created,
				'province_id'=>$province_id,
				'is_img'=>$is_img,
				'time_public'=>$time_public,
				'type_post'=>SystemIO::post('type_post','int',0),
				'status'=>SystemIO::post('status','int',0)
		);
		/*kiem tra xem co can cong ly duyet khong?*/
		if($arrNewData['type_post']==2 || $arrNewData['type_post']==3)// bai dich va bai tu viet
		{
			if($arrNewData['status']!=2)// khong con la tin rieng da day len cho duyet
			{
				$arrNewData['status']=3;// 3 bai phai do cho cong ly duyet
			}
		}
		if($img1)
			$arrNewData['img1']=$img1;
		if($img2)
			$arrNewData['img2']=$img2;
		if($img3)
			$arrNewData['img3']=$img3;		
		if($img4)
			$arrNewData['img4']=$img4;	
		
		if($news_id){
			if($from=='store')
			{
				$content=$arrNewData['content'];
				unset($arrNewData['content']);
				$arrNewData['user_id']=$row['user_id'] ? $row['user_id'] : $user_id;
				$arrNewData['editor_id']=$user_id;
				if($row['time_public']){
					if($arrNewData['time_public'] < time()){
						//$arrNewData['time_public']=time();
						$arrNewData['time_public']=$row['time_public'];
					}	
					elseif($arrNewData['time_public'] > time())
						$arrNewData['time_public']=$time_public;
				}	
				else
					$arrNewData['time_public']=$time_public;

				$newsObj->updateData('store',$arrNewData,'id='.$news_id);
				$arr_search=array();
				$arr_search=array(
					'nw_id'			=>$news_id,
					'cate_id'		=>$cate_id,
					'cate_path'		=>$cate_path,
					'keyword'		=>Convert::convertUtf8ToSMS($title.' '.$description.' '.$tag).' '.Convert::convertUtf8ToTelex($title.' '.$description.' '.$tag)
				);
			$newsObj->updateData('search',$arr_search,'nw_id='.$news_id);
				$newsObj->updateData('store_content',array('content'=>$content),'nw_id='.$news_id);
				unset($arrNewData['author']);
				unset($arrNewData['origin']);
				unset($arrNewData['user_id']);
				unset($arrNewData['status']);
				unset($arrNewData['editor_id']);
				unset($arrNewData['type_post']);
				if($province_id)
					$newsObj->updateData('store_province',$arrNewData,'nw_id='.$news_id);
				unset($arrNewData['province_id']);
				
				if($row['type']==1)// tin nay dang ơ trang chu;
				{
					unset($arrNewData['file']);
					$newsObj->updateData('store_home',$arrNewData,'nw_id='.$news_id);
				}
				$newsObj->updateData('store_view',$arrNewData,'nw_id='.$news_id);
				
				//create cache neu sua trong kho
				if($from == "store")
				{
					$cate_id_cache.=trim($row['cate_path'],',');
					if($row['cate_other'] != "")
						$cate_id_cache.=','.trim($row['cate_other'],',');
					
					$cates = $newsObj->getListCategory("id IN($cate_id_cache)");
					if(count($cates) > 0)
					{
						foreach ($cates as $cat)
						{	
							$newsObj->log('http://congly.vn/'.$cat['alias']."/?cached=1",'-1');
							@file_get_contents('http://congly.vn/'.$cat['alias']."/?cached=1");
						}
					}
				}
				
				if($arrNewData['time_public']){
					if($row['type'])
						Url::redirectUrl(array(),'?app=news&page=admin_news&cmd=home');
					else
						Url::redirectUrl(array(),'?app=news&page=admin_news&cmd=news_store');
				}	
				else
					Url::redirectUrl(array(),'?app=news&page=admin_news&cmd=pending_public');	
			}
			else{
				$arrNewData['user_id']=$row['user_id'] ? $row['user_id']: $user_id;
				$arrNewData['editor_id']=$user_id;
				if($newsObj->updateReview($news_id,$arrNewData) >= 0) {					
					$status=SystemIO::post('status','int',0);
					if($status==2)
						Url::redirectUrl(array(),'?app=news&page=admin_news&cmd=news_private');
					else
						Url::redirectUrl(array(),'?app=news&page=admin_news&cmd=pending_censor');	
				}
			}
					
		}	
		else
		{			
			$arrNewData['user_id']=$row['user_id'] ? $row['user_id'] : $user_id;
			$arrNewData['editor_id']=$user_id;
			
			if($last_id=$newsObj->insertReview($arrNewData)) 
			{
				if(SystemIO::post('continue','int'))
				{
					$_SESSION['news_continue']=array('cate_id'=>$cate_id,'cate_path'=>$cate_path,'province_id'=>$province_id,'origin'=>$origin,'author'=>$author,'tag'=>$tag,'continue'=>1);		
					Url::redirectUrl(array(),'?app=news&page=admin_news&cmd=news_create');
				}	
				else
				{
					unset($_SESSION['news_continue']);
					$status=SystemIO::post('status','int',0);
					if($status==2)
						Url::redirectUrl(array(),'?app=news&page=admin_news&cmd=news_private');
					else
						Url::redirectUrl(array(),'?app=news&page=admin_news&cmd=pending_censor');
				}	
			}
		}
	}
	function index()
	{	

		$cmd=SystemIO::get('cmd','str','intro');
		$news_id=SystemIO::get('news_id','int',0);
		switch($cmd)
		{
			case 'home':
				if(!UserCurrent::havePrivilege('NEWS_HOME'))
				{
				    Url::urlDenied();
				}
				return $this->adminHome();
				break;
			case 'news_private':
				return $this->adminPrivate();
				break;	
			case 'pending_public':
				
				$newsObj=new BackendNews();
				$total_home_record=$newsObj->countRecord('store_home');
				if($total_home_record > 200){
					echo '<script type="text/javascript">alert("Số tin trên trang chủ quá nhiều, bạn phải vào xóa để tiếp tục vào tab này!");</script>';
					echo '<script language="javascript">window.location.href="'.ROOT_URL.'?app=news&page=admin_news";</script></head>';
				}
				return $this->adminPendingPublic();
				break;
			case 'pending_censor':
				$newsObj=new BackendNews();
				$total_home_record=$newsObj->countRecord('store_home');
				if($total_home_record > 200){
					echo '<script type="text/javascript">alert("Số tin trên trang chủ quá nhiều, bạn phải vào xóa để tiếp tục vào tab này!");</script>';
					echo '<script language="javascript">window.location.href="'.ROOT_URL.'?app=news&page=admin_news";</script></head>';
				}
				return $this->adminPendingCensor();
				break;
			case 'news_return':
				return $this->adminNewsReturn();
				break;
			case 'news_store':
				$newsObj=new BackendNews();
				$total_home_record=$newsObj->countRecord('store_home');
				if($total_home_record > 200){
					echo '<script type="text/javascript">alert("Số tin trên trang chủ quá nhiều, bạn phải vào xóa để tiếp tục vào tab này!");</script>';
					echo '<script language="javascript">window.location.href="'.ROOT_URL.'?app=news&page=admin_news";</script></head>';
				}
				if(!UserCurrent::havePrivilege('NEWS_STORE'))
				{
				    Url::urlDenied();
				}
				return $this->adminNewsStore();
				break;
			case 'news_create':
				if(!UserCurrent::havePrivilege('NEWS_CREATE'))
				{
				    Url::urlDenied();
				}
				return $this->adminAddAndEdit($news_id);
				break;
			case 'news_write_seo':
				if(!UserCurrent::havePrivilege('WRITE_SEO'))
				{
				    Url::urlDenied();
				}
				return $this->adminWirteSEO();
				break;
			case 'comment':
				if(!UserCurrent::havePrivilege('NEWS_COMMENT'))
				{
				    Url::urlDenied();
				}
				return $this->adminNewsComment();
				break;	
			case 'intro':
				return $this->adminNewsIntro();
				break;
			default:
				return $this->adminNewsIntro();
				break;
		}
	}
	function adminNewsIntro()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/admin_news_intro.htm");
		Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
		$newsObj=new BackendNews();
		$total_home_record=$newsObj->countRecord('store_home');
		$where="time_public >= ".strtotime(date('d-m-Y',time()));
		$home_record_in_day=$newsObj->countRecord('store_home',$where);
		$where="time_public < ".strtotime(date('d-m-Y',time()));
		$where.=" AND time_public > ".(strtotime(date('d-m-Y',time()))-86400);
		$home_record_yesterday=$newsObj->countRecord('store_home',$where);
		joc()->set_var('total_home_record',$total_home_record);
		joc()->set_var('home_record_in_day',$home_record_in_day);
		joc()->set_var('home_record_yesterday',$home_record_yesterday);
		joc()->set_var('total_home_old',$total_home_record-($home_record_in_day+$home_record_yesterday));
		
		$over_record=$total_home_record-100;
		if($over_record < 0)
			$over_limit='Số tin chưa hiển thị đủ trên trang chủ';
		elseif($over_record < 10) 
			$over_limit='Bạn đã vượt quá <strong>'.$over_record.'</strong> tin cho phép hiển thị trên trang chủ';
		else 
			$over_limit='Bạn đã vượt quá <strong>'.$over_record.'</strong> tin cho phép hiển thị trên trang chủ. Bạn phải xóa các tin cũ hoặc các tin không được hiển thị trên trang chủ. Click  <a href="javascript:;" onclick="deleteNewsHome('.$over_record.')">Vào đây</a> để xóa <b>'.$over_record.'</b> tin cũ nhất';
		joc()->set_var('over_limit',$over_limit);		
		if(UserCurrent::havePrivilege('NEWS_VIEW_ALL')){
			$total_pending_public=$newsObj->countRecord('store','time_public=0');
			joc()->set_var('total_pending_public',$total_pending_public);
			$total_pending_censor=$newsObj->countRecord('review','status=0');
			joc()->set_var('total_pending_censor',$total_pending_censor);
			$total_news_return=$newsObj->countRecord('review','status=1');
			joc()->set_var('total_news_return',$total_news_return);
		}
		else
		{
			joc()->set_var('total_pending_public','N/A');
			$total_pending_censor=$newsObj->countRecord('review','status=0 AND user_id='.UserCurrent::$current->data['id']);
			joc()->set_var('total_pending_censor',$total_pending_censor);
			$total_news_return=$newsObj->countRecord('review','status=1 AND user_id='.(int)UserCurrent::$current->data['id']);
			joc()->set_var('total_news_return',$total_news_return);
		}
		joc()->set_var('total_view',$newsObj->countRecord('store_view'));
		joc()->set_var('total_store',$newsObj->countRecord('store'));
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;
	}
	function adminPendingPublic()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/admin_pending_public.htm");
		Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		Page::registerFile('popup-css',	 Module::pathSystemCSS().'popup.css' , 'header', 'css');
		Page::registerFile('popup-js', JAVASCRIPT_PATH.'popup.js' , 'footer', 'js');
		Page::registerFile('jqDnR', JAVASCRIPT_PATH.'jqDnR.js' , 'footer', 'js');
		$newsObj=new BackendNews();
		$userObj=new User();
		/*Tìm kiếm*/
		$list_category=$newsObj->getListCategory('cate_id1=0','',100,'id');
		
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');
		
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$cate_id=SystemIO::get('cate_id','int',0);
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_category,'id','name'),$cate_id));
		$wh='(time_public = 0 OR time_public > '.time().')';
		if($q) $wh.=" AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
		if($cate_id) $wh.=" AND cate_path LIKE '%,{$cate_id},%'";
		if(!UserCurrent::havePrivilege('NEWS_VIEW_ALL'))
			$wh.=' AND user_id='.(int)UserCurrent::$current->data['id'];
		
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
		$censor_name=SystemIO::get('censor_name','def');
		$list_user_id_search='0';
		if($censor_name)
		{
			$_user_id_search= $userObj->userNameToId($censor_name);
			if(count($_user_id_search))
			{
				foreach($_user_id_search as $_temp)
					$list_user_id_search.=','.$_temp['id'];
			}
			
			$wh.=' AND censor_id IN ('.$list_user_id_search.') AND time_public < '.time();
		}
		$list_news=$newsObj->getListStore($wh,'time_created DESC',$limit);
		$news_ids='';
		foreach($list_news as $_temp)
		{
			$cate_ids.=trim($_temp['cate_path'],',').',';
			$censor_id.=$_temp['censor_id'].',';
		}
		$cate_ids=trim($cate_ids,',');
		$censor_id=trim($censor_id,',');
	
		$list_censor_user_name=$userObj->userIdToName($censor_id);

		$list_path_news=$newsObj->getMultiPathNews($cate_ids);
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		global $NEWS_PROPERTY;
		foreach($list_news as $row)
		{
			joc()->set_var('title',$row['title']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('path',$list_path_news[$row['cate_id']]);
			joc()->set_var('time_created',date('H:i d-m-Y',$row['time_created']));
			joc()->set_var('description',$row['description']);
			joc()->set_var('censer_user_name',$list_censor_user_name[$row['censor_id']]['user_name']);
			joc()->set_var('stt',$stt);
			joc()->set_var('tag',$row['tag']);
			joc()->set_var('origin',$row['origin'] ? $row['origin'] : 'N/A');
			$path_img=$newsObj->getPathNews($row['time_created']);
			if($row['img1']) $src=$row['img1'];
			elseif($row['img2']) $src=$row['img2'];
			elseif($row['img3']) $src=$row['img3'];
			else {
				$src='100x100.jpg';
				$path_img='webskins/icons/';
			}
			joc()->set_var('src',IMG::show($path_img,$src));
			$un_property='';
			foreach($NEWS_PROPERTY as $p=>$desc)
			{

				$un_property.='Xuất bản là: <a href="javascript:;" onclick="setProperty('.$row['id'].','.$p.',0,this)">'.$desc.'</a><br/>';
			}
		
			if(UserCurrent::havePrivilege('NEWS_HOME'))
				joc()->set_var('property',$un_property);
			else
				joc()->set_var('property','');
			
			++$stt;
			$bg="#FFF";
			if($row['time_public'] > time())
			{
				$bg="#CCF2D9";				
				joc()->set_var('time_public','Xuất bản lúc: '.date('H:i d/m/y',$row['time_public']));
			}
			else
				joc()->set_var('time_public','');
				
			joc()->set_var('bg',$bg);
			$act_del_edit='Hành động khác: <a href="?app=news&page=admin_news&cmd=news_create&news_id='.$row['id'].'&from=store">Sửa</a> | <a href="javascript:;" onclick="deleteData('.$row['id'].')">Xóa</a> <br/>';
			if((UserCurrent::havePrivilege('NEWS_ACTION_EDIT_DEL_RETURN')==true) OR (UserCurrent::$current->data['id']==$row['user_id']))
				joc()->set_var('act_del_edit',$act_del_edit);
			else
				joc()->set_var('act_del_edit','');
				
			$text_html.=joc()->output('ListRow');
		}
		joc()->set_var('ListRow',$text_html);
		global $TOTAL_ROWCOUNT;
		$totalRecord=$TOTAL_ROWCOUNT;
		joc()->set_var('total_rowcount',$totalRecord);
		joc()->set_var('paging','<li>Tổng số: '.$totalRecord.'</li>'.Paging::paging ($totalRecord,$item_per_page,10));
		/*Setup Property*/
		joc()->set_block('AdminNews','Property','Property');
		
		$text_property='';
		$j=0;
		foreach($NEWS_PROPERTY as $p=>$desc)
		{
			++$j;
			joc()->set_var('property_setup',$j);// set hay huy
			joc()->set_var('property_cancel',++$j);// set hay huy
			joc()->set_var('property_desc',$desc);
			joc()->set_var('property_value',$p);
			$text_property.=joc()->output('Property');
		}
		if(UserCurrent::havePrivilege('NEWS_HOME'))
			joc()->set_var('Property',$text_property);
		else
			joc()->set_var('Property','');
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;
	}
	function adminHome()
	{
		
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/admin_news.htm");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
		$newsObj=new BackendNews();
		$list_category=$newsObj->getListCategory('cate_id2=0','',"0,100",'id');
		global $NEWS_PROPERTY;
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');
		$property=SystemIO::get('property','def','');
		if($property)
			$arr_property=explode(',',$property);
		
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$cate_id=SystemIO::get('cate_id','int',0);
		joc()->set_var('option_category',SystemIO::selectBox($list_category,array($cate_id), "id","id","name",""));
		
		$property_s='';
		foreach($NEWS_PROPERTY as $value=>$desc)
		{
			
			if(@in_array($value,$arr_property))
				$property_s.='<input type="checkbox" checked="checked" value="'.$value.'"  name="property_s"/>'.$desc.'&nbsp;&nbsp;';
			else
				$property_s.='<input type="checkbox"  value="'.$value.'"  name="property_s"/>'.$desc.'&nbsp;&nbsp;';					
		}
		joc()->set_var('property_s',$property_s);
		
		$is_video=SystemIO::get('is_video','int',0);
		if($is_video)
			joc()->set_var('video_check','checked="checked"');
		else
			joc()->set_var('video_check','');	
		
		$wh='1=1';
		
		if($q) $wh.=" AND title LIKE '%{$q}%' OR description LIKE '%{$q}%'";
		if($cate_id)
			$wh.=" AND cate_path LIKE '%,{$cate_id},%'";
		$property_value=0;
		if(count($arr_property))
		{
			$property_value=array_sum($arr_property);
		}
		
		if($property_value)
			$wh.=" AND property & {$property_value}=$property_value";
		
		if($is_video)
			$wh.=" AND is_video = 1";	
		$list_news=$newsObj->getListHome($wh,'arrange ASC,time_public DESC',$limit);
		/*Xoa tin trang chu khi nhieu*/
		$this->autoDelNewsHome($list_category);
		
		$news_ids='';
		$censor_id=',';
		foreach($list_news as $_temp)
		{
			$news_ids.=$_temp['nw_id'].',';
			$cate_ids.=$_temp['cate_id'].',';
			if(!substr_count($censor_id,','.$_temp['censor_id'].','))
				$censor_id.=$_temp['censor_id'].',';
		}
		$cate_ids=trim($cate_ids,',');
		$news_ids=trim($news_ids,',');
		$censor_id=trim($censor_id,',');
		$userObj=new User();
		$list_censor_user_name=$userObj->userIdToName($censor_id);
		$list_news_hit=$newsObj->getListNewsHit($news_ids);
		$list_path_news=$newsObj->getMultiPathNews($cate_ids);
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		
		foreach($list_news as $row)
		{
			$bg="#FFF";
			if($row['time_public']  > time()) $bg='#CCF2D9';
			joc()->set_var('bg',$bg);
			joc()->set_var('title',$row['title']);
			joc()->set_var('nw_id',$row['nw_id']);
			joc()->set_var('path',$list_path_news[$row['cate_id']]);
			joc()->set_var('time_public',date('H:i d-m-Y',$row['time_public']));
			joc()->set_var('description',$row['description']);
			joc()->set_var('censer_user_name',$list_censor_user_name[$row['censor_id']]['user_name']);
			joc()->set_var('arrange',$row['arrange']);
			joc()->set_var('stt',$stt);
			joc()->set_var('id',$row['id']);
			joc()->set_var('tag',$row['tag']);
			$path_img=$newsObj->getPathNews($row['time_created']);
			if($row['img1']) $src=$row['img1'];
			elseif($row['img2']) $src=$row['img2'];
			elseif($row['img3']) $src=$row['img3'];
			else {
				$src='100x100.jpg';
				$path_img='webskins/icons/';
			}
			joc()->set_var('src',IMG::show($path_img,$src));
			$property='';
			$un_property='';
			foreach($NEWS_PROPERTY as $p=>$desc)
			{
			    if($p == 1)
			    {
			        if(UserCurrent::havePrivilege('HOME_FOCUS'))
			        {
        				if($row['property'] & $p) $property.='Bỏ thiết lập là: <a style="color:#990000" href="javascript:;" onclick="setProperty('.$row['nw_id'].',0,'.$p.')">'.$desc.'</a><br/>';
        				else $un_property.='Thiết lập là: <a href="javascript:;" onclick="setProperty('.$row['nw_id'].','.$p.',0)">'.$desc.'</a><br/>';
			        }
			        else
			        {
			        	if($row['property'] & $p) $property.='Bỏ thiết lập là: <a style="color:#990000" href="javascript:;">'.$desc.'</a><br/>';
        				else $un_property.='Thiết lập là: <a href="javascript:;" >'.$desc.'</a><br/>';
			        }
			    }
			    else 
			    {
			        if(UserCurrent::havePrivilege('CATE_FOCUS'))
			        {
        				if($row['property'] & $p) $property.='Bỏ thiết lập là: <a style="color:#990000" href="javascript:;" onclick="setProperty('.$row['nw_id'].',0,'.$p.')">'.$desc.'</a><br/>';
        				else $un_property.='Thiết lập là: <a href="javascript:;" onclick="setProperty('.$row['nw_id'].','.$p.',0)">'.$desc.'</a><br/>';
			        }
			        else
			        {
			        	if($row['property'] & $p) $property.='Bỏ thiết lập là: <a style="color:#990000" href="javascript:;" >'.$desc.'</a><br/>';
        				else $un_property.='Thiết lập là: <a href="javascript:;" >'.$desc.'</a><br/>';
			        }			        
			    }
			}
			joc()->set_var('hit',(int)$list_news_hit[$row['nw_id']]['hit']);
			joc()->set_var('property',$property.$un_property);
			++$stt;
			$text_html.=joc()->output('ListRow');
		}
		joc()->set_var('ListRow',$text_html);
		global $TOTAL_ROWCOUNT;
		$totalRecord=$TOTAL_ROWCOUNT;
		joc()->set_var('paging','<li>Tổng số: '.$totalRecord.'</li>'.Paging::paging ($totalRecord,$item_per_page,10));
		/*Setup Property*/
		joc()->set_block('AdminNews','Property','Property');
		
		$text_property='';
		$j=0;
		foreach($NEWS_PROPERTY as $p=>$desc)
		{
			++$j;
			joc()->set_var('property_setup',$j);// set hay huy
			joc()->set_var('property_cancel',++$j);// set hay huy
			joc()->set_var('property_desc',$desc);
			joc()->set_var('property_value',$p);
			$text_property.=joc()->output('Property');
		}
		joc()->set_var('Property',$text_property);
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;
	
	}
	function adminPendingCensor()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/admin_pending_censor.htm");
		Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		Page::registerFile('popup-css',	 Module::pathSystemCSS().'popup.css' , 'header', 'css');
		Page::registerFile('popup-js', JAVASCRIPT_PATH.'popup.js' , 'footer', 'js');
		Page::registerFile('jqDnR', JAVASCRIPT_PATH.'jqDnR.js' , 'footer', 'js');
		$newsObj=new BackendNews();
		
		
		$userObj=new User();
		/*Tìm kiếm*/
		$list_category=$newsObj->getListCategory('cate_id1=0','',100,'id');
		
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');
		
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$cate_id=SystemIO::get('cate_id','int',0);
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_category,'id','name'),$cate_id));
		$wh='(status = 0 OR status = 3 OR status is NULL)';
		if(!UserCurrent::havePrivilege('NEWS_VIEW_ALL'))
		$wh.=' AND user_id='.(int)UserCurrent::$current->data['id'];
		if($q) $wh.=" AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
		if($cate_id) $wh.=" AND cate_path LIKE '%,{$cate_id},%'";
		
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
		$user_name=SystemIO::get('user_name','def');
		joc()->set_var('user_name',$user_name);
		$list_user_id_search='0';
		if($user_name)
		{
			$_user_id_search= $userObj->userNameToId($user_name);
			if(count($_user_id_search))
			{
				foreach($_user_id_search as $_temp)
					$list_user_id_search.=','.$_temp['id'];
			}
			
			$wh.=' AND user_id IN ('.$list_user_id_search.')';
		}
		$list_news=$newsObj->getListReview($wh,'id desc',$limit);
		$news_ids='';
		$user_id='';
		$cate_ids='';
		$editor_id='';
		foreach($list_news as $_temp)
		{
			$cate_ids.=$_temp['cate_id'].',';
			$user_id.=$_temp['user_id'].',';
			if((int)$_temp['editor_id'])
				$editor_id.=$_temp['editor_id'].',';
			
		}
		$cate_ids=trim($cate_ids,',');
		$user_id=trim($user_id,',');
		$editor_id=trim($editor_id,',');
		if($editor_id)
			$user_id.=','.$editor_id;
		$list_censor_user_name=$userObj->userIdToName($user_id);
		
		$list_path_news=$newsObj->getMultiPathNews($cate_ids);
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		global $NEWS_PROPERTY;
		foreach($list_news as $row)
		{
			$bg="#FFF";
			if($row['time_public']  > time()) $bg='#CCF2D9';
			joc()->set_var('title',$row['title']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('path',$list_path_news[$row['cate_id']]);
			joc()->set_var('reason_return',$row['reason_return'] ? '<p>Lý do trả: '.$row['reason_return'].'</p>' : '');
			joc()->set_var('time_created',date('H:i d-m-Y',$row['time_created']));
			joc()->set_var('description',$row['description']);
			joc()->set_var('censer_user_name',$list_censor_user_name[$row['user_id']]['user_name']);
			joc()->set_var('edit_user_name',$list_censor_user_name[$row['editor_id']]['user_name'] ? $list_censor_user_name[$row['editor_id']]['user_name'] :'N/A');
			joc()->set_var('stt',$stt);
			joc()->set_var('tag',$row['tag'] ? $row['tag'] : 'N/A');
			if($row['time_public'])
				joc()->set_var('time_public','Xuất bản lúc: '.date('H:i d/n/y',$row['time_public']));
			else
				joc()->set_var('time_public','');	
			
			$path_img=$newsObj->getPathNews($row['time_created']);
			if($row['img1']) $src=$row['img1'];
			elseif($row['img2']) $src=$row['img2'];
			elseif($row['img3']) $src=$row['img3'];
			else {
				$src='100x100.jpg';
				$path_img='webskins/icons/';
			}
			joc()->set_var('src',IMG::show($path_img,$src));
			joc()->set_var('origin',$row['origin'] ? $row['origin']: 'N/A');
			$un_property='';
			foreach($NEWS_PROPERTY as $p=>$desc)
			{
				$un_property.='Duyệt tin là: <a href="javascript:;" onclick="setProperty('.$row['id'].','.$p.',0,this)">'.$desc.'</a><br/>';
			}
			if(UserCurrent::havePrivilege('NEWS_HOME'))
				joc()->set_var('property',$un_property);
			else
				joc()->set_var('property','');
			
			
				
			$function='';
			if(UserCurrent::havePrivilege('NEWS_ACTION_RETURN'))
				$function.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].')">Trả về</a><br />';
			if(UserCurrent::havePrivilege('NEWS_ACTION_CENSOR'))
				$function.='<a href="javascript:;" onclick="doCensor('.$row['id'].',0,this)">Lên chờ xuất bản</a><br/>';
			if(UserCurrent::havePrivilege('NEWS_ACTION_PUBLIC_CENSOR'))
				$function.='<a href="javascript:;" onclick="doCensor('.$row['id'].',1)">Tin thường mục</a><br/>';
			joc()->set_var('function',$function);
			++$stt;
			
			$act_del_edit='Hành động khác: <a href="?app=news&page=admin_news&cmd=news_create&news_id='.$row['id'].'">Sửa</a> | <a href="javascript:;" onclick="delData('.$row['id'].')">Xóa</a> <br/>';
			if((UserCurrent::havePrivilege('NEWS_ACTION_EDIT_DEL_RETURN')==true) OR (UserCurrent::$current->data['id']==$row['user_id']))
				joc()->set_var('act_del_edit',$act_del_edit);
			else
				joc()->set_var('act_del_edit','');
				
			if($row['status']==0)
				joc()->set_var('congly_censor','');
			elseif($row['status']==3){
				$bg="#CCF2D9";
				joc()->set_var('congly_censor','<font color="#990000">Đang chờ Công lý duyệt...</font><br/>');
			}
			joc()->set_var('bg',$bg);		
			$text_html.=joc()->output('ListRow');
		}
		joc()->set_var('ListRow',$text_html);
		global $TOTAL_ROWCOUNT;
		$totalRecord=$TOTAL_ROWCOUNT;
		joc()->set_var('total_rowcount',$totalRecord);
		joc()->set_var('paging','<li>Tổng số: '.$totalRecord.'</li>'.Paging::paging ($totalRecord,$item_per_page,10));
		/*Setup Property*/
		joc()->set_block('AdminNews','Property','Property');
		
		$text_property='';
		$j=0;
		foreach($NEWS_PROPERTY as $p=>$desc)
		{
			++$j;
			joc()->set_var('property_setup',$j);// set hay huy
			joc()->set_var('property_cancel',++$j);// set hay huy
			joc()->set_var('property_desc',$desc);
			joc()->set_var('property_value',$p);
			$text_property.=joc()->output('Property');
		}
		if(UserCurrent::havePrivilege('NEWS_HOME'))
			joc()->set_var('Property',$text_property);
		else
			joc()->set_var('Property','');
		$f_censor_all='';
		if(!UserCurrent::havePrivilege('NEWS_ACTION_CENSOR'))
			$f_censor_all='disabled="disabled"';
		joc()->set_var('f_censor_all',$f_censor_all);
		
		$f_censor_public_all='';
		if(!UserCurrent::havePrivilege('NEWS_ACTION_PUBLIC_CENSOR'))
			$f_censor_public_all='disabled="disabled"';
		joc()->set_var('f_censor_public_all',$f_censor_public_all);
		
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;
	}
	function adminNewsReturn()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/admin_news_return.htm");
		Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		Page::registerFile('popup-css',	 Module::pathSystemCSS().'popup.css' , 'header', 'css');
		Page::registerFile('popup-js', JAVASCRIPT_PATH.'popup.js' , 'footer', 'js');
		Page::registerFile('jqDnR', JAVASCRIPT_PATH.'jqDnR.js' , 'footer', 'js');
		$newsObj=new BackendNews();
		$userObj=new User();
		$list_category=$newsObj->getListCategory('cate_id1=0','',100,'id');
		
		$item_per_page=50;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');
		
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$cate_id=SystemIO::get('cate_id','int',0);
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_category,'id','name'),$cate_id));
		$wh='status = 1';
		if(!UserCurrent::havePrivilege('NEWS_VIEW_ALL'))
			$wh.=' AND user_id='.(int)UserCurrent::$current->data['id'];
		if($q) $wh.=" AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
		if($cate_id) $wh.=" AND cate_path LIKE '%,{$cate_id},%'";
		
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
		$list_news=$newsObj->getListReview($wh,'',$limit);
		$news_ids='';
		$censor_id=',';
		$user_ids='';
		foreach($list_news as $_temp)
		{
			$cate_ids.=$_temp['cate_id'].',';
			if(!substr_count($censor_id,','.$_temp['censor_id'].','))
				$censor_id.=(int)$_temp['censor_id'].',';
			if(!substr_count($user_ids,','.$_temp['user_id'].','))	
				$user_ids.=(int)$_temp['user_id'].',';	
				
		}
		$cate_ids=trim($cate_ids,',');
		$censor_id=trim($censor_id,',');
		$user_ids=trim($user_ids,',');
		if(strlen($user_ids))
			$list_censor_user_name=$userObj->userIdToName($censor_id.','.$user_ids);
		$list_path_news=$newsObj->getMultiPathNews($cate_ids);
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		global $NEWS_PROPERTY;
		foreach($list_news as $row)
		{
			joc()->set_var('title',$row['title']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('path',$list_path_news[$row['cate_id']]);
			joc()->set_var('time_created',date('H:i d-n-Y',$row['time_created']));
			joc()->set_var('description',$row['description']);
			joc()->set_var('reason',nl2br($row['reason_return']));
			joc()->set_var('creator',$list_censor_user_name[$row['user_id']]['user_name']);
			joc()->set_var('censor',$list_censor_user_name[$row['censor_id']]['user_name']? $list_censor_user_name[$row['censor_id']]['user_name']:'N/A');			
			joc()->set_var('stt',$stt);
			joc()->set_var('tag',$row['tag'] ? $row['tag'] : 'N/A');
			$path_img=$newsObj->getPathNews($row['time_created']);
			if($row['img1']) $src=$row['img1'];
			elseif($row['img2']) $src=$row['img2'];
			elseif($row['img3']) $src=$row['img3'];
			else {
				$src='100x100.jpg';
				$path_img='webskins/icons/';
			}
			joc()->set_var('src',IMG::show($path_img,$src));
			++$stt;
			$text_html.=joc()->output('ListRow');
		}
		joc()->set_var('ListRow',$text_html);
		global $TOTAL_ROWCOUNT;
		$totalRecord=$TOTAL_ROWCOUNT;
		joc()->set_var('total_rowcount',$totalRecord);
		joc()->set_var('paging','<li>Tổng số: '.$totalRecord.'</li>'.Paging::paging ($totalRecord,$item_per_page,10));
		/*Setup Property*/

		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;
	}
	function adminNewsStore()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/admin_news_store.htm");
		Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		Page::registerFile('popup-css',	 Module::pathSystemCSS().'popup.css' , 'header', 'css');
		Page::registerFile('popup-js', JAVASCRIPT_PATH.'popup.js' , 'footer', 'js');
		Page::registerFile('jqDnR', JAVASCRIPT_PATH.'jqDnR.js' , 'footer', 'js');
		$newsObj=new BackendNews();
		$userObj=new User();
		/*Tìm kiếm*/
		$list_category=$newsObj->getListCategory('cate_id1=0','',100,'id');
		$list_category1=$newsObj->getListCategory('cate_id1 > 0 AND cate_id2=0','',100,'id');
		
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		$filter_id=SystemIO::get('filter_id','int',0);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');
		
		joc()->set_var('q',$q);
		$label_s=substr($q,0,7);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$cate_id=SystemIO::get('cate_id','int',0);
		$cate_id_2=SystemIO::get('cate_id_2','int',0);
		
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_category,'id','name'),$cate_id));
		joc()->set_var('option_category1',SystemIO::getOption(SystemIO::arrayToOption($list_category1,'id','name'),$cate_id_2));
		$wh='time_public > 0 AND time_public < '.time();
		if($q) {
			if($label_s=='origin:' || $label_s=='Origin:' || $label_s=='ORIGIN:')
				$wh.=" AND origin LIKE '%".substr($q,7,strlen($q))."%'";
			else
				$wh.=" AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";	
		}
		if($cate_id_2) 
			$wh.=" AND cate_path LIKE '%,{$cate_id_2},%'";
		if($cate_id) $wh.=" AND cate_path LIKE '%,{$cate_id},%'";
		
		if($filter_id) $wh.=' AND cate_id='.$filter_id;	
		
		$date_begin=SystemIO::get('date_begin','def');
		joc()->set_var('date_begin',$date_begin);
		if($date_begin)
		{
			$date_begin=strtotime(str_replace('/','-',$date_begin));
			$wh.= " AND time_public >= {$date_begin}";
			
		}
		
		$date_end=SystemIO::get('date_end','def');
		joc()->set_var('date_end',$date_end);
		if($date_end)
		{
			$date_end=strtotime(str_replace('/','-',$date_end));
			$date_end+=86399;
			$wh.= " AND time_public <= {$date_end}";
		}	
		$censor_name=SystemIO::get('censor_name','def');
		joc()->set_var('censor_name',$censor_name);
		$list_user_id_search='0';
		if($censor_name)
		{
			$_user_id_search= $userObj->userNameToId($censor_name);
			if(count($_user_id_search))
			{
				foreach($_user_id_search as $_temp)
					$list_user_id_search.=','.$_temp['id'];
			}
			$wh.=' AND censor_id IN ('.$list_user_id_search.')';
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
		$list_news=$newsObj->getListStore($wh,'time_public desc',$limit);
		$news_ids='';
		$cate_ids='';
		$user_ids=',';
		$censor_ids=',';
		$editor_id=',';
		$news_ids_home='';
		foreach($list_news as $_temp)
		{
			$cate_ids.=$_temp['cate_id'].',';
			$news_ids.=$_temp['id'].',';
			if(!substr_count($censor_ids,','.$_temp['censor_id'].','))
				$censor_ids.=$_temp['censor_id'].',';
			if(!substr_count($user_ids,','.$_temp['user_id'].','))	
				$user_ids.=$_temp['user_id'].',';
			if(!substr_count($editor_id,','.$_temp['editor_id'].','))	
				$editor_id.=(int)$_temp['editor_id'].',';
			if($_temp['type']==1)
				$news_ids_home.=$_temp['id'].',';		
		}
		$news_ids_home=rtrim($news_ids_home,',');
		$cate_ids=trim($cate_ids,',');
		$censor_ids=trim($censor_ids,',');
		$user_ids=trim($user_ids,',');
		$editor_id=trim($editor_id,',');
		$news_ids=trim($news_ids,',');
		$list_news_hit=$newsObj->getListNewsHit($news_ids);
		if($editor_id)
			$user_ids.=','.$editor_id;
		if($censor_ids && $user_ids)
			$list_censor_user_name_and_name_btv=$userObj->userIdToName($censor_ids.','.$user_ids);
		
		
		if($news_ids_home)
			$list_home=$newsObj->getListData('store_home','nw_id,property','nw_id IN('.$news_ids_home.')',null,'100','nw_id',false);
	
		$list_path_news=$newsObj->getMultiPathNews($cate_ids);
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		global $NEWS_PROPERTY;
		foreach($list_news as $row)
		{
			joc()->set_var('title',$row['title']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('path',$list_path_news[$row['cate_id']]);
			joc()->set_var('time_created',date('H:i d-m-Y',$row['time_created']));
			joc()->set_var('description',$row['description']);
			joc()->set_var('censer_user_name',$list_censor_user_name_and_name_btv[$row['censor_id']]['user_name']);
			joc()->set_var('name_btv',$list_censor_user_name_and_name_btv[$row['user_id']]['user_name']);
			joc()->set_var('name_edit',$list_censor_user_name_and_name_btv[$row['editor_id']]['user_name']);
			joc()->set_var('stt',$stt);
			joc()->set_var('tag',$row['tag'] ? $row['tag'] : 'N/A');
			joc()->set_var('time_public',date('H:i d-m-Y',$row['time_public']));
			joc()->set_var('href','http://congly.vn/?app=news&page=detail&id='.$row['id']);
			joc()->set_var('hit',(int)$list_news_hit[$row['id']]['hit']);
			$path_img=$newsObj->getPathNews($row['time_created']);
			if($row['img1']) $src=$row['img1'];
			elseif($row['img2']) $src=$row['img2'];
			elseif($row['img3']) $src=$row['img3'];
			else {
				$src='100x100.jpg';
				$path_img='webskins/icons/';
			}
			joc()->set_var('src',IMG::show($path_img,$src));
			
			joc()->set_var('origin',$row['origin']);
			$property='';
			$property_focus='';
			foreach($NEWS_PROPERTY as $p=>$desc)
			{
				if($p==1 && UserCurrent::havePrivilege('HOME_FOCUS'))
				{
					$property_focus.='Sét: <a href="javascript:;" onclick="setHome('.$row['id'].','.$p.',this)">'.$desc.'</a><br/>';
				}	
				else
				{
					if(UserCurrent::havePrivilege('SET_HOME'))
						$property.='Sét: <a href="javascript:;" onclick="setHome('.$row['id'].','.$p.',this)">'.$desc.'</a><br/>';
				}	
			}
			$title_pos='';
			if($row['type']==1) {
				$title_pos='Tin được xuất bản là tin hiển thị trang chủ';
				$bg="#EBBFF2";
				if($list_home[$row['id']]['property'] & NEWS_FEATURED){
					$title_pos=" Tin được xuất bản là tin NỔI BẬT TRANG CHỦ";
					$bg="#D912F6";
				}
				if($list_home[$row['id']]['property'] & NEWS_FEATURED_CATE)
				{
					$title_pos.=' Tin được xuất bản là tin NỔI BẬT MỤC TRANG CHỦ';
					$bg="#EBBFF2";
				}				
			}
			else {
				$bg="#FFF";
				$title_pos="Tin được xuất bản là tin thông thường";
			}
			if(UserCurrent::havePrivilege('NEWS_ACTION_EDIT_DEL_RETURN'))
				joc()->set_var('action_store','<a href="javascript:;" onclick="getId('.$row['id'].')" rel="reason-return" class="show-list">Về tin chờ duyệt</a> | <a href="?app=news&page=admin_news&cmd=news_create&news_id='.$row['id'].'&from=store">Sửa</a>| <a href="javascript:;" onclick="deleteData('.$row['id'].')">Xóa bài</a><br/>');
			else
				joc()->set_var('action_store','N/A');
			if(UserCurrent::havePrivilege('NEWS_ACTION_REFRESH'))
			{
				$action_refresh='<a href="javascript:;" onclick="newsRefresh(\''.$row['id'].'\')">Làm mới tin</a><br/><a href="javascript:;" onclick="newsSetTimePublic(\''.$row['id'].'\')">Làm cũ tin<a>';
			}
			else
				$action_refresh='';
			joc()->set_var('action_refresh',$action_refresh);	
			joc()->set_var('title_pos',$title_pos);
			joc()->set_var('bg',$bg);
			joc()->set_var('property',$property_focus.$property);
			++$stt;
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
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/admin_add_edit.htm");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('ckeditor.js'	, 'webskins/richtext/ckeditor/ckeditor.js', 'footer', 'js');			
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('thickbox.js'	, Module::pathSystemJS().'thickbox.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		Page::registerFile('thickbox css'		 , Module::pathSystemCSS().'thickbox.css' , 'header', 'css');		
		
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		
		Page::registerFile('jquery.adapter.js' , 'webskins/richtext/ckeditor/adapters/jquery.adapter.js', 'footer', 'js');	
		Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
		//Page::registerFile('admin_news.js', Module::pathJS().'admin_news.js' , 'footer', 'js');	
		joc()->set_var('begin_form' , Form::begin(false, "POST", ' enctype="multipart/form-data" onsubmit="return checkForm()"'));
		joc()->set_var('end_form' 	, Form::end());
		$newsObj=new BackendNews();
		$from=SystemIO::get('from','def','review');
		joc()->set_var('from',$from);
		$row=$_SESSION['news_continue'];
		if($id){
			if($from=='review'){
				$row=$newsObj->getReviewOne($id);
				//$row_seo=$newsObj->readSeo($id);
			}	
			elseif($from=='store'){
				$row=$newsObj->getStoreOne($id);
				$row['content']=$newsObj->getContentOne($id);
				//$row_seo=$newsObj->readSeo($id);
			}		
		}
//		print_r($row_seo);
//		die;
		$list_category=$newsObj->getListCategory('','id ASC');
		$arr_cate1=array();
		$arr_cate2=array();
		$arr_cate3=array();
		$arr_cate4=array();
		$arr_cate5=array();
		foreach($list_category as $_temp)
		{
			if($_temp['cate_id4']) $arr_cate5[$_temp['id']]=$_temp['name'];
			elseif($_temp['cate_id3'])$arr_cate4[$_temp['id']]=$_temp['name'];
			elseif($_temp['cate_id2'])$arr_cate3[$_temp['id']]=$_temp['name'];
			elseif($_temp['cate_id1']){
				$arr_cate2[$_temp['id']]=$_temp['name'];
			}
			else
				$arr_cate1[$_temp['id']]=$_temp;
		}

		$arr_cate_id=explode(',',trim($row['cate_path'],','));
		
		for($i=0; $i<= count($arr_cate_id);++$i)
		{
			$row['cate_id'.($i+1)]=$arr_cate_id[$i];
		}
		
		$str_cate_selected=trim($row['cate_path'],',');
		if($row['cate_other'])$str_cate_selected.=','.trim($row['cate_other'],','); 
		
		$arraySelected=explode(',',$str_cate_selected);
		//joc()->set_var('option_cate1',SystemIO::getMutileOption($arr_cate1,$arraySelected));
		joc()->set_var('option_cate1', SystemIO::selectBox($arr_cate1, $arraySelected,"id","id","name"));
		for($k=2;$k <= count($arr_cate_id); ++$k)
		{
			$arr_cate="arr_cate".$k;
			if($row['cate_id'.$k]){
				joc()->set_var('option_cate'.($k),'<select  id="cate'.($k).'" name="data[cate_id'.($k).'][]" multiple="multiple" style="height:100px;"><option >Chọn danh mục cấp '.($k).'</option>'.SystemIO::getMutileOption($$arr_cate,$arraySelected).'</select>');// 2 day $$ la dung
			}	
			else
				joc()->set_var('option_cate'.$k,'');
				
		}
		for($l=count($arr_cate_id)+1; $l < 5;++$l)
		{
			joc()->set_var('option_cate'.$l,'');
		}
		//if($id==0)
		joc()->set_var('option_cate2','<select  id="cate2" name="data[cate_id2][]" multiple="multiple" style="height:100px;"><option>Chọn danh mục cấp 2</option>'.SystemIO::getOption($arr_cate2,(int)$row['cate_id2']).'</select>');		
		/*load cate_other co xac dinh chinh phu ro rang*/
		if($row['cate_other'])
		{
			$arr_cate_other=explode(',',trim($row['cate_other'],','));
		}
		joc()->set_var('option_cate11',SystemIO::getOption(SystemIO::arrayToOption($arr_cate1,'id','name'),(int)$arr_cate_other['0']));
		joc()->set_var('option_cate12',SystemIO::getOption($arr_cate2,(int)$arr_cate_other['1']));
		
		joc()->set_var('news_id',$id);
		if($row['relate'])
			$list_relate=$newsObj->getListStore('id IN ('.$row['relate'].')');
		$text_relate='';
		if(count($list_relate))
		{
			foreach($list_relate as $relate)
			{
				$text_relate.='<li style="margin-left:150px;"><input type="checkbox" value="'.$relate['id'].'" name="relate[]" checked="checked"/>'.$relate['title'].'</li>';	
			}
		}
		joc()->set_var('text_relate',$text_relate);		
		
		joc()->set_var('title',$row['title']? htmlspecialchars($row['title']) :'');
		joc()->set_var('description',$row['description']? $row['description'] :'');
		joc()->set_var('content',$row['content']? $row['content'] :'');
		joc()->set_var('author',$row['author']? $row['author'] :'');
		joc()->set_var('tag',$row['tag']? $row['tag'] :'');
		joc()->set_var('origin',$row['origin']? $row['origin'] :'');
		joc()->set_var('file',$row['file']);
		joc()->set_var('poll',$row['poll_id']);
		joc()->set_var('is_video',$row['is_video'] ? 'checked="checked"' : "");
		joc()->set_var('title_seo',$row_seo['title']);
		joc()->set_var('description_seo',$row_seo['description']);		
		joc()->set_var('img1',$row['img1'] ? '<img src="'.IMG::show($newsObj->getPathNews($row['time_created']),$row['img1']).'" width="100px;" />':'');
		joc()->set_var('img2',$row['img2'] ? '<img src="'.IMG::show($newsObj->getPathNews($row['time_created']),$row['img2']).'" width="100px;" />':'');
		joc()->set_var('img3',$row['img3'] ? '<img src="'.IMG::show($newsObj->getPathNews($row['time_created']),$row['img3']).'" width="100px;" />':'');
		joc()->set_var('img4',$row['img4'] ? '<img src="'.IMG::show($newsObj->getPathNews($row['time_created']),$row['img4']).'" width="100px;" />':'');
		joc()->set_var('img5',$row['img5'] ? '<img src="'.IMG::show($newsObj->getPathNews($row['time_created']),$row['img5']).'" width="100px;" />':'');
		
		if($row['continue'] && (!$row['id']))
		{
			joc()->set_var('check-disabled','checked="checked"');
		}
		elseif($row['id'])
			joc()->set_var('check-disabled','disabled="disabled"');
		else
			joc()->set_var('check-disabled','');
		$date_public='';
		$hour_public='25';
		$minutes='00';
		if($row['time_public'] > time())
		{
			$hour_public=(int)date('H',$row['time_public']);
			$date_public=date('d/m/Y',$row['time_public']);
			$minutes=date('i',$row['time_public']);
		}
		$arr_hour=array('0'=>0,'1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,'10'=>10,'11'=>11,'12'=>12,'13'=>13,'14'=>14,'15'=>15,'16'=>16,'17'=>17,'18'=>18,'19'=>19,'20'=>20,'21'=>21,'22'=>22,'23'=>23);
		
		joc()->set_var('option_hour',SystemIO::getOption($arr_hour,$hour_public));
		joc()->set_var('date_public',$date_public);
		joc()->set_var('minutes',$minutes);
		$a_type_post=array('0'=>'Bài sưu tầm','1'=>'Thông tấn xã','2'=>'Dịch','3'=>'Tự viết','4'=>'Tin tổng hợp');
		$type_post='';
		foreach($a_type_post as $k=>$v)
		{
			if($k==(int)$row['type_post'])
				$type_post.='<input type="radio" name="type_post" value="'.$k.'" checked="checked"> '.$v.'&nbsp;';
			else
				$type_post.='<input type="radio" name="type_post" value="'.$k.'"> '.$v.'&nbsp;';	
			
		}
		joc()->set_var('type_post',$type_post);
		
		if($row['is_img'])
		{
			joc()->set_var('is_img','checked="checked"');
		}
		else
			joc()->set_var('is_img','');
			
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;	
	}
	function adminPrivate()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/admin_private.htm");
		Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		Page::registerFile('popup-css',	 Module::pathSystemCSS().'popup.css' , 'header', 'css');
		Page::registerFile('popup-js', JAVASCRIPT_PATH.'popup.js' , 'footer', 'js');
		Page::registerFile('jqDnR', JAVASCRIPT_PATH.'jqDnR.js' , 'footer', 'js');
		$newsObj=new BackendNews();
		$userObj=new User();
		/*Tìm kiếm*/
		$list_category=$newsObj->getListCategory('cate_id1=0','',100,'id');
		
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');
		
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$cate_id=SystemIO::get('cate_id','int',0);
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_category,'id','name'),$cate_id));
		$wh='status=2';
		if(UserCurrent::$current->data['user_name']!='namdd')
			$wh.=' AND user_id='.(int)UserCurrent::$current->data['id'];
		if($q) $wh.=" AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
		if($cate_id) $wh.=" AND cate_path LIKE '%,{$cate_id},%'";
		
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
		$list_news=$newsObj->getListReview($wh,'id desc',$limit);
		$news_ids='';
		$user_id='';
		$cate_ids='';
		$editor_id='';
		foreach($list_news as $_temp)
		{
			$cate_ids.=$_temp['cate_id'].',';
				$user_id.=$_temp['user_id'].',';
			if((int)$_temp['editor_id'])
				$editor_id.=$_temp['editor_id'].',';
			
		}
		$cate_ids=trim($cate_ids,',');
		$user_id=trim($user_id,',');
		$editor_id=trim($editor_id,',');
		if($editor_id)
			$user_id.=','.$editor_id;
		$list_censor_user_name=$userObj->userIdToName($user_id);
		
		$list_path_news=$newsObj->getMultiPathNews($cate_ids);
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		global $NEWS_PROPERTY;
		foreach($list_news as $row)
		{
			joc()->set_var('title',$row['title']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('path',$list_path_news[$row['cate_id']]);
			joc()->set_var('time_created',date('H:i d-m-Y',$row['time_created']));
			joc()->set_var('description',$row['description']);
			joc()->set_var('censer_user_name',$list_censor_user_name[$row['user_id']]['user_name']);
			joc()->set_var('edit_user_name',$list_censor_user_name[$row['editor_id']]['user_name'] ? $list_censor_user_name[$row['editor_id']]['user_name'] :'N/A');
			joc()->set_var('stt',$stt);
			joc()->set_var('tag',$row['tag'] ? $row['tag'] : 'N/A');
			if($row['time_public'])
				joc()->set_var('time_public','Xuất bản lúc: '.date('H:i d/n/y',$row['time_public']));
			else
				joc()->set_var('time_public','');	
			
			$path_img=$newsObj->getPathNews($row['time_created']);
			if($row['img1']) $src=$row['img1'];
			elseif($row['img2']) $src=$row['img2'];
			elseif($row['img3']) $src=$row['img3'];
			else {
				$src='100x100.jpg';
				$path_img='webskins/icons/';
			}
			joc()->set_var('src',IMG::show($path_img,$src));
			joc()->set_var('origin',$row['origin'] ? $row['origin']: 'N/A');
			++$stt;
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
	function autoDelNewsHome($list_category)
	{
		/** 1=> Hau truong,5=>Dep,7=>Dien anh,8=>Am nhac,9=>hoa hau,10=>The thao,13=>ki quac,15=>thu gian,12=> theo dong su kien,14=>Ngam,11=>gioi tre,16=>ket noi*/
		$arr_cate_home=array('1'=>3,'5'=>5,'7'=>4,'8'=>4,'9'=>4,'10'=>4,'13'=>4,'15'=>3,'12'=>4,'14'=>3,'11'=>4,'16'=>4,'55'=>4,'56'=>4,'57'=>4,'58'=>4,'59'=>4,'66'=>6);
		$newsObj=new BackendNews();
		$arr_cate_news=array();
		$total_record=array_sum($arr_cate_home);
		
		$list_news=$newsObj->getListData('store_home','nw_id,cate_id,time_public,property','property & '.NEWS_FEATURED.'!='.NEWS_FEATURED.' AND time_public < '.time(),'','0,150','',false);
		$total_in_fact=count($list_news);
		
		if($total_in_fact > $total_record)
		
		{
			
			foreach($arr_cate_home as $cate_id=>$total_news)
			{
				foreach($list_news as $_tmp)
				{
					if($_tmp['cate_id']==$cate_id || $list_category[$_tmp['cate_id']]['cate_id1']==$cate_id)
						$arr_cate_news[$cate_id][$_tmp['nw_id']]=$_tmp['time_public'];										
				}
			}
			
			/*kiem tra xem co thua du lieu khong?*/
			foreach($arr_cate_home as $cate_id=>$total_news)
			{
				if(count($arr_cate_news[$cate_id]) > $total_news)
				{
					asort($arr_cate_news[$cate_id]);
					$k=count($arr_cate_news[$cate_id]) - $total_news;
					$j=1;
					$del_news_ids='';
					foreach($arr_cate_news[$cate_id] as $nw_id=>$time_public)
					{
						$del_news_ids.=$nw_id.',';
						++$j;
						if($j > $k) break;
					}
					$del_news_ids=trim($del_news_ids,',');
					$newsObj->deleteMultiHome('nw_id IN('.$del_news_ids.')');
					/*update lai trang thai khi xao du lieu*/
					$sql="UPDATE store SET type =0 WHERE id IN (".$del_news_ids.")";
					$newsObj->querySql($sql);
				}
			}
		}
	}
	function adminNewsComment()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/admin_news_comment.htm");
		Page::setHeader("Quản trị tin bài", "Quản trị bình luận", "Quản trị bình luận");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		Page::registerFile('popup-css',	 Module::pathSystemCSS().'popup.css' , 'footer', 'css');
		Page::registerFile('popup-js', JAVASCRIPT_PATH.'popup.js' , 'footer', 'js');
		Page::registerFile('jqDnR', JAVASCRIPT_PATH.'jqDnR.js' , 'footer', 'js');
		$item_per_page = 20;
		$wh = '';
		$commentObj=new Comment();
		$newsObj = new BackendNews();
		$userObj=new User();
		$list_comment=$commentObj->getList($wh,'time_created desc',$limit);
		
		//Liệt kê danh sách người duyệt bình luận - begin
		$censor_ids=',';
		foreach($list_comment as $_temp)
		{
			if(!substr_count($censor_ids,','.$_temp['censor_id'].','))
				$censor_ids.=$_temp['censor_id'].',';
			
		}
		$censor_ids=trim($censor_ids,',');
		if($censor_ids)
			$list_censor=$userObj->userIdToName($censor_ids);
		//Liệt kê danh sách người duyệt bình luận - end
		
		
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		$stt =0;
		joc()->set_var('total_rowcount',count($list_comment));
		foreach($list_comment as $row)
		{

			$news = $newsObj->getStoreOne($row['nw_id']);
//			$news = $newsObj->getStoreOne(22950);
//			$row['nw_id'] = 22950;
//			$commentObj->updateData($row,$row[id]);
			$stt++;
			joc()->set_var('stt',$stt);
			joc()->set_var('id',$row['id']);
			joc()->set_var('title',$row['title']);
			joc()->set_var('content',$row['content']);
			joc()->set_var('email',$row['email']);
			joc()->set_var('censor_id',$list_censor[$row['censor_id']]['user_name']);
//			joc()->set_var('censor_id',$row['censor_id']);
			joc()->set_var('time_created',date('H:i d-m-Y',$row['time_created']));
			joc()->set_var('nw_title',$news['title']);
			joc()->set_var('href','http://ngoisao.vn/?app=news&page=detail&id='.$news['id']);
			
			$text_html.=joc()->output('ListRow');
		}
		
		joc()->set_var('ListRow',$text_html);
		
		
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;	
	}
	function adminWirteSEO()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/admin_write_seo.htm");
		Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		$newsObj=new BackendNews();
		$userObj=new User();
		/*Tìm kiếm*/
		$list_category=$newsObj->getListCategory('cate_id1=0','',100,'id');
		$list_category1=$newsObj->getListCategory('cate_id1 > 0 AND cate_id2=0','',100,'id');
		
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		$filter_id=SystemIO::get('filter_id','int',0);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');
		
		joc()->set_var('q',$q);
		$label_s=substr($q,0,7);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$cate_id=SystemIO::get('cate_id','int',0);
		$cate_id_2=SystemIO::get('cate_id_2','int',0);
		
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_category,'id','name'),$cate_id));
		joc()->set_var('option_category1',SystemIO::getOption(SystemIO::arrayToOption($list_category1,'id','name'),$cate_id_2));
		$wh='time_public > 0 AND time_public < '.time();
		if($q) {
			if($label_s=='origin:' || $label_s=='Origin:' || $label_s=='ORIGIN:')
				$wh.=" AND origin LIKE '%".substr($q,7,strlen($q))."%'";
			else
				$wh.=" AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";	
		}
		if($cate_id_2) 
			$wh.=" AND cate_path LIKE '%,{$cate_id_2},%'";
		if($cate_id) $wh.=" AND cate_path LIKE '%,{$cate_id},%'";
		
		if($filter_id) $wh.=' AND cate_id='.$filter_id;	
		
		$date_begin=SystemIO::get('date_begin','def');
		joc()->set_var('date_begin',$date_begin);
		if($date_begin)
		{
			$date_begin=strtotime(str_replace('/','-',$date_begin));
			$wh.= " AND time_public >= {$date_begin}";
			
		}
		
		$date_end=SystemIO::get('date_end','def');
		joc()->set_var('date_end',$date_end);
		if($date_end)
		{
			$date_end=strtotime(str_replace('/','-',$date_end));
			$date_end+=86399;
			$wh.= " AND time_public <= {$date_end}";
		}	
		/*Lay thông tin news*/
		$list_news=$newsObj->getListStore($wh,'time_public desc',$limit);
		$cate_ids='';
		$news_ids='';
		foreach($list_news as $_temp)
		{
			$cate_ids.=$_temp['cate_id'].',';
			$news_ids.=$_temp['id'].',';
			
		}
		$news_ids=trim($news_ids,',');
		$list_seo=$newsObj->getListData('store_seo','id,title,description','id IN ('.$news_ids.')',null,'30','id',false);
		$cate_ids=trim($cate_ids,',');	
		$list_path_news=$newsObj->getMultiPathNews($cate_ids);
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		global $NEWS_PROPERTY;
		foreach($list_news as $row)
		{
			joc()->set_var('title',$row['title']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('path',$list_path_news[$row['cate_id']]);
			joc()->set_var('description',$list_seo[$row['id']]['description'] ? $list_seo[$row['id']]['description'] : $row['description']);			
			joc()->set_var('title_seo',$list_seo[$row['id']]['title'] ? htmlspecialchars($list_seo[$row['id']]['title']) : htmlspecialchars($row['title']));
			joc()->set_var('time_public',date('H:i d-m-Y',$row['time_public']));
			joc()->set_var('href','http://ngoisao.vn/?app=news&page=detail&id='.$row['id']);
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
}