<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

require_once UTILS_PATH.'image.upload.php';

require_once APPLICATION_PATH.'news'.DS."includes".DS."category_model.php";
require_once APPLICATION_PATH.'news'.DS."includes".DS."cate_xahoi_model.php";
require_once APPLICATION_PATH.'news'.DS."includes".DS."xahoi_model.php";

require_once 'application/news/backend/includes/backend.news.php';


class ConvertData extends Form
{	
	
	function __construct()
	{    
		Form::__construct($this);
	}
	
	function on_submit()
	{
		
	    
	}
	
	function index()
	{
		Page::setHeader("Chuyển đổi dữ liệu từ xahoi.com.vn", "news, tin tức", "Quản lý danh mục tin tức");		
		
		joc()->set_file('Convert', Module::pathTemplate()."backend".DS."convert_data.htm");	
						
		$category = new CategoryModel();
		$cate_id_convert='52,15,145,25,87';		
		$cat = $category->getList();
		
		$cate_xahoi = new CateXahoiModel();
		//$wh='id IN ('.$cate_id_convert.') OR cate_id1 IN('.$cate_id_convert.')';
		$wh='1=1';
		$cat_xh = $cate_xahoi->getList($wh);
		$option_xh = "";
		$option_ns = "";
		
		foreach ($cat_xh as $xh)
		{
			if($xh["cate_id1"] == 0)
				$option_xh .= '<option value="'.$xh["id"].'">-- '.$xh["name"].'</option>';
			foreach ($cat_xh as $x)
				if($x["cate_id1"] == $xh["id"])
					$option_xh .= '<option value="'.$x["id"].'">-------- '.$x["name"].'</option>';
		}
		
		foreach($cat as $c)
		{
			if($c["cate_id1"] == 0)
				$option_ns .= '<option value="'.$c["id"].'">-- '.$c["name"].'</option>';
			foreach ($cat as $ca)
				if($ca["cate_id1"] == $c["id"])
					$option_ns .= '<option value="'.$c["id"].",".$ca["id"].'">-------- '.$ca["name"].'</option>';
		}
		
		joc()->set_var('option_xahoi'	, $option_xh);
		joc()->set_var('option_ngoisao'	, $option_ns);
		
		$html= joc()->output("Convert");
		
		joc()->reset_var();
		
		return $html;
	}
}

?>