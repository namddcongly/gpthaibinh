<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/includes/property_news.php';
require_once 'application/main/includes/user.php';
require_once UTILS_PATH.'paging.php';
require_once UTILS_PATH.'image.upload.php'; 
require_once 'application/news/includes/shopping.php';
require_once 'application/news/includes/category_shopping.php';
class AdminShopping extends Form
{
	function __construct()
	{
		Form::__construct($this);
	}
	function on_submit()
	{
		require_once UTILS_PATH.'image.resize.php';
		$user_info=UserCurrent::$current->data;	
		$shoppingObj=new Shopping();
		$imageResize=new ImageResize();
		$newsObj=new BackendNews();
		$id=SystemIO::post('id','int',0);
		$img1='';
		if($id){
			$row=$shoppingObj->readData($id);
		}	
		$path_img_upload=SHOPPING_IMG_UPLOAD;
		if($id==0){
			$time_created=time();
			$time_public=0;
		}	
		else{
			$time_created=$row['time_created'];
			$time_public=$row['time_public'];
		}		
		if($_FILES['img1']['name']){
			$uploader=new Uploader();
			$uploader->setPath($path_img_upload);
			$uploader->setMaxSize(10000);
			$uploader->setFileType('custom',array('jpg','jpeg','png','gif'));
			$result=$uploader->doUpload('img1');	
			$img1=(string)$result['name'];			
		}
		
		$cate_id=0;
		$title=SystemIO::post('title','def');
		$description=SystemIO::post('description','def');
		$tag=SystemIO::post('tag','def');
		
		$cate_id1=SystemIO::post('cate1','int',0);
		$cate_id2=SystemIO::post('cate2','int',0);		
		if($cate_id2 > 0)
			$cate_id=$cate_id2;
		else
			$cate_id=$cate_id1;	
			
		$list_province=$newsObj->getProvince();
		$sql="SELECT * FROM district";
		dbObject()->setProperty('news','district');
		dbObject()->query($sql);
		$list_district=dbObject()->fetchAll('id');

		$author=SystemIO::post('author','def');
		$origin=SystemIO::post('origin','def');
		$content=SystemIO::post('content','def');
		$province_id=SystemIO::post('province_id','int');
		$district_id=SystemIO::post('district_id','int');
		/*Dat lich public*/
		$user_id=$user_info['id'];
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
				'title'=>$title,
				'website'=>SystemIO::post('website','def',''),
				'phone'=>SystemIO::post('phone','def',''),
				'address'=>SystemIO::post('address','def',''),
				'email'=>SystemIO::post('email','def',''),
				'cate_id'	=>$cate_id,
				'content'=>$content,
				'tag'=>$tag,
				'author'=>$author,
				'origin'=>$origin,
				'user_id'=>$user_id,
				'province_id'=>$province_id,
				'province_name'=>$list_province[$province_id]['name'],
				'district_id'=>$district_id,
				'district_name'=>$list_district[$district_id]['name'],
				'time_created'=>$time_created,
				'time_public'=>$time_public,
				'status'=>0
		);
		if($img1) $arrNewData['img']=$img1;
		$arrNewData['user_id']=$row['user_id'] ? $row['user_id'] : $user_id;
		$arrNewData['editor_id']=$user_id;
		$tab=SystemIO::get('tab','def','store');
		if($id==0){
			if($shoppingObj->insertData($arrNewData)) 
				Url::redirectUrl(array(),'?app=news&page=admin_shopping&cmd=store_shopping&tab=store_pendding');
		}		
		else
		{
			if($shoppingObj->updateData($arrNewData,$id))
				Url::redirectUrl(array(),'?app=news&page=admin_shopping&cmd=store_shopping&tab='.$tab);				
		}
	}
	function index()
	{	
		Page::registerFile('sorttable Js',Module::pathSystemJS().'jquery.tablesorter.min.js' , 'header', 'js');
		$cmd=SystemIO::get('cmd','str','intro');
		$id=SystemIO::get('id','int',0);
		$tab=SystemIO::get('tab','def','');
		switch($cmd)
		{
			case 'store_shopping':
				return $this->adminShoppingStore($tab);
				break;
			case 'create_shopping':
				return $this->adminAddAndEdit($id);
				break;		
			default:
				return $this->adminShoppingStore($tab);
				break;
		}
	}
	function adminShoppingStore($tab)
	{
		joc()->set_file('AdminShopping', Module::pathTemplate()."backend/admin_shopping_store.htm");
		Page::setHeader("Quản trị tin bài shopping", "Quản trị tin bài shopping", "Quản trị tin bài shopping");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		Page::registerFile('popup-css',	 Module::pathSystemCSS().'popup.css' , 'footer', 'css');
		Page::registerFile('popup-js', JAVASCRIPT_PATH.'popup.js' , 'footer', 'js');
		Page::registerFile('jqDnR', JAVASCRIPT_PATH.'jqDnR.js' , 'footer', 'js');
		$userObj=new User();
		$shoppingObj=new Shopping();
		$cateShop=new CategoryShopping();
		$list_cate=$cateShop->getList('property=1','arrange ASC',50,'id');
		$cate_id=SystemIO::get('cate_id','int',0);
		$cate_id_search='';
		/**/
		$cate_level1=array();
		foreach($list_cate as $_tmp)
		{
			if($_tmp['parent_id']==0)
				$cate_level1[]=$_tmp;
			if($_tmp['parent_id']==$cate_id && $cate_id > 0)
				$cate_id_search.=$_tmp['id'].',';	
				
		}
		$cate_id_search=rtrim($cate_id_search,',');
		joc()->set_var('option_cate1',SystemIO::getOption(SystemIO::arrayToOption($cate_level1,'id','name'),0));
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		$filter_id=SystemIO::get('filter_id','int',0);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		/*Lay thông tin news*/
		$tab=SystemIO::get('tab','def','store');
		joc()->set_var('tab',$tab);
		if($tab=='store_pendding')
		{
			$wh='time_public =0';
			$class2='header-menu-active';
			$class1='';
		}
		else
		{
			$wh='time_public > 0 AND time_public < '.time();
			$class1='header-menu-active';
			$class2='';
		}
		joc()->set_var('class1',$class1);
		joc()->set_var('class2',$class2);
		$q=SystemIO::get('q','def');
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		if($q) $wh.=" AND title LIKE '%{$q}%'";
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
		$list_user_id_search='0';
		if($btv_name)
		{
			$_user_id_search= $userObj->userNameToId($btv_name);
			if(count($_user_id_search))
			{
				foreach($_user_id_search as $_temp)
					$list_user_id_search.=','.$_temp['id'];
			}
			
			$wh.=' AND user_id IN ('.$list_user_id_search.')';
		}
		if($cate_id_search)
			$wh.=' AND cate_id IN ('.$cate_id_search.')';
		
		$tab_s=SystemIO::get('tab_s','def','store');
		joc()->set_var('tab_s',$tab_s);
		$list_shopping=$shoppingObj->getList($wh,'time_public desc',$limit);
		$user_ids=',';
		$censor_ids=',';
		$editor_id=',';
		foreach($list_shopping as $_temp)
		{
			$cate_ids.=$_temp['cate_id'].',';
			if(!substr_count($censor_ids,','.$_temp['censor_id'].','))
				$censor_ids.=$_temp['censor_id'].',';
			if(!substr_count($user_ids,','.$_temp['user_id'].','))	
				$user_ids.=$_temp['user_id'].',';
			if(!substr_count($editor_id,','.$_temp['editor_id'].','))	
				$editor_id.=(int)$_temp['editor_id'].',';	
		}		
		$cate_ids=trim($cate_ids,',');
		$censor_ids=trim($censor_ids,',');
		$user_ids=trim($user_ids,',');
		$editor_id=trim($editor_id,',');
		if($editor_id)
			$user_ids.=','.$editor_id;
		if($censor_id)
			$user_ids.=','.$censor_id;
		if($user_ids)
			$list_censor_user_name_and_name_btv=$userObj->userIdToName($user_ids);
		
		joc()->set_block('AdminShopping','ListRow','ListRow');
		$text_html='';
		
		foreach($list_shopping as $row)
		{
			if($tab=='store_pendding')
				$function='<a href="javascript:;" onclick="doPublic('.$row['id'].',1)">Xuất bản</a>';
			else
				$function='<a href="javascript:;" onclick="doPublic('.$row['id'].',0)">Bỏ xuất bản</a>';
			joc()->set_var('tab',$tab);
			joc()->set_var('func',$function);		
			joc()->set_var('title',$row['title']);
			joc()->set_var('address',$row['address']);
			joc()->set_var('province_name',$row['province_name']);
			joc()->set_var('district_name',$row['district_name']);
			joc()->set_var('website',$row['website']);
			joc()->set_var('email',$row['email']);
			joc()->set_var('id',$row['id']);
			joc()->set_var('path',$list_path_news[$row['cate_id']]);
			joc()->set_var('time_created',date('H:i d-m-Y',$row['time_created']));
			joc()->set_var('phone',$row['phone']);
			joc()->set_var('censer_user_name',$list_censor_user_name_and_name_btv[$row['censor_id']]['user_name'] ? $list_censor_user_name_and_name_btv[$row['censor_id']]['user_name']: 'N/A');
			joc()->set_var('name_btv',$list_censor_user_name_and_name_btv[$row['user_id']]['user_name']);
			joc()->set_var('name_edit',$list_censor_user_name_and_name_btv[$row['editor_id']]['user_name']);
			joc()->set_var('stt',$stt);
			joc()->set_var('tag',$row['tag'] ? $row['tag'] : 'N/A');
			joc()->set_var('time_public',date('H:i d-m-Y',$row['time_public']));
			joc()->set_var('path_1',$list_cate[$list_cate[$row['cate_id']]['parent_id']]['name']);
			joc()->set_var('path_2',$list_cate[$row['cate_id']]['name']);
			joc()->set_var('href','http://xahoi.com.vn/?app=news&page=detail&id='.$row['id']);
			if($row['img']){ 
				$path_img='data/shopping/';
				$src=$row['img'];
			}
			else {
				$src='100x100.jpg';
				$path_img='webskins/icons/';
			}
			joc()->set_var('src',IMG::show($path_img,$src));
			
			joc()->set_var('origin',$row['origin']);
			$property='';
			$property_focus='';
			joc()->set_var('bg',$bg);
			++$stt;
			$text_html.=joc()->output('ListRow');
		}
		joc()->set_var('ListRow',$text_html);
		global $TOTAL_ROWCOUNT;
		$totalRecord=$TOTAL_ROWCOUNT;
		joc()->set_var('total_rowcount',$totalRecord);
		joc()->set_var('paging','<li>Tổng số: '.$totalRecord.'</li>'.Paging::paging ($totalRecord,$item_per_page,10));
		$html= joc()->output("AdminShopping");
		joc()->reset_var();
		return $html;
	}
	function adminAddAndEdit($id=0)
	{

		joc()->set_file('AdminShopping', Module::pathTemplate()."backend/admin_shopping_add_edit.htm");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('ckeditor.js'	, 'webskins/richtext/ckeditor/ckeditor.js', 'footer', 'js');			
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');		
		Page::registerFile('jquery.adapter.js' , 'webskins/richtext/ckeditor/adapters/jquery.adapter.js', 'footer', 'js');	
		Page::setHeader("Tạo bài shopping", "Tạo bài shopping", "Tạo bài shopping");
		joc()->set_var('begin_form' , Form::begin(false, "POST", ' enctype="multipart/form-data" onsubmit="return checkForm()"'));
		joc()->set_var('end_form' 	, Form::end());
		$tab=SystemIO::get('tab','def','store_pendding');
		joc()->set_var('tab',$tab);
		
		$newsObj=new BackendNews();
		$shoppingObj=new Shopping();
		$cateShop=new CategoryShopping();
		$list_cate=$cateShop->getList('property=1','arrange ASC',100,'id');
		$cate1=array();
		$cate2=array();
		foreach($list_cate as $_tmp)
		{
			if($_tmp['parent_id']==0)
				$cate1[]=$_tmp;
			else
				$cate2[]=$_tmp;
		}		
		$list_province=$newsObj->getProvince();
		$sql="SELECT * FROM district";
		dbObject()->setProperty('news','district');
		dbObject()->query($sql);
		$list_district=dbObject()->fetchAll('id');
		$row=$shoppingObj->readData($id);
		joc()->set_var('option_province',SystemIO::getOption(SystemIO::arrayToOption($list_province,'id','name'),(int)$row['province_id']));
		joc()->set_var('option_district',SystemIO::getOption(SystemIO::arrayToOption($list_district,'id','name'),(int)$row['district_id']));
		joc()->set_var('option_cate1',SystemIO::getOption(SystemIO::arrayToOption($cate1,'id','name'),(int)$list_cate[$list_cate[$row['cate_id']]['parent_id']]['id']));
		joc()->set_var('option_cate2',SystemIO::getOption(SystemIO::arrayToOption($cate2,'id','name'),(int)$row['cate_id']));	
		joc()->set_var('title',$row['title']? htmlspecialchars($row['title']) :'');
		joc()->set_var('content',$row['content']? $row['content'] :'');
		joc()->set_var('author',$row['author']? $row['author'] :'');
		joc()->set_var('tag',$row['tag']? $row['tag'] :'');
		joc()->set_var('origin',$row['origin']? $row['origin'] :'');
		joc()->set_var('img1',$row['img'] ? '<img src="data/shopping/'.$row['img'].'" width="100px;" />':'');
		joc()->set_var('id',$id);
		$html= joc()->output("AdminShopping");
		joc()->reset_var();
		return $html;	
	}

}