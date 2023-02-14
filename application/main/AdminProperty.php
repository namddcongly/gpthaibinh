<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

if(!UserCurrent::havePrivilege(ADMIN_PROPERTY))
{
	Url::urlDenied();
}
class AdminProperty extends Form
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
		Page::registerFile('css standard', Module::pathCSS().'standard.css' , 'header', 'css');
		Page::registerFile('css ja.menu' , Module::pathCSS().'ja.cssmenu.css' , 'header', 'css');
		Page::registerFile('jquery'		 , Module::pathJS().'jquery.js' , 'footer', 'js');

		Page::setHeader("Trang chủ"		 , "homepage", "trang chủ");

		joc()->set_file('HOME'			 , Module::pathTemplate()."admin_home.htm");

		$html= joc()->output("HOME");
		joc()->reset_var();
		echo $html;
	}
}

?>
