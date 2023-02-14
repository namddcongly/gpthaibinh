<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/main/includes/user.php';
require_once 'application/news/backend/includes/baogiay.news.php';
require_once 'application/main/includes/user.php';
require_once UTILS_PATH.'paging.php';
require_once UTILS_PATH.'image.upload.php'; 
//ini_set('display_errors',1);
class AdminBaogiay extends Form
{
	function __construct()
	{
		Form::__construct($this);
	}
	function on_submit()
	{
		ini_set('display_errors',1);
		$newsObj= new BaogiayNews();
		$user_info=UserCurrent::$current->data;	
		
		if($_FILES['img1']['name']){
			$uploader=new Uploader();
			$uploader->setPath(NEWS_IMG_UPLOAD_BAOGIAY);
			$uploader->setMaxSize(1500000);
			$uploader->setFileType('custom',array('jpg','jpeg','png','gif'));
			$result=$uploader->doUpload('img1');
			$img1=(string)$result['name'];

        }
       
		$page=SystemIO::post('page','int');
		$cate_id=SystemIO::post('cate_id','int');
		$news_id=SystemIO::post('news_id','int',0);
		$user_id=$user_info['id'];
		$arrNewData=array('page'=>$page,'cate_id'=>$cate_id,'img'=>$img1,'time_created'=>time(),'user_id'=>$user_id);
		if($news_id==0)
		{
			$id=$newsObj->insertData('baogiay',$arrNewData);
			//echo $id;
			//print_r($arrNewData);
		//die;
			Url::redirectUrl(array(),'?app=news&page=admin_baogiay&cmd=news_store');
		}
		else
		{
			$newsObj->updateData('baogiay',$arrNewData,'id='.$news_id);
			Url::redirectUrl(array(),'?app=news&page=admin_baogiay&cmd=news_store');
		}
		
	}
	function index()
	{
        if (!UserCurrent::isLogin()) {
            @header('Location:?app=main&page=admin_login');
        }
		$cmd=SystemIO::get('cmd','str','news_store');
		$news_id=SystemIO::get('news_id','int',0);
		switch($cmd)
		{
			case 'news_store':
				return $this->adminStore();
				break;
			case 'news_create':
				return $this->adminAddAndEdit($news_id);
				break;
		}
	}
	
	function adminStore()
	{
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/admin_bao_giay.htm");
		Page::setHeader("Quản trị tin báo giấy", "Quản trị báo giấy", "Quản trị báo giấy");
		$newsObj= new BaogiayNews();
		$userObj= new User();
		$list_user = $userObj->getList('active=1');
		$cate_id=SystemIO::get('cate_id','int',0);
		$y=date('Y',time());
		if($cate_id)
			//$wh='cate_id='.$cate_id.' AND time_created = '.(int)$y;
			$wh='cate_id='.$cate_id;
		else
			$wh='';
		//echo $wh;	
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$list_cate = $newsObj->getListData('cate_baogiay','id,name','','id DESC','0,1000','name',false);
		$list_news = $newsObj->getListData('baogiay','*',$wh,'cate_id DESC,page ASC',$limit);
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_cate,'id','name'),$cate_id));		
		joc()->set_block('AdminNews','ListRow','ListRow');
		$txt_html='';
		foreach($list_news as $row)
		{
			
			joc()->set_var('page',$row['page']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('stt',$stt);
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
		joc()->set_file('AdminNews', Module::pathTemplate()."backend/admin_bao_giay_add_edit.htm");
		joc()->set_var('begin_form' , Form::begin(false, "POST", ' enctype="multipart/form-data" onsubmit="return checkForm()"'));
		joc()->set_var('end_form' 	, Form::end());
		$newsObj= new BaogiayNews();
		$y=date('Y',time());
		$list_cate = $newsObj->getListData('cate_baogiay','id,name','time_created = '.(int)$y,'','0,1000','id');
		$row=array();
		if($news_id)
		{
			$rows=$newsObj->getListData('baogiay','*','id='.$news_id);
		}
		$row=current($rows);
		joc()->set_var('news_id',$row['id']);
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_cate,'id','name'),$row['cate_id']));	
		joc()->set_var('page',$row['page']);
		joc()->set_var('img1','<img src="data/baogiay/'.$row['image'].'" />');
		$html= joc()->output("AdminNews");
		joc()->reset_var();
		return $html;
		
	}
	

	
}