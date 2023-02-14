<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
if(!UserCurrent::havePrivilege('ENTERTAINMENT_CONFIG'))
{
    Url::urlDenied();
}
require_once UTILS_PATH.'image.upload.php';
require_once APPLICATION_PATH.'news'.DS."includes".DS."entertain_config_model.php";
require_once 'application/news/backend/includes/backend.news.php';


class EntertainmentConfig extends Form
{
	private $enterObj;
	
	private $id;
	
	private $one;
	
	function __construct()
	{
	    $this->enterObj = new EntertainConfigModel();
	    
	    $this->id = SystemIO::get('id','int',0);
	    $cmd = SystemIO::get('cmd','str','');
	    $eid = SystemIO::get('eid','int', 0);
	    	    
	    if($cmd == "delete" && $eid > 0)
	    {
	        $o = $this->enterObj->EnterOne("image",$eid);
	        @unlink(PATH_IMAGE_SLIDE.$o['image']);
	        $this->enterObj->deleteData($eid);
	        header("Location:?portal=news&page=admin_entertainment_configuration");
	    }
	    
		Form::__construct($this);
		
		if($this->id > 0)
		    $this->one = $this->enterObj->EnterOne("*",$this->id);
	}
	
	function on_submit()
	{
		if(isset($_POST['updates']))
		{
			$update = SystemIO::post("update", "arr", array());
			
			foreach ($update as $id => $arrange)
				$this->enterObj->updateData(array("arrange" => $arrange ), $id);
				
			@file_get_contents("http://xahoi.com.vn/giai-tri/?cached=1");	
		}
		else 
		{
		    $data = SystemIO::post('data', 'arr',array());
		     
		    if($_FILES['upload']['name'] != "")
		    {
				$uploader = new Uploader();
				$uploader->setPath(PATH_IMAGE_SLIDE); 
				$uploader->setMaxSize(1500);
				$uploader->setFileType('custom',array('jpg','jpeg','png','gif'));
				
				$result = $uploader->doUpload('upload');	
				
				$data['image'] = (string)$result['name'];
		    }
	
		    if(trim($data['title']) != "")
		    {
		        if($this->id > 0)
		        {	        
	    	        $id = $this->id;
	    	        unset($data['id']);
	    	        if($data['image'] != "")	        
	    	            @unlink($data['tmp_image']);            
	    	        
	    	        unset($data['tmp_image']);
	    	        $this->enterObj->updateData($data, $id);
		        }
		        else 
		        {
		            $newsObj=new BackendNews();
		            
		            $news = $newsObj->getStoreOne($data['nw_id']);
		            
		            if($news['title'] != "")
		            {
		                $data['image_thumb'] = $news['img2'];
		                $data['date_created'] = $news['time_created'];
		                $data['description'] = $news['description'];
		                unset($data['tmp_image']);
		                $data['cate_path'] = $news['cate_path'];
		                $this->enterObj->insertData($data);
		            }
		        }
		    }
		    @file_get_contents("http://xahoi.com.vn/giai-tri/?cached=1");
		    header("Location:?portal=news&page=admin_entertainment_configuration");
		}
	    
	}
	
	function index()
	{
		Page::setHeader("Quản lý tin trang chủ giải trí", "news, tin tức", "Quản lý danh mục tin tức");		
		
		joc()->set_file('Configuration', Module::pathTemplate()."backend".DS."entertainment_config.htm");	
		
		Page::registerFile('admin_news.js', Module::pathJS().'admin_news.js' , 'footer', 'js');
		
		joc()->set_block('Configuration','SLIDE');
		
		joc()->set_var('begin_form' , Form::begin(false, "POST", ' enctype="multipart/form-data" onsubmit="return checkForm()"'));
		
		joc()->set_var('end_form' , Form::end());
		
		joc()->set_var('begin_form_update' , Form::begin(false, "POST"));
		
		joc()->set_var('end_form_update' , Form::end());
		
		$arr = array("0" => array("id" => 0, "name" => "Slide Top"),"1" =>array("id" => 1, "name" => "Slide Giữa"));
		
		joc()->set_var('option', SystemIO::selectBox($arr,array($this->one['type']),"id","id","name"));
		
		$type = SystemIO::get("type","int",0);
		
		$news = $this->enterObj->getList("*" , $type == -1 ? "" : "type=$type", "arrange ASC");

		$html_c = "";
		
		if(count($news) > 0)
		{
		    foreach ($news as $n)
		    {
		        joc()->set_var('id' , $n['id']);
		        joc()->set_var('title' , $n['title'] );
		        joc()->set_var('arrange' , $n['arrange']);
		        joc()->set_var('image' , PATH_IMAGE_SLIDE.$n['image']);
		        joc()->set_var('position_title' , $n['arrange_title']);
		        		        
		        $html_c .= joc()->output('SLIDE');
		    }
		}
		
		if($this->id > 0)
		{
		    joc()->set_var('news_id'              , $this->one['nw_id']);
		    joc()->set_var('news_title'           , $this->one['title']);
		    joc()->set_var('news_image'           , PATH_IMAGE_SLIDE.$this->one['image']);
		    joc()->set_var('news_arrange'         , $this->one['arrange']);
		    joc()->set_var('news_arrange_title'   , $this->one['arrange_title']);
		    joc()->set_var('news_position'        , $this->one['position_title']);
		}
		else 
		{
		    joc()->set_var('news_id'      , '');
		    joc()->set_var('news_title'   , '');
		    joc()->set_var('news_image'   , '');
		    joc()->set_var('news_arrange' , '');
		    joc()->set_var('news_arrange_title'   , '');
		    joc()->set_var('news_position', '');
		}
		
		joc()->set_var('SLIDE',$html_c);

		$html= joc()->output("Configuration");
		
		joc()->reset_var();
		
		return $html;
	}
}

?>