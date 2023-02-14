<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/main/includes/user.php';
require_once 'application/news/backend/includes/administration.baogiay.news.php';
require_once 'application/main/includes/user.php';
require_once UTILS_PATH.'paging.php';
require_once UTILS_PATH.'image.upload.php'; 
class AdministrationMaket extends Form
{
	function __construct()
	{
		Form::__construct($this);
	}
	function on_submit()
	{
		ini_set('display_errors',1);
		$newsObj= new AdministrationBaogiayNews();
		$user_info=UserCurrent::$current->data;	
		
		if($_FILES['img1']['name']){
			$uploader=new Uploader();
			$uploader->setPath(NEWS_IMG_UPLOAD_BAOGIAY);
			$uploader->setMaxSize(20000);
			$uploader->setFileType('custom',array('jpg','jpeg','png','gif'));
			$result=$uploader->doUpload('img1');
			$img1=(string)$result['name'];			
        }
		$page=SystemIO::post('page','int');
		$cate_id=SystemIO::post('cate_id','int');
		$news_id=SystemIO::post('news_id','int',0);
		$user_id=$user_info['id'];
		$arrNewData=array('page'=>$page,'cate_id'=>$cate_id,'img'=>$img1,'time_created'=>time(),'user_id'=>$user_id,'property'=>1);
		if($news_id==0)
		{
			$newsObj->insertData('maket',$arrNewData);
			Url::redirectUrl(array(),'?app=news&page=administration_maket&cmd=news_store');
		}
		else
		{
			$newsObj->updateData('maket',$arrNewData,'id='.$news_id);
			Url::redirectUrl(array(),'?app=news&page=administration_maket&cmd=news_store');
		}
		
	}
	function index()
	{	
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		Page::registerFile('popup-css',	 Module::pathSystemCSS().'popup.css' , 'header', 'css');
		Page::registerFile('popup-js', JAVASCRIPT_PATH.'popup.js' , 'footer', 'js');
		Page::registerFile('jqDnR', JAVASCRIPT_PATH.'jqDnR.js' , 'footer', 'js');
		Page::registerFile('admin process'		 , Module::pathSystemJS().'administration.process.js' , 'header', 'js');
		$cmd=SystemIO::get('cmd','str','news_store');
		$news_id=SystemIO::get('news_id','int',0);
		switch($cmd)
		{
			case 'news_store':
				return $this->adminMaket();
				break;
			case 'store_maket':
				return $this->adminNewsStore();
				break;				
			case 'news_create':
				return $this->adminAddAndEdit($news_id);
				break;
		}
	}
	function adminNewsStore()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/administration_store_maket.htm");
		Page::setHeader("Hệ thống quản lý báo giấy", "Quản trị tin bài", "Quản trị tin bài");
		$newsObj=new AdministrationBaogiayNews();
		$userObj=new User();
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
		$list_news=$newsObj->getListData('store','*',$wh,'time_post desc',$limit);
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
			joc()->set_var('user_name_row',$list_user[$row['user_id']]['user_name']);
			joc()->set_var('stt',$stt);
			if($row['status'] == 7)
			{
				$bg="#FFCCCC";
			}
			else
			{
			
				$bg="#FFF";
			}	
			if($row['property'] == 1)
			{
				joc()->set_var('function','<a href="javascript::" onclick="postMaket('.$row['id'].')">Đăng tin</a>');
			}
			elseif($row['property'] == 2)
			{
				joc()->set_var('function','Đã đăng tin');
			}
				
			++$stt;	
			joc()->set_var('bg',$bg);
			joc()->set_var('time_post',date('H:i d-m-Y',$row['time_post']));
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
	function adminMaket()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/administration_maket_bao_giay.htm");
		Page::setHeader("Quản trị tin báo giấy", "Quản trị báo giấy", "Quản trị báo giấy");
		$newsObj= new AdministrationBaogiayNews();
		$userObj= new User();
		$list_user = $userObj->getList('active=1');
		$cate_id=SystemIO::get('cate_id','int',0);
		$property= SystemIO::get('property','def');
		$wh='1=1';
		if($cate_id)
			$wh.=' AND cate_id='.$cate_id;
		
		if($property!="")
		{
			$wh.=' AND property = '.(int)$property;
		}	
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$list_news = $newsObj->getListData('maket','*',$wh,'page ASC',$limit);
		$list_cate = $newsObj->getListData('cate','id,name','','','0,1000','id',false);
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_cate,'id','name'),$cate_id));
		
		joc()->set_block('AdminNews','ListRow','ListRow');
		$txt_html='';
		foreach($list_news as $row)
		{
			
			joc()->set_var('page',$row['page']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('stt',$stt);
			$function='';
			if($row['property'] == 2)
			{
				$function='<br/>Đã duyệt maket';
				joc()->set_var('function',$function);
				joc()->set_var('function_edit','');
			}
			elseif($row['property'] == 1)
			{
				joc()->set_var('function_edit','<a href="?app=news&page=admin_baogiay&cmd=news_create&news_id='.$row['id'].'">Sửa</a> | <a onclick="deleteData('.$row['id'].')" href="javascript:void;">Xóa</a>');					
				joc()->set_var('function','<br/><a onclick="updateProperty('.$row['id'].',2)" href="javascript:void;">Duyệt maket</a> | <a onclick="updateProperty('.$row['id'].',0)" href="javascript:void;">Không duyệt maket</a>');
			}
			elseif($row['property'] == 0)
			{
				$function =' Không duyệt';
				joc()->set_var('function',$function);
				joc()->set_var('function_edit','<a href="?app=news&page=admin_baogiay&cmd=news_create&news_id='.$row['id'].'">Sửa</a> | <a onclick="deleteData('.$row['id'].')" href="javascript:void;">Xóa</a>');
			}
			joc()->set_var('path',$list_cate[$row['cate_id']]['name']);
			joc()->set_var('img','data/baogiay/'.$row['img']);
			joc()->set_var('time_created',date('d/m/Y',$row['time_created']));
			joc()->set_var('name_btv',$list_user[$row['user_id']]['user_name']);
			++$stt;
			$txt_html.=joc()->output('ListRow');
		}
		joc()->set_var('ListRow',$txt_html);
		global $TOTAL_ROWCOUNT;
		$totalRecord=$TOTAL_ROWCOUNT;
		joc()->set_var('total_rowcount',$totalRecord);
		joc()->set_var('paging','<li>Tổng số: '.$totalRecord.'</li>'.Paging::paging ($totalRecord,$item_per_page,10));
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;	
	}
	function adminAddAndEdit($news_id=0)
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/administration_maket_add_edit.htm");
		joc()->set_var('begin_form' , Form::begin(false, "POST", ' enctype="multipart/form-data" onsubmit="return checkForm()"'));
		joc()->set_var('end_form' 	, Form::end());
		$newsObj= new AdministrationBaogiayNews();
		$list_cate = $newsObj->getListData('cate','*','','','0,1000','id');
		$row=array();
		if($news_id)
		{
			$rows=$newsObj->getListData('maket','*','id='.$news_id);
		}
		$row=current($rows);
		joc()->set_var('news_id',$row['id']);
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_cate,'id','name'),$row['cate_id']));	
		joc()->set_var('page',$row['page']);
		if($row['img'])
			joc()->set_var('img1','<img src="data/baogiay/'.$row['img'].'" />');
		else
			joc()->set_var('img1','');	
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;
		
	}
	

	
}