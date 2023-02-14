<?php
//ini_set('display_errors',1);
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once UTILS_PATH.'paging.php';
require_once UTILS_PATH.'image.upload.php';
if(!UserCurrent::havePrivilege('ADMIN_ADV'))
{
	Url::urlDenied();
}
class AdminAdv extends Form
{
	
	function __construct()
	{
		Form::__construct($this);
	}
    function on_submit(){
        $id=SystemIO::get('id','int');
        if($id){
            $adv = news()->selectOne('banner','time_created',"id=".$id);
            $dir = date('Y/n',$adv['time_created']);
        }
        else 
		{
			$dir = date('Y/n',time());
		}
        $img1 ='';
        $path_img_upload=ADV_UPLOAD.$dir;
		if($_FILES['img1']['name']){
			$uploader=new Uploader();
			$uploader->setPath($path_img_upload);
			$uploader->setMaxSize(2000);
			$uploader->setFileType('custom',array('jpg','jpeg','png','gif','swf'));
			$result=$uploader->doUpload('img1');	
			$img1=(string)$result['name'];
		}
        $img2 ='';
        $path_banner_upload = ADV_UPLOAD_BANNER;
        
        if($_FILES['img2']['name']){
			$uploader=new Uploader();
			$uploader->setPath($path_banner_upload);
			$uploader->setMaxSize(50000);
			$uploader->setFileType('custom',array('jpg','jpeg','png','gif','swf'));
			$result=$uploader->doUpload('img2');
            //echo (string)$result['name'];die;
			$img2=(string)$result['name'];
		}
        //echo $img2;die;
		if($_FILES['video']['name']){
			$uploader=new Uploader();
			$uploader->setPath($path_img_upload);
			$uploader->setMaxSize(600000);
			$uploader->setFileType('custom',array('flv','mp4'));
			$result=$uploader->doUpload('video');		
			$video = (string)$result['name'];
		}
        $title=SystemIO::post('title','def');
		$time_created=time();
		$position = SystemIO::post('position','int', 0);
        $page1 = SystemIO::post('page1','def', '0');
        $page2 = SystemIO::post('page2','def', '0');
        $page3 = SystemIO::post('page3','def', '0');
        $status = SystemIO::post('status','int', 0);
        $link = SystemIO::post('link','def', '');
		$arrNewData=array(
				'title'=>$title,
				'position'	=>$position,
				'page_home'=>$page1,
                'page_cate'=>$page2,
                'page_detail'=>$page3,
                'status' =>$status
		);
		if($img1)
			$arrNewData['img']=$img1;
		if($img2){
			$arrNewData['img2']=$img2;
			$link='http://congly.com.vn/banner/'.$img2;
		}	
        /*if($img2)
        {
            $arrNewData['img2']=$img2;
            $arrNewData['link']='http://congly.com.vn/banner/'.$img2;
        }
        else*/
            $arrNewData['link']=$link;
        
       #var_dump($arrNewData);die;     
        if($video)
			$arrNewData['video']=$video;
        #var_dump($arrNewData);die;
        if($id){
		  #var_dump($arrNewData);die;
			if(news()->update('banner',$arrNewData, "id=".$id)) {
				Url::redirectUrl(array(),'?app=news&page=admin_adv');
			}
		}	
		else
		{			
			$arrNewData['time_created']=$time_created;
            #var_dump($arrNewData);die;
			if(news()->insert('banner',$arrNewData)) 
			{
				Url::redirectUrl(array(),'?app=news&page=admin_adv');
			}
		}
    }
    function index()
    {
        $cmd=SystemIO::get('cmd','def','store');
		$id=SystemIO::get('id','int',0);
        switch($cmd)
		{
			
			case 'store':
				return $this->admin_adv();
				break;
			case 'create':
				return $this->adminAddAndEdit($id);
				break;
			
			default:
				return $this->adminStore();
				break;
		}
    }
	function admin_adv(){
	   
	   joc()->set_file('AdminAdv', Module::pathTemplate()."backend/admin_adv.htm");
	   Page::setHeader("Quản trị quảng cáo", "Quản trị quảng cáo", "Quản trị quảng cáo");
   	   Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
	   Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
	   Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');
       joc()->set_var('url_current', '?app=news&page=admin_adv&cmd=store');
	   joc()->set_var('link_add',  '?app=news&page=admin_adv&cmd=create');     
       joc()->set_block('AdminAdv','adv');
       $item_per_page=20;
	   $page_no=SystemIO::get('page_no','int',1);
	   if ($page_no<1) $page_no=1;
	   $stt=($page_no-1)*$item_per_page+1;
	   $limit=(($page_no-1)*$item_per_page).','.$item_per_page;
       $list_adv = news()->select('banner','*','','position asc',$limit);
       $array_ext = array('jpg','jpeg','png','gif');
       $html_adv = '';
       foreach($list_adv as $row)
       {
            joc()->set_var('id',$row['id']); 
            joc()->set_var('title',$row['title']);
            joc()->set_var('int_status',$row['status']); 
            joc()->set_var('link',$row['link']);
            if($row['status']==0)
                joc()->set_var('status','Duyệt');
            else
                joc()->set_var('status','Không duyệt');
            joc()->set_var('stt',$stt++);
            joc()->set_var('position',$row['position']);
            joc()->set_var('time_created',date('H:i, d/m/Y',$row['time_created']));
            if($row['img'])
			    $img = IMG::show('adv/'.date('Y/n',$row['time_created']).'/',$row['img']);
    		else 
                $img = 'webskins/icons/100x100.jpg';
            
           joc()->set_var('banner','<img width="120px;" src="'.$img.'" />');

            $html_page = '';
            if($row['page_home'] == 1)
                $html_page .='Trang chủ <br/>';
            if($row['page_cate'] == 1)
                $html_page .='Trang cấp hai <br/>';
            if($row['page_detail'] == 1)
                $html_page .='Trang chi tiết <br/>';        
            joc()->set_var('page',$html_page);  

            $html_adv .= joc()->output('adv');
       }
       $sql = 'select count(id) as total from banner';
       news()->query($sql);
       $total = news()->fetch();
       $total = $total['total'];
       joc()->set_var('total_rowcount',$total);
	   joc()->set_var('paging','<li>Tổng số: '.$total.'</li>'.Paging::paging ($total,$item_per_page,10));
       joc()->set_var('adv',$html_adv);
       $html= joc()->output("AdminAdv");
	   joc()->reset_var();
	   return $html;
	} 
    function adminAddAndEdit(){
        joc()->set_file('AdminAdv', Module::pathTemplate()."backend/adv_add_edit.htm");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('ckeditor.js'	, 'webskins/richtext/ckeditor/ckeditor.js', 'footer', 'js');			
		Page::registerFile('jquery.adapter.js' , 'webskins/richtext/ckeditor/adapters/jquery.adapter.js', 'footer', 'js');	
		Page::setHeader("Tạo quảng cáo", "Tạo quảng cáo", "Tạo quảng cáo");
		Page::registerFile('admin_news.js', Module::pathJS().'admin_bds.js' , 'footer', 'js');	
		joc()->set_var('begin_form' , Form::begin(false, "POST", ' enctype="multipart/form-data" onsubmit="return checkForm()"'));
		joc()->set_var('end_form' 	, Form::end());
		joc()->set_var('url_current', '?app=news&page=admin_adv&cmd=store');
		joc()->set_var('link_add',  '?app=news&page=admin_adv&cmd=create');
        $id = SystemIO::get('id','int','');
		if($id || $id !=0)
			$row=news()->selectOne('banner','*',"id=".$id);
		
		joc()->set_var('id',$id);
		if($row['img'])
			joc()->set_var('img1', $img = IMG::show('adv/'.date('Y/n',$row['time_created']).'/',$row['img']));
		else 
			joc()->set_var('img1', 'webskins/icons/100x100.jpg');
        if($row['img2'])
			joc()->set_var('img2', 'banner/'.$row['img2']);
		else 
			joc()->set_var('img2', 'webskins/icons/100x100.jpg');    		
		joc()->set_var('title',$row['title']? htmlspecialchars($row['title']) :'');
        joc()->set_var('position',$row['position']);
        joc()->set_var('link',$row['link']);
        if($row['page_home'] == 1)
            joc()->set_var('check1','checked="check"');
        if($row['page_cate'] == 1);
            joc()->set_var('check2','checked="check"');
        if($row['page_detail'] == 1)
            joc()->set_var('check3','checked="check"');
       
		$html= joc()->output("AdminAdv");
		joc()->reset_var();
		return $html;	
	
    }
}