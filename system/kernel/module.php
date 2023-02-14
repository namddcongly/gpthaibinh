<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class Module
{
	public static $portal;

	static function pathModule($portal = "")
	{
		if($portal != "")
		self::$portal = $portal;
		return APPLICATION_PATH.self::$portal.DS;
	}
	static function pathTemplate($portal = "")
	{
		if($portal != "")
		return TEMPLATE_PATH.$portal.DS;
		return TEMPLATE_PATH.self::$portal.DS;
	}
	static function pathCSS($portal = "")
	{
		if($portal != "")
		return 'webskins'.DS.'skins'.DS.$portal.DS.'css'.DS;
		return 'webskins'.DS.'skins'.DS.self::$portal.DS.'css'.DS;
	}
	static function pathJS()
	{
		return 'webskins'.DS.'skins'.DS.self::$portal.DS.'js'.DS;
	}
	static function pathImages()
	{
		return 'webskins'.DS.'skins'.DS.self::$portal.DS.'images'.DS;
	}
	static function pathSystemJS()
	{
		return 'webskins'.DS.'javascript'.DS;
	}
	static function pathSystemCSS()
	{
		return 'webskins'.DS.'css'.DS;
	}
}
?>