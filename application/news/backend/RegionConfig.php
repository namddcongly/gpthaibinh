<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

require_once UTILS_PATH.'image.upload.php';
require_once APPLICATION_PATH.'news'.DS."includes".DS."category_model.php";
require_once APPLICATION_PATH.'news'.DS."includes".DS."category_region_model.php";
require_once APPLICATION_PATH.'news'.DS."includes".DS."category_model.php";
require_once APPLICATION_PATH.'news'.DS."includes".DS."region_store_model.php";

require_once 'application/news/backend/includes/backend.news.php';

class RegionConfig extends Form
{

	
	function __construct()
	{
		Form::__construct($this);
	}
	
	function on_submit()
	{
		$updates = SystemIO::post("arrage","arr",array());
		$regnewsObj =  new RegionStoreModel();
		
		$region_id = SystemIO::get("region_id","int",0);
		$cate_id = SystemIO::get("cate_id","int",0);
		
		if(count($updates) > 0)
	    	foreach ($updates as $n_id => $value)
	    		$regnewsObj->updateData(array("arrange" => $value),0,'nw_id='.$n_id.' AND region_id='.$region_id);

	    $cateObj = new CategoryModel();	
	    
	    $cate = $cateObj->CategoryOne($cate_id);
	    
	    @file_get_contents('http://xahoi.com.vn/'.$cate['alias']."/?cached=1");    
	}
	
	function index()
	{
		Page::setHeader("Quản lý tin trong vung", "news, tin tức", "Quản lý danh mục tin tức");		
		
		joc()->set_file('Configuration', Module::pathTemplate()."backend".DS."region_config.htm");	
		
		joc()->set_block('Configuration','SLIDE');
				
		joc()->set_var('begin_form' , Form::begin(false, "POST"));
		
		joc()->set_var('end_form' , Form::end());
		
		$cateObj =  new CategoryModel();
		$regnewsObj =  new RegionStoreModel();
		
		$cates = $cateObj->getList("cate_id2=0");
		
		$cate_id = SystemIO::get("cate_id","int",0);
		$region_id = SystemIO::get("region_id","int",0);

		$option_cate="";
		
		foreach ($cates as $cate)
		{
			if($cate["cate_id1"] == 0 )
			{
				$option_cate .= '<option '.($cate['id'] == $cate_id ? 'selected="selected"' : '').' value="'.$cate["id"].'">- '.$cate['name'].'</option>';
				foreach ($cates as $cat)
				{
					if($cat['cate_id1'] == $cate['id'])
						$option_cate .= '<option '.($cat['id'] == $cate_id ? 'selected="selected"' : '').' value="'.$cat['id'].'">------- '.$cat['name'].'</option>';
				}
			}
		}
		
		$news_ids = $regnewsObj->getList("region_id=$region_id");
		
		$html_slide = "";
		if(count($news_ids) > 0)
		{
			$newsObj = new BackendNews();
			$ids = "0";
			foreach($news_ids as $nid)
				$ids .= ",".$nid['nw_id'];
			
			$news = $newsObj->getListStore("id IN($ids)");	

			if(count($news) > 0)
			{
				foreach ($news as $n)
				{
					joc()->set_var('title', $n['title']);
					
					$arrange = 0;
					
					foreach($news_ids as $nid)
						if($nid['nw_id'] == $n['id'])
							$arrange = $nid['arrange'];
					joc()->set_var('arrage', $arrange);
					joc()->set_var('id', $n['id']);
					
					$html_slide .= joc()->output('SLIDE');
				}
			}			
		}
		joc()->set_var("SLIDE", $html_slide);
		joc()->set_var('option_cate', $option_cate);
		
		$html= joc()->output("Configuration");
		
		joc()->reset_var();
		
		return $html;
	}
}

?>