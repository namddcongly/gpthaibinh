<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

if(!UserCurrent::havePrivilege('NEWS_REGION'))
{
    Url::urlDenied();
}

require(APPLICATION_PATH.'news'.DS."includes".DS."category_model.php");
require(APPLICATION_PATH.'news'.DS."includes".DS."region_model.php");
require(APPLICATION_PATH.'news'.DS."includes".DS."category_region_model.php");
require(UTILS_PATH.'pagination.php');

class NewsRegion extends Form
{
	private $msg = "";
	private $id;
	private $cateObj;
	private $categories;
	
	private $regionObj;
	private $cateRegionObj;
	private $regions;
	private $region;
	private $selected;
	private $keyword;
	private $categoryID;
	private $page_no;
		
	function __construct()
	{				
		$this->id = SystemIO::get('id', 'int', 0);
		
		$this->keyword = SystemIO::get('query', 'str', '');
		
		$this->categoryID = SystemIO::get('categoryID', 'int', 0);
		
		$this->cateObj = SystemIO::createObject('CategoryModel');
		
		$this->categories = $this->cateObj->getList();
		
		$this->regionObj = SystemIO::createObject('RegionModel');
		
		$this->cateRegionObj = SystemIO::createObject('CategoryRegionModel');
		
		$this->categories = $this->cateObj->getList("", "arrange asc");
		
		$condition = ($this->keyword == "" ? "" : "b.name like'%".$this->keyword."%'");
		
		$condition .= ($this->categoryID > 0 ? ($condition != "" ? " AND " : "")."a.cate_id=".$this->categoryID: "");
		
		$this->page_no 	= SystemIO::get('page_no', 'int', 1);
		
		$item_per_page = 10;
		
		$this->regions = $this->cateRegionObj->getList($condition, "id DESC", ($this->page_no-1)*$item_per_page . "," . $item_per_page);
		
		Form::__construct($this);
		
		if($this->id)
		{
			$this->region = $this->regionObj->RegionOne($this->id);
			$this->selected = $this->cateRegionObj->getSelected("cate_id", "region_id=".$this->id,"","","cate_id");
		}
	}
	
	function on_submit()
	{		
		$data 		= SystemIO::post('data' , 'arr', array());
			
		$cate 		= SystemIO::post('cate' , 'arr', array());
		
		if(!isset($_POST['update_region']))
		{
			if($data['name'] != "")
			{
				$length = count($cate);
				if($length > 0)
				{
					if($this->id > 0)
					{
						if($this->regionObj->updateData($data, $this->id))
						{						
							$this->cateRegionObj->deleteData(0, "region_id=".$this->id." AND cate_id NOT IN(".implode(",", $cate).")");
							
							$check = false;
							
							for ($i=0; $i< $length; $i++)
							{
								$insert['region_id'] = $this->id;
								$insert['cate_id'] = $cate[$i];
								$insert['property'] = 1;
								
								if(!$this->cateRegionObj->exist("region_id=".$this->id." AND cate_id=".$cate[$i]))
									$check = $this->cateRegionObj->insertData($insert);	
								else 
									$check = true;
							}
							if($check)
								$this->msg = "Cập nhật thành công <meta http-equiv=\"refresh\" content=\"0;url=".ROOT_URL."?portal=news&page=admin_news_region&id=".$this->id."\">";
							else 
								$this->msg = "Cập nhật không thành công";
						}
						else 
							$this->msg = "Có lỗi xảy ra trong quá trình thao tác";
					}
					else 
					{
						$regionID = $this->regionObj->insertData($data);
						if($regionID > 0)
						{
							for ($i=0; $i< $length; $i++)
							{
								$insert['region_id'] = $regionID;
								$insert['cate_id'] = $cate[$i];
								$insert['property'] = 1;
								
								if($this->cateRegionObj->insertData($insert))
									$this->msg = "Thêm mới thành công <meta http-equiv=\"refresh\" content=\"0;url=".ROOT_URL."?portal=news&page=admin_news_region"."\">";
								else 
									$this->msg = "Thêm mới không thành công";
							}
						}
						else 
							$this->msg = "Có lỗi xảy ra trong quá trình thao tác";
					}
				}
				else 
					$this->msg = "Chưa chọn danh mục chứa vùng";
			}
			else 
				$this->msg = "Chưa đặt tên vùng";
		}
		else 
		{
			$update = SystemIO::post('update', 'arr', array());
			$status = SystemIO::post('status', 'arr', array());
			
			if(count($status) > 0)
			{
				$check = false;
				foreach ($update as $key => $up)
				{
					$data["number_record"] 	= (int)$data["number_record"];
					$data["arrange"] 		= (int)$data["arrange"];
					
					if(isset($status[$key]))
						$check = $this->cateRegionObj->updateData($up, $key);
				}
				$this->msg = $check ? "Cập nhật thành công <meta http-equiv=\"refresh\" content=\"1;url=".ROOT_URL."?portal=news&page=admin_news_region"."\">" : "Cập nhật không thành công";
			}
			else 
				$this->msg = "Chưa chọn item để cập nhật";
		}
		$this->region = $data;
		$this->select = $cate;
	}
	function index()
	{
		Page::setHeader("Quản lý vùng của danh mục", "news, tin tức", "Quản lý danh mục tin tức");		
		
		joc()->set_file('REGION', Module::pathTemplate()."backend".DS."news_region.htm");	
		
		joc()->set_block('REGION', 'LIST');
		
		Page::registerFile('admin_news.js'	, Module::pathJS().'admin_news.js' , 'footer', 'js');	
		
		joc()->set_var('begin_form' 		, Form::begin(false, 'POST', 'onsubmit="return filltext()"'));
		
		joc()->set_var('begin_form_update' 	, Form::begin(false, 'POST', 'onsubmit="return filltext()"'));
		
		joc()->set_var('end_form' 			, Form::end());
		
		joc()->set_var('end_form_update' 	, Form::end());
		
		joc()->set_var('add_link'			, $this->id > 0 ? "?portal=news&page=admin_news_region&show=1" : "javascript:;");
		
		joc()->set_var('submit'				, $this->id == 0 ? "Thêm mới" : "Cập nhật");
		
		$selected = count($this->selected) > 0 ? array_keys($this->selected) : array();
		
		joc()->set_var('category'			, $this->selectTag($this->categories, $selected , "id"));	
		
		joc()->set_var('category_option'	, $this->selectTag($this->categories, array($this->categoryID) , "id"));
		
		joc()->set_var('region_name' 		, $this->region['name']);
		
		joc()->set_var('region_description' , $this->region['description']);
		
		joc()->set_var('keyword' 			, $this->keyword);
		
		joc()->set_var('msg', $this->msg);	
		
		$show = SystemIO::get('show', 'int', 0);
		
		joc()->set_var('show'	, ($this->id == 0 && $show == 0)? "display:none" : "");	
		
		joc()->set_var('display', $this->msg == "" ? "display:none" : "");	
		
		$html_region = "";
	
		if(count($this->regions) > 0)
		{
			foreach ($this->regions as $reg)
			{
				joc()->set_var('stt' 		 	, $reg['region_id']);
				joc()->set_var('id' 		 	, $reg['id']);
				joc()->set_var('name' 		 	, $reg['name']);
				joc()->set_var('description' 	, $reg['description']);
				joc()->set_var('cate_name'		, $this->categories[$reg['cate_id']]['name']);
				joc()->set_var('skin_name' 		, $reg['skins_type']);
				joc()->set_var('number_record' 	, $reg['number_record']);
				joc()->set_var('arrange' 		, $reg['arrange']);
				joc()->set_var('publish' 		, $reg['property']==1? "Bỏ xuất bản" : "Xuất bản");
				joc()->set_var('link_delete'	, "ajax.php?path=news&fnc=region_delete&region_id=".$reg['region_id'].'&cate_id='.$reg['cate_id']);
				joc()->set_var('link_publish'	, "ajax.php?path=news&fnc=region_publish&region_id=".$reg['region_id'].'&cate_id='.$reg['cate_id'].'&value='.($reg['property'] == 1 ? 0 : 1));
				
				$html_region .= joc()->output('LIST');
			}
		}

		$paging 		= new Pagination();
		
		$paging->total 	= (int)$GLOBALS['TOTAL'];
		
		$paging->page 	= $this->page_no-1;
		
		$paging->portal = "news";
		
		$paging->per_page = 10;
		
		$ext = ($this->keyword != "" ? "&query=".$this->keyword : "").($this->categoryID > 0 ? "&categoryID=".$this->categoryID : "");
			
		$paging->pagename 	= "admin_news_region";
	
		joc()->set_var("paging" , $paging->create1(($this->keyword != "" ? "&query=".$this->keyword : "").($this->categoryID > 0 ? "&categoryID=".$this->categoryID : "")));
		
		joc()->set_var('LIST', $html_region);
		
		$html= joc()->output("REGION");
		
		joc()->reset_var();
		
		return $html;
	}
	
	function selectTag($array, $checked, $field_check="id")
	{
		$select = '';
		
		if(count($array) > 0)
		{
			foreach ($array as $cat)
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
			
			foreach ($categories as $arr)
			{
				$tab = "- ";
				
				for($j = 1; $j < 6; $j++)
					if($arr['cate_id'.$j] > 0)
						$tab .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
									
				if(in_array($arr[$field_check], $checked))
					$select .= '<option selected="selected" value="'.$arr['id'].'">'.$tab.$arr['name'].'</option>';
				else
					$select .= '<option value="'.$arr['id'].'">'.$tab.$arr['name'].'</option>';
			}
		}
		return $select;
	}
}

?>