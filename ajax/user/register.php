<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
$cmd=SystemIO::get('cmd', 'str','');
if($cmd == 'submit'){
	$userName = SystemIO::post('user_name', 'str','');
	$password = SystemIO::post('password', 'str','');
	$verify_password = SystemIO::post('verify_password', 'str','');
	$email = SystemIO::post('email', 'str','');
	$phone = SystemIO::post('phone', 'str','');
	$address = SystemIO::post('address', 'str','');
	$full_name = SystemIO::post('full_name', 'str','');
	require_once 'application/user/includes/user.common.php';
	require_once 'system/utils/validate.php';
	$classUser = new UserCommon();
	$error = '';
	if(!$userName){
		$error[]= '- Bạn chưa nhập tên tài khoản';
	}else{
		if(!Validate::isUserName($userName)){
			$error[]= '- Tên tài khoản không được dùng các ký tự đặc biệt';
		}else
		if($classUser->readData(0, 'user_name="'.$userName.'"')){
			$error[] = '- Tài khoản bạn đăng ký đã tồn tại';
		}
	}
	if(!$password){
		$error[] = '- Bạn chưa nhập mật khẩu';
	}
	if(!$verify_password){
		$error[] = '- Bạn chưa nhập Xác nhận mật khẩu';
	}
	if($verify_password != $password){
		$error[] = '- Mật khẩu và xác nhận mật khẩu không giống nhau';
	}
	if(!$full_name){
		$error[] = '- Bạn chưa nhập họ tên đầy đủ';
	}
	if(!$email){
		$error[] = '- Bạn chưa nhập email';
	}
	else if(!Validate::isEmail($email)){
		$error[] = '- Email bạn nhập không đúng định dạng';
	}
	else{
		if($classUser->readData(0, 'email="'.$email.'"')){
			$error[] = '- Email bạn dùng đã được đăng ký cho tài khoản khác';
		}
	}
	if(!$phone){
		$error[] = '- Bạn chưa nhập điện thoại';
	}
	$type=SystemIO::get('type', 'str','');
	if($type != 'ajax'){
		$code=SystemIO::post('captcha_code','def');
		require_once UTILS_PATH.'captchar.php';
		$captcha= new Captcha(4);
		if(!$captcha->checkCaptcha($code,'user_register')){
			$error[] = '- Mã xác minh không đúng';
		}
	}

	if($error){
		echo '<b>'.implode("<br>", $error).'</b>';
		exit;
	}

	$arrData = array('user_name' => $userName,
					 'email' => $email,
					 'password' => UserCommon::encodePassword($password),
					 'phone'	=> $phone,
					 'full_name' => $full_name,
					 'address'	=> $address,
					 'time_register' => time()
	);

	$id = $classUser->insertData($arrData);
	if($id){
		echo 1;
	}
}
else{
	joc()->set_file('register', TEMPLATE_PATH.'user/frontend/register_ajax.htm');

	echo joc()->output('register');
	joc()->reset_var();
}
?>