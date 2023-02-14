<?php 
if(defined(IN_JOC)) die("Direct access not allowed!");
require(UTILS_PATH.'memcache.php');

class Caching
{
	private $memobj;	
	private $time_expire = 0;
	function __construct()	
	{		
		$this->memobj = SystemIO::createObject('Memcached');
	}
	function set_time_expire($value)
	{
		$this->time_expire = $value;
	}
	function set_cache($key,$value,$time_expire=86400)
	{
		return $this->memobj->setData($key,$value,false,$time_expire);
	}
	function get_cache($key)
	{
		$content = $this->memobj->getData($key);
		if($content != false)
		{
			return $content;
		}
		return false;
	}
}

?>