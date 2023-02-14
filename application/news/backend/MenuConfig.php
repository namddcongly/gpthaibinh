<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
if(!UserCurrent::havePrivilege('ADMIN_LAYOUT'))
{
    Url::urlDenied();
}

require(APPLICATION_PATH.'news'.DS."includes".DS."category_model.php");

require(APPLICATION_PATH.'news'.DS."includes".DS."category_property_model.php");

class MenuConfig extends Form
{
	private $msg = "";
	
	private $category = array();
	
	private $categories = array();
	
	private $cateObj;
	
	private $id;
	
	function __construct()
	{
		$this->id = SystemIO::get('id', 'int', 0);
		
		$this->cateObj = SystemIO::createObject('CategoryModel');
		
		//$cond = $this->id < 2 ? "cate_id1=0" : "cate_id1=".$this->id;
				
		Form::__construct($this);
		
		if($this->id > 0)
			$this->category = $this->cateObj->CategoryOne($this->id);
			
		$this->categories = $this->cateObj->getList("cate_id1=0", "arrange asc");
	}
	
	function on_submit()
	{
		$data = SystemIO::post('data', 'arr', array());
		if(count($data) > 0)
		{
			if($this->id > 0)
				$this->cateObj->updateData(array("block_home" => "","order_cate" => "0","number" => 0),0, "cate_id1=".$this->id." AND cate_id2=0");
			else 
				$this->cateObj->updateData(array("block_home" => "","order_cate" => "0","number" => 0),0, "cate_id1=0");
			
			foreach ($data as $key => $value)			
			{
				if($value['status'] == 1)
				{
					$value_name = $value['name'];
					
					unset($value['status']);
					
					unset($value['name']);
										
					if($this->cateObj->updateData($value, $key))
						$this->msg .= "Cập nhật thành công danh mục <b>".$value_name."</b><br />";
					else 
						$this->msg .= "Cập nhật không thành công danh mục <b>".$value_name."</b><br />";
				}
			}
		}
	}
	
	function index()
	{
		Page::setHeader("Quản lý danh mục tin tức", "news, tin tức", "Quản lý danh mục tin tức");		
		
		joc()->set_file('Configuration', Module::pathTemplate()."backend".DS."category_config.htm");	
		
		joc()->set_block('Configuration', 'CONFIG');
		
		Page::registerFile('admin_news.js', Module::pathJS().'admin_news.js' , 'footer', 'js');	
		
		joc()->set_var('begin_form' , Form::begin(false, 'POST'));
		
		joc()->set_var('end_form' , Form::end());

		
				
		if($this->id == 0)
			$configs = $this->categories;
		else 
			$configs = $this->cateObj->getList("cate_id1=".$this->id." AND cate_id2=0");
		
		$html_conf = "";
			
		if(is_array($configs) && count($configs) > 0)
		{			
		    //var_dump($configs);
			foreach ($configs as $conf)
			{			
				$layt = array();
					
				joc()->set_var('id'				, $conf['id']);
				
				joc()->set_var('cate_name'		, $conf['name']);
				
				joc()->set_var('checked'		, ($conf['property']&8)==8 ? ' checked="checked" ' : '');
				
				joc()->set_var('possition'		, $conf['order_cate']);
				
				
				
				joc()->set_var('block_option'	, $conf['block_home']);
				
				joc()->set_var('number'			, $conf['number']);
				
				$html_conf .= joc()->output('CONFIG');
			}		
		}
		
		joc()->set_var('CONFIG', $html_conf);
		
		joc()->set_var('display', $this->msg == "" ? "display:none" : "");	
		
		joc()->set_var('show'	, $this->id ==0 ? "display:none" : "");	
		
		joc()->set_var('msg'	, $this->msg);	
		
		joc()->set_var('option_category'	, SystemIO::selectBox($this->categories, array($this->id),"id"));	
		
		$html= joc()->output("Configuration");
		
		joc()->reset_var();
		
		return $html;
	}
}

?>