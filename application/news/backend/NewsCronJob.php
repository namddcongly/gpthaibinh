<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/news/backend/includes/backend.news.php';

class NewsCronJob
{
	function __construct()
	{
		
	}
	function index()
	{			
		$time_c = time();
		
		$newsObj=new BackendNews();
		
		$news = $newsObj->getListStore('time_public <= '.$time_c.' AND time_public >= '.($time_c - 300));
		
		if(count($news) > 0)
		{
			$cate_id = "";
			foreach($news as $n)			
				$cate_id .= trim($n['cate_path'], ",").",".trim($n['cate_other'],",").",";
			
			$cate_id = trim($cate_id, ",");
			
			$cates = $newsObj->getListCategory("id IN ($cate_id)");
			
			if(count($cates) > 0)
			{
				foreach ($cates as $cate)	
				{			
					@file_get_contents(ROOT_URL.$cate['alias']."/?cached=1");
					echo ROOT_URL.$cate['alias']."/?cached=1<br />";
				}					
			}
		}
	}
}
?>