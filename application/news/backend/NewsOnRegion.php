<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

if(!UserCurrent::havePrivilege('NEWS_REGION'))
{
    Url::urlDenied();
}

require(APPLICATION_PATH.'news'.DS."includes".DS."region_model.php");

class NewsOnRegion
{
	private $regionObj;
	private $id;
	
	function __construct()
	{
		$this->regionObj = SystemIO::createObject('RegionModel');
		
		$this->id = SystemIO::get('id', 'int', 0);
	}
	function index()
	{
		Page::setHeader("Các tin trong vùng", "news, tin tức", "Các tin trong vùng");		
		
		joc()->set_file('NewsOnRegion'		, Module::pathTemplate()."backend".DS."news_on_region.htm");
		
		joc()->set_block('NewsOnRegion'		, 'LIST');
		
		Page::registerFile('admin_news.js'	, Module::pathJS().'admin_news.js' , 'footer', 'js');	
		
		$region = $this->regionObj->RegionOne($this->id);
		
		joc()->set_var('region_name' , $region['name']);
		
		joc()->set_var('begin_form' 		, Form::begin(false, 'POST', 'onsubmit="return filltext()"'));
		
		joc()->set_var('end_form' 			, Form::end());
		
		$html= joc()->output("NewsOnRegion");
		
		joc()->reset_var();
		
		return $html;
	}
}
?>