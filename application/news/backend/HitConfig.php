<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
/*if(!UserCurrent::havePrivilege('HIT_CONFIG'))
{
    Url::urlDenied();
}*/


require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/includes/hit_model.php';
require_once 'application/news/includes/category_model.php';


class HitConfig extends Form
{
	private $hitObj;
	
	function __construct()
	{
	    $this->hitObj = new HitModel();
	    
		Form::__construct($this);
	}
	
	function on_submit()
	{
		$data = SystemIO::post("data", "arr" , array());
		
		if(count($data) > 0)
		{
			foreach($data as $key => $val)
			{
				if(isset($val["check"]))
				{
					//var_dump($val);
					$this->hitObj->updateData(array("arrange" => (int)$val[0]),0,"nw_id=".$key);
				}
			}
		}
	}
	
	function index()
	{
		Page::setHeader("Quản lý tin trang chủ giải trí", "news, tin tức", "Quản lý danh mục tin tức");		
		
		joc()->set_file('Configuration', Module::pathTemplate()."backend".DS."hit_config.htm");	
		
		Page::registerFile('admin_news.js', Module::pathJS().'admin_news.js' , 'footer', 'js');
		
		joc()->set_var('begin_form' , Form::begin(false, "POST"));
		
		joc()->set_var('end_form' , Form::end());
		
		joc()->set_block('Configuration' , 'HIT');
		
		$cate_id = SystemIO::get("cate_id","int",0);
		
		$time = time();
		
		$cond = "time_created > ".($time-345600);
		if($cate_id > 0)
			$cond .= " AND  cate_path like '%,$cate_id,%'";

			

		$hits = $this->hitObj->getList("*",$cond,"arrange DESC,hit DESC","0,30");
		
		$cateObj = new CategoryModel();
		$cates = $cateObj->getList();
		
		joc()->set_var("cate_option", SystemIO::selectBox($cates, array($cate_id),"id","id","name"));		
		
		$html_hit = "";
		
		if(count($hits) > 0)
		{
			$ids = "0";
			$newsObj = new BackendNews();
			foreach($hits as $hit)			
				$ids .= ",".$hit["nw_id"]; 

			if($ids != "0")
			{	
				
				if($cate_id)
					$news = $newsObj->getListHit("store","id,title","id IN($ids)");
				else 
					$news = $newsObj->getListHit("store","id,title","id IN($ids) AND type != 1");// vi chong trung 	
				
				foreach($hits as $hit)
				{
					if(!$news[$hit['nw_id']]["id"]) continue;
					joc()->set_var('id'		, $hit['nw_id']);
					joc()->set_var('title'	, $news[$hit['nw_id']]["title"]);
					joc()->set_var('hit'	, $hit['hit']);
					joc()->set_var('arrange' , $hit["arrange"]);
					$html_hit .= joc()->output('HIT');				
				}
			}
		}
		
		joc()->set_var('HIT', $html_hit);

		$html= joc()->output("Configuration");
		
		joc()->reset_var();
		
		return $html;
	}
}

?>