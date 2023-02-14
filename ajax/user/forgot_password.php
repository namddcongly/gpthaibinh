<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
$cmd=SystemIO::get('cmd', 'str','');
if($cmd == 'submit'){

	$email = SystemIO::post('email', 'str','');

	require_once 'application/user/includes/user.common.php';
	require_once 'system/utils/validate.php';
	$classUser = new UserCommon();
	$error = '';

	if(!$email){
		$error = 'Bạn chưa nhập email';
	}
	else if(!Validate::isEmail($email)){
		$error = 'Email bạn nhập không đúng định dạng';
	}
	else{
		if(!$user = $classUser->readData(0, 'email="'.$email.'"')){
			$error = 'Email bạn nhập không tồn tại';
		}
	}

	if($error){
		echo  $error;
		exit;
	}
	require_once 'system/utils/mail.class.php';
	$mail = new Mail();
	$title = 'Quen mat khau tai khoan thanh vien tren xahoi.com.vn';
	$time = time();
	$check = md5($email.$time.'joc@abc');
	$content = 'Chào bạn '.$user['full_name'].',<br><br>
				Bạn vừa gởi yêu cầu khôi phục lại mật khẩu tài khoản trên xahoi.com.vn.
				Để hoàn thành khôi phục lại mật khẩu xin bạn vui lòng nhấn vào đường link sau:
				'.ROOT_URL.Url::buildUrlRewrite(array('email' => $email, 'time' => $time, 'check'=> $check), 'main', 'forgot_password').'<br>
				Nếu sau 7 ngày bạn không nhấn vào đường link trên thì quá trình khôi phục mật khẩu sẽ không được thực hiện và tài khoản của bạn sẽ vẫn giữ mật khẩu cũ.<br>
				Để bảo mật, xin bạn vui lòng thay đổi mật khẩu ngay khi bạn đăng nhập vào bằng mật khẩu được tái tạo.<br><br>
				Chúc bạn thành công,<br>
				Xahoi.com.vn';
	$id = $mail->sendEmail($user['email'], $user['full_name'], $title, $content);
	if($id){
		echo 1;
	}
	else echo 'Có lỗi xảy ra. Vẫn chưa gửi được email thay đổi mật khẩu';
}
else{
	joc()->set_file('register', TEMPLATE_PATH.'user/frontend/forgot_password.htm');

	echo joc()->output('register');
	joc()->reset_var();
}
?>