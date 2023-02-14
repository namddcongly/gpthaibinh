<?php
include 'application/user/includes/contact_of_user_model.php';
class Contact extends Form
{
	function __construct()
	{
		Form::__construct($this);
	}
	function on_submit()
	{
		$objContactOfUser=new ContactOfUser();
		$title=SystemIO::post('title','def');
		$content=SystemIO::post('content','def');
		$email=SystemIO::post('email','def');
		$full_name=SystemIO::post('full_name','def');
		$arrNewData=array('title'=>$title,'content'=>$content,'email'=>$email,'user_name'=>$full_name,'time_created'=>time());
		if($title && $content)
		{
			if($objContactOfUser->insertData($arrNewData))
			{
				echo '<script type="text/javascript">alert("Bạn đã gửi thành công!")</script>';
				echo '<meta http-equiv="refresh" content="0;url='.ROOT_URL.'" />';
			}
		}
	}
	function index()
	{
		Page::setHeader("Liên hệ với tòa soạn", "Liên hệ với tòa soạn", "Liên hệ với tòa soạn");
		Page::registerFile('main.css','webskins'.DS.'skins'.DS.'news'.DS.'css'.DS.'main.css' , 'header', 'css');
		Page::registerFile('home.css','webskins'.DS.'skins'.DS.'news'.DS.'css'.DS.'home.css' , 'header', 'css');
		joc()->set_file('Contact', Module::pathTemplate('user')."/frontend/contact.htm");
		joc()->set_var('begin_form' , Form::begin(false, "POST", 'onsubmit="return checkForm()"'));
		joc()->set_var('end_form' 	, Form::end());
		$html= joc()->output("Contact");
		joc()->reset_var();
		return $html;
	}
}