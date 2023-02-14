<?php
class Error
{
	function __construct()
	{
		
	}
	function index()
	{		
		joc()->set_file('Error', Module::pathTemplate('news')."frontend".DS."error.htm");	
		Page::setHeader('Ngoisao.vn - Trang không tôn tại','Ngoisao.vn - Trang không tồn tại','Ngoisao.vn - Trang không tồn tại');
		$html= joc()->output("Error");
		joc()->reset_var();
		return $html;
	}
}
?>