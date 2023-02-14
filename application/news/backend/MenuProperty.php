<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

require(APPLICATION_PATH.'news'.DS.'includes'.DS."category_property_model.php");

if(!UserCurrent::havePrivilege('ADMIN_LAYOUT'))
{
    Url::urlDenied();
}

class MenuProperty extends Form
{
	private $properties;
	
	private $cateObj;
	
	private $msg = "";
	
	private $prop = array();
	
	private $id;
	
	function __construct()
	{
		$this->cateObj = SystemIO::createObject('CategoryPropertyModel');
		
		$this->id = SystemIO::get('id', 'int', 0);
				
		Form::__construct($this);
		
		$this->prop = $this->cateObj->PropertyOne($this->id);
		
		$this->properties = $this->cateObj->getList();
	}
	
	function on_submit()
	{
		$data = SystemIO::post('data', 'def', array());
		
		if(count($data) > 0)
		{
			if(trim($data['name']) != "")
			{
				if(trim($data['value']) != "")
				{
					if($this->id > 0) // sua
					{
						if( $data['groups'] > 0 && $this->cateObj->exist("groups=".$this->id))
							$this->msg = "Trong nhóm vẫn còn nhóm con. Bạn phải xóa hoặc chuyển hết sang nhóm khác trước khi sửa nhóm";
						else 
						{
							if($this->cateObj->updateData($data, $this->id))
								$this->msg = "Cập nhật thành công";
							else 
								$this->msg = "Cập nhật không thành công";
						}
					}
					else //them moi
					{
						if($this->cateObj->insertData($data))
							$this->msg = "Thêm mới thành công";
						else 
							$this->msg = "Thêm mới không thành công";
					}
					header("Location:".ROOT_URL.'?'.$_SERVER['QUERY_STRING']);
				}
				else 
					$this->msg = "Chưa nhập giá trị thuộc tính";
			}
			else 
				$this->msg = "Chưa nhập tên thuộc tính.";
		}
	}
	
	function index()
	{
		Page::setHeader("Thuộc tính doanh mục", "property, category", "Quản lý thuộc tính danh mục");		
		
		Page::registerFile('admin_news.js', Module::pathJS().'admin_news.js' , 'footer', 'js');	
		
		joc()->set_file('Pro', Module::pathTemplate()."backend".DS."category_property.htm");		
		
		joc()->set_block('Pro', 'PROPERTY');
		
		if($this->id == 0)
			joc()->set_var('submit', 'Tạo mới');
		else 
			joc()->set_var('submit', 'Cập nhật');
		
		$type = array(
						0 => array("id" => 0, "name" => "Checkbox"),
						1 => array("id" => 1, "name" => "Radio"),
						2 => array("id" => 2, "name" => "Input"),
						3 => array("id" => 3, "name" => "Textarea"),
						4 => array("id" => 4, "name" => "SelectBox")
		);
		joc()->set_var('type_option', SystemIO::selectBox($type, array($this->prop['type']), "id", "id", "name"));
		
		joc()->set_var('begin_form', Form::begin(false));	
			
		joc()->set_var('end_form', Form::end());	
		
		joc()->set_var('msg', $this->msg);	
		
		joc()->set_var('display', $this->msg == "" ? "display:none" : "");	
		
		joc()->set_var('names' , $this->prop['name']);
		
		joc()->set_var('values' , $this->prop['value']);
		
		joc()->set_var('styles' , htmlspecialchars($this->prop['styles']));
		
		joc()->set_var('alias' , $this->prop['alias']);
		
		
		$html_pro = "";
		
		$group = array();
		
		if(is_array($this->properties) && count($this->properties) > 0)
		{
			$properties;
			
			foreach ($this->properties as $proper)
			{
				if($proper['groups'] == 0)
				{
					$properties[] = $proper;
					
					foreach ($this->properties as $p)					
						if($p['groups'] == $proper['id'])
							$properties[] = $p;
					
				}
			}
			$i = 0;
			foreach ($properties as $property)
			{
				$name 	= $_group = $property['name'];
				
				if($property['groups'] == 0 )
				{
					$name = "";
					$group[] = $property;					
				}
				else 	
				{
					$name .= " (giá trị:\"<b>".$property['value']."</b>\")";
					$_group = "---";
				}
								
				joc()->set_var('stt'	, $property['id']);
				joc()->set_var('bg'		, $i%2 == 0 ? 'bg-grey': "");
				joc()->set_var('name'	, $name);
				joc()->set_var('group'	, $_group);
				joc()->set_var('type'	, $type[$property['type']]['name']);
				
				$html_pro .= joc()->output('PROPERTY');	
				$i++;
			}
		}	
		
		joc()->set_var('group_option', SystemIO::selectBox($group, array($this->prop['groups']),"id"));	
			
		joc()->set_var('PROPERTY', $html_pro);
		
		$html= joc()->output("Pro");
		joc()->reset_var();
		return $html;
	}
}

?>