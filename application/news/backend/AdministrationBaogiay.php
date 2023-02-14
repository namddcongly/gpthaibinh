<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/news/backend/includes/administration.baogiay.news.php';
require_once 'application/main/includes/user.php';
require_once UTILS_PATH.'paging.php';
require_once UTILS_PATH.'image.upload.php'; 
ini_set('display_errors',1);
class AdministrationBaogiay extends Form
{
	function __construct()
	{
		Form::__construct($this);
	}
	
	function on_submit()
	{
		
		$newsObj= new AdministrationBaogiayNews();
		$user_info=UserCurrent::$current->data;	
		$news_id=SystemIO::post('news_id','int',0);
		$user_id=$user_info['id'];
		/* Lay du lieu */
		$title=SystemIO::post('title','def');
		$sub_title=SystemIO::post('sub_title','def');
		$description=SystemIO::post('description','def');
		$content=SystemIO::post('content','def');
		$status =SystemIO::post('status','int',0);
		
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
				'title'			=>$title,
				'sub_title'		=>$sub_title,
				'description'	=>$description,
				'status'		=>$status,
				'is_return'		=>0,
				'content'		=>$content,
				'time_created'  =>time()
		);
		
		if($news_id==0)
		{
			$arrNewData['user_id'] =$user_id;
			$newsObj->insertData('store',$arrNewData);
			Url::redirectUrl(array(),'?app=news&page=administration_baogiay&cmd=news_private');	
		}
		else
		{
			$list_data= $newsObj->getListData('store','*','id='.$news_id,'','0,1','',false);
			$row=current($list_data);
			$row['nw_id']= $row['id'];
			unset($row['id']);
			$row['editor_id'] = $user_info['id'];
			$newsObj->insertData('store_edited',$row);
			$arrNewData['user_id']=$row['user_id'];
			$newsObj->updateData('store',$arrNewData,'id='.$news_id);
			if($arrNewData['status'] == 0 || $arrNewData['status'] == 1)
				Url::redirectUrl(array(),'?app=news&page=administration_baogiay&cmd=news_private');
			elseif($arrNewData['status'] == 6 || $arrNewData['status'] == 7)// sua trong khoa
				Url::redirectUrl(array(),'?app=news&page=administration_baogiay&cmd=news_store');				
			else
				Url::redirectUrl(array(),'?app=news&page=administration_baogiay&cmd=pending_censor');
						
		}
		
	}
	function index()
	{	
		//ini_set('display_errors',1);
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		Page::registerFile('popup-css',	 Module::pathSystemCSS().'popup.css' , 'header', 'css');
		Page::registerFile('popup-js', JAVASCRIPT_PATH.'popup.js' , 'footer', 'js');
		Page::registerFile('jqDnR', JAVASCRIPT_PATH.'jqDnR.js' , 'footer', 'js');
		Page::registerFile('admin process'		 , Module::pathSystemJS().'administration.process.js' , 'header', 'js');
		$cmd=SystemIO::get('cmd','str','message');
		$news_id=SystemIO::get('news_id','int',0);
		$newsObj=new AdministrationBaogiayNews();
		switch($cmd)
		{
			case 'news_statistic':
				return $this->inforStatistic();
				break;
			case 'news_store':
			
				if(!UserCurrent::havePrivilege('NEWS_STORE'))
				{
				    Url::urlDenied();
				}
				return $this->adminNewsStore();
				break;
			case 'news_private':
				return $this->adminPrivate();
				break;	
			case 'pending_censor':
				return $this->adminPendingCensor();
				break;
			case 'news_return':
				return $this->adminNewsReturn();
				break;
			case 'message':
				if(!UserCurrent::havePrivilege('VIEW_MESSAGE'))
				{
				    Url::urlDenied();
				}
				return $this->viewMessage();
				break;
			case 'news_create':
				if(!UserCurrent::havePrivilege('NEWS_CREATE'))
				{
				    Url::urlDenied();
				}
				return $this->adminAddAndEdit($news_id);
				break;
			case 'view_content':
				$news_id=SystemIO::get('nw_id','int');
				return $this->viewContent($news_id);
				break;
			default:
				return $this->viewMessage();
				break;		
				
		}
	}
	function adminNewsStore()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/administration_store_bao_giay.htm");
		Page::setHeader("Hệ thống quản lý báo giấy", "Quản trị tin bài", "Quản trị tin bài");
		$newsObj=new AdministrationBaogiayNews();
		$userObj=new User();
		$user_info=UserCurrent::$current->data;	
		/*Tìm kiếm*/
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		$filter_id=SystemIO::get('filter_id','int',0);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$wh='(status =6 OR status = 7)';
		if(!UserCurrent::havePrivilege('NEWS_VIEW_ALL_STORE'))
			$wh.=" AND user_id = ".$user_info['id'];	
		if($q) {
			
			$wh.=" AND (title LIKE '%{$q}%')";	
		}
		$date_begin=SystemIO::get('date_begin','def');
		joc()->set_var('date_begin',$date_begin);
		if($date_begin)
		{
			$date_begin=strtotime(str_replace('/','-',$date_begin));
			$wh.= " AND time_post >= {$date_begin}";
		}
		
		$date_end=SystemIO::get('date_end','def');
		joc()->set_var('date_end',$date_end);
		if($date_end)
		{
			$date_end=strtotime(str_replace('/','-',$date_end));
			$date_end+=86399;
			$wh.= " AND time_post <= {$date_end}";
		}
		$list_user = $userObj->userIdName();
		$user_name=SystemIO::get('user_name','def');
		if($user_name)
		{
			$k=0;
			foreach($list_user as $u)
			{
				if($u['user_name'] == $user_name)
				{
					++$k;	
					$wh.=' AND user_id = '.$u['id'];
					break;
				}
			}
			if($k==0)
				$wh.=' AND user_id = 0';
		}
		joc()->set_var('user_name',$user_name);
		$list_news=$newsObj->getListData('store','*',$wh,'time_post desc, property DESC',$limit);
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		$function_censor='';
		$bg='#FFF';
		foreach($list_news as $row)
		{
			joc()->set_var('title',$row['title']);
			joc()->set_var('sub_title',$row['sub_title']);
			joc()->set_var('description',$row['description']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('stt',$stt);
			joc()->set_var('time_post',date('H:i d-m-Y',$row['time_post']));
			$function_return='';
			$function_censor='';
			if($row['property']  == 1)
			{
				joc()->set_var('property_maket','Bài chờ đăng');
				if(UserCurrent::havePrivilege('IS_TONGBIENTAP'))
				{
					$function_return='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',1)">Trả bài cho PV</a><br />';
					$function_return.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',2)">Trả về BIÊN TẬP VIÊN</a><br />';
					$function_return.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',3)">Trả về TRƯỞNG BAN BIÊN TẬP</a><br />';
					$function_return.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',4)">Trả về THƯ KÝ BIÊN TẬP</a><br />';
					$function_return.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',5)">Trả về TRƯỞNG BAN THƯ KÝ</a><br />';
					$function_return.='<a href="javascript:;" class="show-list" rel="tinnhan-chidao">Đưa chỉ đạo cho Nhân viên</a><br />';			
				}			
				if($row['status'] == 7)
				{
						$function_censor='';
						$bg="#FFCCCC";
				}
				else{
					if(UserCurrent::havePrivilege('IS_TONGBIENTAP'))
					{
						$function_censor='<a href="javascript:;" onclick="postNews(\''.$row['id'].'\',\'tongbientap_censor\',this);">DUYỆT BÀI</a><br/>';
					}
					$bg="#FFF";
				}		
			}	
			else
			{
				joc()->set_var('property_maket','Bài đã đăng');			
			}
				
			joc()->set_var('function_censor',$function_censor);
			joc()->set_var('function_return',$function_return);
			joc()->set_var('bg',$bg);
			joc()->set_var('user_name_row',$list_user[$row['user_id']]['user_name']);
			joc()->set_var('reason',$row['reasons'] ? $row['reasons'] : 'N/A');
			if(UserCurrent::havePrivilege('NEWS_ACTION_EDIT_DEL_RETURN'))
				joc()->set_var('action_store','<a href="javascript:;" onclick="getId('.$row['id'].')" rel="reason-return" class="show-list">Về tin chờ duyệt</a> | <a href="?app=news&page=admin_news&cmd=news_create&news_id='.$row['id'].'&from=store">Sửa</a>| <a href="javascript:;" onclick="deleteData('.$row['id'].')">Xóa bài</a><br/>');
			else
				joc()->set_var('action_store','N/A');
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
	function adminPendingCensor()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/administration_pending_censor_baogiay.htm");
		Page::setHeader("Hệ thống quản lý báo giấy", "", "");
		$newsObj=new AdministrationBaogiayNews();
		$userObj=new User();
		/*Tìm kiếm*/
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');
		
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');		
		$wh='1= 1';
		/* Kiểm tra quyền*/
		if(UserCurrent::havePrivilege('IS_BIENTAP')){
			$wh.=' AND status=2 AND is_return =0';
		}	
		elseif(UserCurrent::havePrivilege('IS_TRUONGBANBIENTAP')){
			$wh.=' AND status=3 AND is_return =0';
		}	
		elseif(UserCurrent::havePrivilege('IS_THUKYBIENTAP')){
			$wh.=' AND status=4 AND is_return =0';
		}	
		elseif(UserCurrent::havePrivilege('IS_TRUONGBANTHUKY')){
			$wh.=' AND status=5 AND is_return =0';	
		}	
		if(UserCurrent::havePrivilege('IS_TONGBIENTAP')){
			$wh='1 = 1';
		}	
		if($q) $wh.=" AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
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
		$list_user = $userObj->userIdName();
		
		if($user_name)
		{
			foreach($list_user as $_temp)
			{
				if($_temp['user_name'] == $user_name){
					$list_user_id_search.=','.$_temp['id'];
					break;
				}	
			}
			$list_user_id_search=trim($list_user_id_search,',');
			$wh.=' AND user_id IN ('.$list_user_id_search.')';
		}
		$list_news=$newsObj->getListData('store','*',$wh,'id desc',$limit);
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		foreach($list_news as $row)
		{
			
			
			if(UserCurrent::havePrivilege('IS_BIENTAP')){
				$function_return='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',1)">Trả bài cho PV</a><br />';	
			}	
			elseif(UserCurrent::havePrivilege('IS_TRUONGBANBIENTAP')){
				$function_return='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',2)">Trả về BIÊN TẬP VIÊN</a><br />';
			}	
			elseif(UserCurrent::havePrivilege('IS_THUKYBIENTAP')){
				$function_return='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',3)">Trả về TRƯỞNG BAN BIÊN TẬP</a><br />';
			}	
			elseif(UserCurrent::havePrivilege('IS_TRUONGBANTHUKY')){
				$function_return='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',4)">Trả về THƯ KÝ BIÊN TẬP</a><br />';
			}	
			if(UserCurrent::havePrivilege('IS_TONGBIENTAP')){
				$function_return='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',1)">Trả bài cho PV</a><br />';
				$function_return.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',2)">Trả về BIÊN TẬP VIÊN</a><br />';
				$function_return.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',3)">Trả về TRƯỞNG BAN BIÊN TẬP</a><br />';
				$function_return.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',4)">Trả về THƯ KÝ BIÊN TẬP</a><br />';
				$function_return.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',5)">Trả về TRƯỞNG BAN THƯ KÝ</a><br />';			
			}	
			joc()->set_var('sub_title',$row['sub_title']);
			joc()->set_var('title',$row['title']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('time_created',date('H:i d-m-Y',$row['time_created']));
			joc()->set_var('description',$row['description']);
			joc()->set_var('mobile',$list_user[$row['user_id']]['mobile_phone'] ? $list_user[$row['user_id']]['mobile_phone'] : 'N/A');
			joc()->set_var('user_name_row',$list_user[$row['user_id']]['user_name'] ? $list_user[$row['user_id']]['user_name'] :'N/A');	
			$function='';
			if(UserCurrent::havePrivilege('NEWS_ACTION_RETURN')){
				$function.=$function_return;
			}
			if(UserCurrent::havePrivilege('NEWS_ACTION_CENSOR') && UserCurrent::havePrivilege('IS_BIENTAP'))
				$function.='<a href="javascript:;" onclick="postNews(\''.$row['id'].'\',\'bt_post_news\',this);">chuyển BAN BIÊN TẬP</a><br/>';
			if(UserCurrent::havePrivilege('NEWS_ACTION_CENSOR') && UserCurrent::havePrivilege('IS_TRUONGBANBIENTAP'))
				$function.='<a href="javascript:;" onclick="postNews(\''.$row['id'].'\',\'tbbt_post_news\',this);">chuyển THƯ KÝ BIÊN TẬP</a><br/>';
			if(UserCurrent::havePrivilege('NEWS_ACTION_CENSOR')&& UserCurrent::havePrivilege('IS_THUKYBIENTAP'))
				$function.='<a href="javascript:;" onclick="postNews(\''.$row['id'].'\',\'tkbt_post_news\',this);">chuyển BAN THƯ KÝ</a><br/>';
			if(UserCurrent::havePrivilege('NEWS_ACTION_CENSOR') && UserCurrent::havePrivilege('IS_TRUONGBANTHUKY'))
				$function.='<a href="javascript:;" onclick="postNews(\''.$row['id'].'\',\'tbtk_post_news\',this);">duyệt bài KHO DỮ LIỆU</a><br/>';	
						
			joc()->set_var('function',$function);
			$act_del_edit='Hành động khác: <a href="?app=news&page=administration_baogiay&cmd=news_create&news_id='.$row['id'].'">Sửa</a> | <a href="javascript:;" onclick="delData('.$row['id'].')">Xóa</a> <br/>';
			joc()->set_var('act_del_edit',$act_del_edit);			
			joc()->set_var('stt',$stt);
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
	function adminNewsReturn()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/administration_news_return_baogiay.htm");
		Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		Page::registerFile('popup-css',	 Module::pathSystemCSS().'popup.css' , 'header', 'css');
		Page::registerFile('popup-js', JAVASCRIPT_PATH.'popup.js' , 'footer', 'js');
		Page::registerFile('jqDnR', JAVASCRIPT_PATH.'jqDnR.js' , 'footer', 'js');
		$newsObj=new AdministrationBaogiayNews();
		$userObj=new User();
		$user_info=UserCurrent::$current->data;	
		$item_per_page=50;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');
		
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		if(UserCurrent::havePrivilege('IS_PHONGVIEN'))
			$wh='((status = 1 AND user_id = '.$user_info['id'].'))';
		elseif(UserCurrent::havePrivilege('IS_BIENTAP'))
			$wh='((status = 2 AND is_return = 1) OR is_return =2)';
		elseif(UserCurrent::havePrivilege('IS_TRUONGBANBIENTAP'))
			$wh='((status = 3 AND is_return = 1) OR is_return =2)';
		elseif(UserCurrent::havePrivilege('IS_THUKYBIENTAP'))
			$wh='((status = 4 AND is_return = 1) OR is_return =2)';
		elseif(UserCurrent::havePrivilege('IS_TRUONGBANTHUKY'))
			$wh='((status = 5 AND is_return = 1) OR is_return =2)';					
		else
			$wh='1=0';
			
		if($q) $wh.=" AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
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
		$list_news=$newsObj->getListData('store','*',$wh,'',$limit);
		$news_ids='';
		$censor_id=',';
		$user_ids='';
		foreach($list_news as $_temp)
		{
			if(!substr_count($censor_id,','.$_temp['censor_id'].','))
				$censor_id.=(int)$_temp['censor_id'].',';
			if(!substr_count($user_ids,','.$_temp['user_id'].','))	
				$user_ids.=(int)$_temp['user_id'].',';	
				
		}
		$censor_id=trim($censor_id,',');
		$user_ids=trim($user_ids,',');
		if(strlen($user_ids))
			$list_censor_user_name=$userObj->userIdToName($censor_id.','.$user_ids);
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		foreach($list_news as $row)
		{
			if(UserCurrent::havePrivilege('IS_BIENTAP')){
				$function_return='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',1)">Trả bài cho PV</a><br />';	
			}	
			elseif(UserCurrent::havePrivilege('IS_TRUONGBANBIENTAP')){
				$function_return='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',2)">Trả về BIÊN TẬP VIÊN</a><br />';
			}	
			elseif(UserCurrent::havePrivilege('IS_THUKYBIENTAP')){
				$function_return='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',3)">Trả về TRƯỞNG BAN BIÊN TẬP</a><br />';
			}	
			elseif(UserCurrent::havePrivilege('IS_TRUONGBANTHUKY')){
				$function_return='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',4)">Trả về THƯ KÝ BIÊN TẬP</a><br />';
			}	
			if(UserCurrent::havePrivilege('IS_TONGBIENTAP')){
				$function_return='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',1)">Trả bài cho PV</a><br />';
				$function_return.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',2)">Trả về BIÊN TẬP VIÊN</a><br />';
				$function_return.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',3)">Trả về TRƯỞNG BAN BIÊN TẬP</a><br />';
				$function_return.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',4)">Trả về THƯ KÝ BIÊN TẬP</a><br />';
				$function_return.='<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId('.$row['id'].',5)">Trả về TRƯỞNG BAN THƯ KÝ</a><br />';			
			}
			joc()->set_var('function',$function_return);	
			joc()->set_var('title',$row['title']);
			joc()->set_var('sub_title',$row['sub_title']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('function_return',$function_return);
			joc()->set_var('time_created',date('H:i d-n-Y',$row['time_created']));
			joc()->set_var('description',$row['description']);
			joc()->set_var('reason',nl2br($row['reasons']));
			joc()->set_var('stt',$stt);
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
	function adminPrivate()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/administration_private_baogiay.htm");
		Page::setHeader("Tin lưu trữ cá nhân", "Quản trị tin bài", "Quản trị tin bài");
		$newsObj=new AdministrationBaogiayNews();
		$userObj=new User();
		/*Tìm kiếm*/
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');
		
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$wh='status=0';
		if(UserCurrent::$current->data['user_name']!='namdd')
			$wh.=' AND user_id='.(int)UserCurrent::$current->data['id'];
		if($q) $wh.=" AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
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
		$list_news=$newsObj->getListData('store','*',$wh,'id desc',$limit);
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		foreach($list_news as $row)
		{
			joc()->set_var('title',$row['title']);
			joc()->set_var('sub_title',$row['sub_title']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('time_created',date('H:i d-m-Y',$row['time_created']));
			joc()->set_var('description',$row['description']);
			joc()->set_var('stt',$stt);
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
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/administration_add_edit.htm");
		Page::registerFile('ckeditor.js'	, 'webskins/richtext/ckeditor/ckeditor.js', 'footer', 'js');	
		Page::setHeader("Tạo bài viết", "Tạo bài viết", "Tạo bài viết");
		joc()->set_var('begin_form' , Form::begin(false, "POST", ' enctype="multipart/form-data" onsubmit="return checkForm()"'));
		joc()->set_var('end_form' 	, Form::end());
		$newsObj=new AdministrationBaogiayNews();
		if($id){
			$list_data= $newsObj->getListData('store','*','id='.$id,'','0,1','',false);
			$row=current($list_data);
		}
		joc()->set_var('news_id',$row['id']);
		joc()->set_var('title',$row['title']? htmlspecialchars($row['title']) :'');
		joc()->set_var('sub_title',$row['sub_title']? htmlspecialchars($row['sub_title']) :'');
		joc()->set_var('description',$row['description']? $row['description'] :'');
		joc()->set_var('content',$row['content']? $row['content'] :'');
		
		if($row['status'] == 1)// tin bitra ve
			$row['status']=0;
		joc()->set_var('status',(int)$row['status']);
		
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;	
	}
	function viewMessage()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/administration_message_baogiay.htm");
		Page::setHeader("Tin chỉ đạo", "Quản trị tin bài", "Quản trị tin bài");
		$newsObj=new AdministrationBaogiayNews();
		$userObj=new User();
		/*Tìm kiếm*/
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');	
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$wh='1 =1 ';
		if(UserCurrent::$current->data['user_name']!='namdd')
			$wh.=' AND ( recipients_id ='.(int)UserCurrent::$current->data['id'].' OR recipients_id=0)';
		if($q) $wh.=" AND (content LIKE '%{$q}%')";
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
		$list_news=$newsObj->getListData('message','*',$wh,'id desc',$limit);
		$list_user = $userObj->userIdName();
		joc()->set_block('AdminNews','ListRow','ListRow');
		$text_html='';
		foreach($list_news as $row)
		{
			joc()->set_var('content',$row['content']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('time_created',date('H:i d-m-Y',$row['time_created']));
			joc()->set_var('user_name_row',$list_user[$row['user_id']]['user_name']);
			joc()->set_var('stt',$stt);
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
	function viewContent($news_id)
	{
	
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/view_content.htm");
		Page::registerFile('ckeditor.js'	, 'webskins/richtext/ckeditor/ckeditor.js', 'footer', 'js');	
		Page::setHeader("Xem nội dung bài viết", "Tạo bài viết", "Tạo bài viết");
		$userObj=new User();
		$list_user = $userObj->userIdName();
		$newsObj=new AdministrationBaogiayNews();
		if($news_id){
			$list_data= $newsObj->getListData('store','*','id='.$news_id,'','0,1','',false);
			$row=current($list_data);
		}
		//print_r($row);
		joc()->set_var('news_id',$row['id']);
		joc()->set_var('status',$row['status']);
		joc()->set_var('title',$row['title']? htmlspecialchars($row['title']) :'');
		joc()->set_var('sub_title',$row['sub_title']? htmlspecialchars($row['title']) :'');
		joc()->set_var('description',$row['description']? $row['description'] :'');
		joc()->set_var('content',$row['content']? $row['content'] :'');
		joc()->set_var('time_created',date('H:i d/m/Y',$row['time_created']));
		joc()->set_var('user_name',$list_user[$row['user_id']]['user_name']);
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;	
	
	}
	function inforStatistic()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/administration_baogiay_statistic.htm");
		Page::setHeader("Thông tin thống kê nhanh", "Quản trị tin bài", "Quản trị tin bài");
		$newsObj=new AdministrationBaogiayNews();
		$total_tbt_record=$newsObj->countRecord('store','status=7');
		joc()->set_var('total_tbt_record',$total_tbt_record);
		
		$total_pending_tbt_record=$newsObj->countRecord('store','status=6');
		joc()->set_var('total_pending_tbt_record',$total_pending_tbt_record);
		
		$total_truongbanthuky_danhan=$newsObj->countRecord('store','status=5');
		joc()->set_var('total_truongbanthuky_danhan',$total_truongbanthuky_danhan);
		
		$total_thukybientap_danhan=$newsObj->countRecord('store','status=4');
		joc()->set_var('total_thukybientap_danhan',$total_thukybientap_danhan);		
		
		$total_truongbanbientap_danhan=$newsObj->countRecord('store','status=3');
		joc()->set_var('total_truongbanbientap_danhan',$total_truongbanbientap_danhan);
		
		$total_bientapvien=$newsObj->countRecord('store','status=2');
		joc()->set_var('total_bientapvien',$total_bientapvien);
		
		$total_maket_duyet=$newsObj->countRecord('maket','property=2');
		joc()->set_var('total_maket_duyet',$total_maket_duyet);
				
		$total_maket_chuaduyet=$newsObj->countRecord('maket','property=1');	
		joc()->set_var('total_maket_chuaduyet',$total_maket_chuaduyet);
		
		$total_maket_khongduyet=$newsObj->countRecord('maket','property=0');		
		joc()->set_var('total_maket_khongduyet',$total_maket_khongduyet);
		
		$total_dadang=$newsObj->countRecord('store','property=2');		 
		joc()->set_var('total_dadang',$total_dadang);
		
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;
	}
}