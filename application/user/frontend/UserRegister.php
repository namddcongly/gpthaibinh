<?php
class UserRegister
{
	function __construct()
	{

	}
	function index()
	{
		Page::setHeader("Thông tin tài khoản", "Đăng ký thành viên", "Đăng ký thành viên");
		joc()->set_file('UserRegister', Module::pathTemplate('user')."/frontend/register.htm");
		//Page::registerFile('user_profile.css',Module::pathCSS('bds').'user_profile.css' , 'header', 'css');
		require_once UTILS_PATH.'captchar.php';
		$captcha= new Captcha(4);
		$src=$captcha ->getCaptcha(false,false,'user_register');
		joc()->set_var('captcha',$src);
		$html= joc()->output("UserRegister");
		joc()->reset_var();
		return $html;
	}
}
?>