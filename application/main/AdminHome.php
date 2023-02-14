<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
if(!UserCurrent::havePrivilege('ADMIN_HOME'))
{
	Url::urlDenied();
}

class AdminHome extends Form
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
		Page::setHeader("Trang chủ", "homepage", "trang chủ");

		joc()->set_file('HOME', Module::pathTemplate()."admin_home.htm");

		$html= joc()->output("HOME");
		joc()->reset_var();
		return $html;
	}
}

?>
