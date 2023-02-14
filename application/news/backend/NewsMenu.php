<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
if(!UserCurrent::havePrivilege('ADMIN_CATEGORY'))
{
    Url::urlDenied();
}

require(APPLICATION_PATH.'news'.DS."includes".DS."category_model.php");

require(APPLICATION_PATH.'news'.DS."includes".DS."category_property_model.php");

class NewsMenu extends Form
{
	private $msg = "";
	
	private $category = array();
	
	private $categories = array();
	
	private $filters = array();
	
	private $cateObj;
	
	private $layoutObj;
	
	private $layouts;
	
	private $id;
	
	private $filter_id;
	
	private $keyword;
	
	function __construct()
	{
		$this->id = SystemIO::get('id', 'int', 0);
		
		$this->filter_id = SystemIO::get('filter_id', 'int', 0);
		
		$this->cateObj = SystemIO::createObject('CategoryModel');
		
		$this->layoutObj = SystemIO::createObject('LayoutModel');
		
		$this->layouts = $this->layoutObj->getLayout("name");
		
		$this->keyword = preg_replace('/[\'\"]/', '', SystemIO::get('query', 'str', ""));
				
		Form::__construct($this);
		
		$condition = ($this->keyword != "" ? "name like '%$this->keyword%'" : "");
		
		if($this->filter_id == 0)		
			$this->categories = $this->cateObj->getList(($condition == "" ? "" : $condition." AND " )." cate_id1=0", "arrange asc");		
		else
			$this->categories = $this->cateObj->getList(($condition == "" ? "" : $condition." AND " )."(cate_id1=".$this->filter_id." OR id=".$this->filter_id.")", "arrange asc");
			
		$this->filters = $this->cateObj->getList("cate_id1=0", "arrange asc");
		
		if($this->id > 0)
			$this->category = $this->cateObj->CategoryOne($this->id);
	}
	
	function on_submit()
	{		
		if(!isset($_POST['update']))
		{
			$data 		= SystemIO::post('data' , 'arr', array());
			
			$property 	= SystemIO::post('property' , 'arr', array());
	
			if($data['name'] != "")
			{			
				if($data['title'] != "")
				{
					if($data['keyword'] != "")
					{
						if($data['description'] != "")
						{	
							$data['property'] = 0;
							
							if(count($property) > 0)						
								foreach ($property as $key => $value)
									$data['property'] += (int) $value;						
								
							if($this->id > 0)
							{
								unset($data['property']);
								if(!isset($data['cate_id3']))
									$data['cate_id3'] = 0;
								if(!isset($data['cate_id3']))
									$data['cate_id4'] = 0;
							
								if($this->cateObj->updateData($data, $this->id))
									echo "";//header("Location:".ROOT_URL.'?'.$_SERVER['QUERY_STRING']);
								else 
									$this->msg = "Cập nhật không thành công";
							}
							else 
							{
								if($this->cateObj->insertData($data))
									header("Location:".ROOT_URL.'?'.$_SERVER['QUERY_STRING']);
								else 
									$this->msg = "Thêm mới không thành công";
							}
						}
						else 
							$this->msg = "Chưa nhập mô tả trang trong thẻ meta của danh mục ";
					}
					else 	
						$this->msg = "Chưa nhập từ khóa trong thẻ meta của danh mục ";
				}
				else 	
					$this->msg = "Chưa nhập tiêu đề trang của danh mục ";
			}
			else 	
				$this->msg = "Chưa nhập tên danh mục";
			$this->category = $data;
		}
		else 
		{
			$pos = SystemIO::post('pos', 'arr', array());
			
			if(count($pos) > 0)			
				foreach ($pos as $key => $p)				
					if(isset($p['id']))
						$this->cateObj->updateData(array("arrange" => $p['possition']) , (int)$key);
		}
	}
	function index()
	{
		Page::setHeader("Quản lý danh mục tin tức", "news, tin tức", "Quản lý danh mục tin tức");		
		
		joc()->set_file('MENU', Module::pathTemplate()."backend".DS."news_category.htm");	
		
		joc()->set_block('MENU', 'LIST');
		
		Page::registerFile('admin_news.js', Module::pathJS().'admin_news.js' , 'footer', 'js');	
		Page::registerFile('thickbox.js'  , Module::pathSystemJS().'thickbox.js' , 'footer', 'js');	
		Page::registerFile('thickbox.css'  , Module::pathSystemCSS().'thickbox.css' , 'header', 'css');	
		
		joc()->set_var('begin_form' 	, Form::begin(false, 'POST', 'onsubmit="return filltext()"'));
		
		joc()->set_var('begin_form_update', Form::begin(false, 'POST', 'onsubmit="return checkupdate()"'));
		
		joc()->set_var('end_form' 		, Form::end());
		
		joc()->set_var('end_form_update', Form::end());
		
		joc()->set_var('submit'			, $this->id == 0 ? "Thêm mới" : "Cập nhật");
		
		joc()->set_var('catename'		, $this->category["name"]);
		
		joc()->set_var('name_display'	, $this->category["name_display"]);
		
		joc()->set_var('catetitle'		, $this->category["title"]);
		
		joc()->set_var('catekeyword'	, $this->category["keyword"]);
		
		joc()->set_var('catedescription', $this->category["description"]);
		
		joc()->set_var('catealias'		, $this->category["alias"]);
		
		joc()->set_var('add_link'		, $this->id > 0 ? "?app=news&page=admin_news_category" : "javascript:;");
		
		joc()->set_var('query'			, $this->keyword);
		
		joc()->set_var('catearrange'	, isset($this->category["arrange"]) ? $this->category["arrange"] : 0);
			
		$cate_1 = array();
		
		$cate_2 = array();
		
		$html_menu = "";
		
		if(is_array($this->categories) && count($this->categories))
		{
			if($this->keyword == "")
			{
				foreach ($this->categories as $cat)
				{
					if($cat['cate_id1'] == 0)
					{
						$categories[] = $cat;
						
						foreach ($this->categories as $ca)
						{
							if($ca['cate_id1'] == $cat['id'] && $ca['cate_id2'] == 0)
							{
								$categories[] = $ca;
								foreach ($this->categories as $c)							
									if($c['cate_id2'] == $ca['id'])
										$categories[] = $c;
								
							}
						}
					}
				}
			}
			else 
				$categories = $this->categories;
			$i=0;
			
			if(count($categories) > 0)
			
			foreach ($categories as $cate)
			{
				joc()->set_var('stt'		, $cate['id']);
				
				if($cate['cate_id1'] == 0)
					$cate_1[] = $cate; 
				
				if($cate['cate_id1'] > 0 && $cate['cate_id2'] == 0)
					$cate_2[] = $cate;	
					
				$name = "";
								
				for($j = 1; $j < 6; $j++)
				{
					$tab = "";
					if($this->filter_id == 0)
						$link = "?app=news&page=admin_news_category";
					else 
						$link = "?app=news&page=admin_news";
					for ($l=1;$l<$j;$l++)
						$tab .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					if($cate['cate_name'.$j] != "" && $cate['cate_name'.$j] != "0")
						$name .= '- '.$tab.' <a class="directory" href="'.$link.'&filter_id='.$cate['cate_id'.$j].'">'.$cate['cate_name'.$j]."</a><br />";
					else 
					{
						$name .= '- '.$tab.' <a class="directory" href="'.$link.'&filter_id='.$cate['id'].'&cmd=news_store&cate_id='.$cate['id'].'">'.$cate['name'].'</a>';
						break;
					}
				}				
				
				joc()->set_var('name'		, $name);
				
				joc()->set_var('publish' 	, $cate['property'] & 1 == 1 ? 'Bỏ xuất bản' : 'Xuất bản');
				
				$level = 1;
				
				if($cate['cate_id2'] > 0) $level = 3;
				elseif($cate['cate_id1'] > 0) $level = 2;
				
				joc()->set_var('level' 		, $level);
				
				joc()->set_var('filter' 	, $this->filter_id > 0 ? "&filter_id=".$this->filter_id : "&filter=".$cate['cate_id1']);
				
				joc()->set_var('value' 		, 1);
				
				joc()->set_var('value_hp' 	, 8);
				
				joc()->set_var('value_dm' 	, 16);
				
				joc()->set_var('value_sp' 	, 4);
				
				joc()->set_var('value_menu' , 32);
				
				joc()->set_var('value_tab' 	, 64);
				
				joc()->set_var('cmd' 		, ($cate['property']&1)==1 ? 0 : 1);

				joc()->set_var('cmd_hp' 	, ($cate['property']&8)==8 ? 0 : 1);
				
				joc()->set_var('cmd_dm' 	, ($cate['property']&16)==16 ? 0 : 1);
				
				joc()->set_var('cmd_sp' 	, ($cate['property']&4)==4 ? 0 : 1);
				
				joc()->set_var('cmd_menu' 	, ($cate['property']&32)==32 ? 0 : 1);
				
				joc()->set_var('cmd_tab' 	, ($cate['property']&64)==64 ? 0 : 1);
				
				joc()->set_var('homepage' 	, ($cate['property']&8)==8 ? 'Bỏ hiển thị' : 'Hiển thị');
				
				joc()->set_var('category' 	, ($cate['property']&16)==16 ? 'Bỏ hiển thị' : 'Hiển thị');
				
				joc()->set_var('special' 	, ($cate['property']&4)==4 ? 'Bỏ hiển thị' : 'Hiển thị');
				
				joc()->set_var('menu_show'	, ($cate['property']&32)==32 ? 'Bỏ hiển thị' : 'Hiển thị');
				
				joc()->set_var('menu_tab'	, ($cate['property']&64)==64 ? 'Bỏ hiển thị' : 'Hiển thị');
				
				joc()->set_var('bg'			, $i%2==0 ? '' : 'bg-grey');
					
				joc()->set_var('title'		, $cate['title']);
				
				joc()->set_var('keyword'	, $cate['keyword']);
				
				joc()->set_var('desc'		, $cate['description']);
				
				joc()->set_var('url'		, $cate['alias']);
				
				joc()->set_var('possition'	, $cate['arrange']);
				
				$i++;
				
				$html_menu .= joc()->output('LIST');
			}
		}
		
		joc()->set_var('option_filter' 	, SystemIO::selectBox($this->filters, array($this->filter_id), "id","id", "name"));
		
		joc()->set_var('option_cate1' 	, SystemIO::selectBox($this->filters, array($this->category['cate_id1']), "id","id", "name"));
		
		joc()->set_var('option_cate2' 	, SystemIO::selectBox($cate_2, array($this->category['cate_id2']), "id","id", "name"));
		
		joc()->set_var('layout_option' 	, SystemIO::selectBox($this->layouts, array($this->category['layout']), "name","name", "name"));
		
		$catePObj = SystemIO::createObject('CategoryPropertyModel');

		joc()->set_var('properties', $this->show_property($catePObj->groupProperty(), (int)$this->category['property']));
		
		joc()->set_var('LIST', $html_menu);	
		
		joc()->set_var('msg', $this->msg);	
		
		joc()->set_var('display', $this->msg == "" ? "display:none" : "");	
		
		joc()->set_var('show'	, $this->id ==0 ? "display:none" : "");	
		
		$html= joc()->output("MENU");
		
		joc()->reset_var();
		
		return $html;
	}
	
	function show_property($data, $checked)
	{
		$html_property = "";
		if(count($data) > 0)
		{
			foreach ($data as $d)
			{
				$pro 	= $d['curl'];
				$items 	= $d['items'];
				
				$html_property .='<li>';
				$html_property .='	<label for="name">'.$pro['name'].'</label>';
				if(count($items) > 0)
				{
					foreach ($items as $it)
					{
						switch ($it['type'])
						{
							case 0:
								$html_property .= '<input type="checkbox" '.(($checked&$it['value']) == $it['value'] ? 'checked="checked"' : "").' name="'.$it['alias'].'" value="'.$it['value'].'"/> '.$it['name'];
								break;
							case 1:
								$html_property .= '<input type="radio" '.(($checked&$it['value']) == $it['value'] ? 'checked="checked"' : "").' name="'.$it['alias'].'" value="'.$it['value'].'"/> '.$it['name'];
								break;
							case 2:
								$html_property .= '<input type="text" name="'.$it['alias'].'" value="'.$it['value'].'"/> '.$it['name'];
								break;
							case 2:
								$html_property .= '<textarea name="'.$it['alias'].'" />'.$it['value'].'</textarea> '.$it['name'];
								break;
						}
					}
				}
				$html_property .='</li>';
			}
		}
		return $html_property;
	}
}

?>