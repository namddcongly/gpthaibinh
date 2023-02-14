<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require(KERNEL_PATH.'page.php');
require(KERNEL_PATH.'module.php');
require(KERNEL_PATH.'form.php');
require(KERNEL_PATH.'system.config.php');

require(UTILS_PATH.'io.php');

require(APPLICATION_PATH.'main'.DS.'includes'.DS.'layout_model.php');
require(APPLICATION_PATH.'main'.DS.'includes'.DS.'module_model.php');
require(APPLICATION_PATH.'main'.DS.'includes'.DS.'page_model.php');

//require class database.

$portal 	= preg_replace("/[^a-zA-Z0-9-_]/" , "" , strtolower(isset($_GET['app']) ? $_GET['app'] : "main"));
$page   	= preg_replace("/[^a-zA-Z0-9-_]/" , "" , strtolower(isset($_GET['page']) ? $_GET['page'] : "admin_login"));

$cate_id   	= strtolower(isset($_GET['cate_id']) ? $_GET['cate_id'] : 0);
$cate_name  = strtolower(isset($_GET['title']) ? $_GET['title'] : "all");
$page_no   	= strtolower(isset($_GET['page_no']) ? $_GET['page_no'] : 0);
$url_uri 	= $_SERVER['REQUEST_URI'];
$url_host	= $_SERVER['HTTP_HOST'];
$cached   	= strtolower(isset($_GET['cached']) ? $_GET['cached'] : 0);
require(KERNEL_PATH.'caching.php');
$cacheObj = new Caching();
if(IS_MEMCACHED && $page!='review')
{
	if($cached == 0)
	{
		$content = $cacheObj->get_cache($url_host.$url_uri);
		if($content!=false)
		{
			$id = SystemIO::get('id', 'int', 0);
			echo $content;
			if($id > 0)	{
				$t= news()->query('UPDATE store_hit SET hit=hit+1 WHERE nw_id='.$id);
			}
			return;
		}
	}
}
Module::$portal 	= $portal;
$pageObj 	= SystemIO::createObject('Page');
$pmObj 		= SystemIO::createObject('PageModel');
if($pmObj->existPage("portal_name='$portal' AND name='".$page."'"))
{
	$tmp = $pageObj->getContent($portal, $page);
	$tmp = preg_replace('/\[\[(.*?)\]\]/', '', $tmp);
	//$tmp = preg_replace('/(\s+)/',' ', $tmp);

	echo $tmp;
	//if(strpos($url_uri,'/truc-tiep')) return;// không thuc hien cached
	if(IS_MEMCACHED && $cached == 0 && $page!='review'){
		if($page=='congly_home' || $page=='congly_cate' || $page=='congly_detail')
		$cacheObj->set_cache($url_host.$url_uri,$tmp);
	}
	if($cached==1 && IS_MEMCACHED)
	{
		$cacheObj->set_cache(str_replace('?cached=1','',$url_host.$url_uri),$tmp);
	}
}
else
	echo "Page request is not exist";
?>