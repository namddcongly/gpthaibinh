<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class AdminNewsImport extends Form{
	function __construct(){
		//Form::__construct($this);
	}
	function index()
	{
		joc()->set_file('temp', Module::pathTemplate()."backend/admin_news_import.htm");
			
		$html= joc()->output("temp");
		joc()->reset_var();
		return $html;	
	}	
}