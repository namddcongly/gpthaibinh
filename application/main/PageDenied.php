<?php
if(defined(IN_JOC)) die("Direct access not allowed!");;
class PageDenied
{
	function __construct(){}
	function index()
	{
		Page::setHeader("Truy Cập không hợp lệ", "Truy Cập không hợp lệ", "Truy Cập không hợp lệ");
		joc()->set_file('PageDenied', Module::pathTemplate()."page_denied.htm");
		$html= joc()->output("PageDenied");
		joc()->reset_var();
		return $html;
	}
}

?>
