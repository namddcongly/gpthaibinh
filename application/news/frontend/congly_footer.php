<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/news/frontend/includes/class.xml.php';
class Congly_footer {
	function index()
	{
	   	joc()->set_file('Congly_footer', Module::pathTemplate('news')."frontend/footer.htm");
		$html= joc()->output("Congly_footer");
		joc()->reset_var();
		return $html;	
	}
}