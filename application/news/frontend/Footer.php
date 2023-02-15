<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
class Footer {
	function index()
	{
	   	joc()->set_file('Footer', Module::pathTemplate('news').'frontend/footer.htm');
		$html= joc()->output('Footer');
		joc()->reset_var();
		return $html;	
	}
}