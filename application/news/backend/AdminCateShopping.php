<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once UTILS_PATH.'paging.php';
require_once 'application/news/includes/category_shopping.php'; 
class AdminCateShopping extends Form
{
	function __construct()
	{
		Form::__construct($this);
	}
	function index()
	{
		joc()->set_file('CateShopping', Module::pathTemplate()."backend/cate_shopping.htm");
		Page::setHeader("Quản trị danh mục mua sắm", "Quản trị danh mục mua sắm", "Quản trị danh mục mua sắm");
		joc()->set_var('begin_form' , Form::begin(false, "POST", 'onsubmit="return checkForm()"'));
		joc()->set_var('end_form' 	, Form::end());
		$CateShop=new CategoryShopping();
		$parent_id=SystemIO::get('parent_id','int',0);
		$parent_name=SystemIO::get('name','def','');
		joc()->set_var('cate_name',($parent_name ? 'Trong danh mục: <font color=red>'.$parent_name.'</font>' : 'Tất cả danh mục cập một' ));
		$wh='property=1 AND parent_id=0';
		if($parent_id)
			$wh=' property=1 AND parent_id='.$parent_id;
		$list_cate=$CateShop->getList($wh,'arrange ASC');
		$list_cate_level1=$CateShop->getList('parent_id=0','arrange ASC');
		joc()->set_var('option_cate1',SystemIO::getOption(SystemIO::arrayToOption($list_cate_level1,'id','name'),0));
		joc()->set_block('CateShopping','LIST','LIST');
		$html='';
		$stt=1;
		foreach($list_cate as $row)
		{
			if($row['parent_id']==0) $href='?app=news&page=admin_category_shopping&parent_id='.$row['id'].'&name='.$row['name'];
			else $href="javascript:;";
			joc()->set_var('href',$href);
			joc()->set_var('stt',$stt);
			joc()->set_var('name',$row['name']);
			joc()->set_var('id',$row['id']);
			joc()->set_var('arrange',$row['arrange']);
			++$stt;
			$html.=joc()->output('LIST');
		}
		joc()->set_var('LIST',$html);
		$html= joc()->output("CateShopping");
		joc()->reset_var();
		return $html;
		
	}
}