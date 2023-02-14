<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
if(!UserCurrent::havePrivilege(CHANGE_PASSWORD)){
	echo 'Bạn không có quyền thay đổi mật khẩu';
	exit;
}

$cmd=SystemIO::get('cmd', 'str','');
$userId = SystemIO::get('user_id', 'int', 0);
require_once 'application/user/includes/user.common.php';
$classUser = new UserCommon();
$user = $classUser->readData($userId);
if(!$user){
	echo 'Tài khoản bạn muốn đổi mật khẩu không tồn tại';
	exit;
}
if($cmd == 'submit'){
	$password = SystemIO::post('password', 'str','');
	$verify_password = SystemIO::post('verify_password', 'str','');

	$error = '';
	if(!$password){
		$error[] = '- Bạn chưa nhập mật khẩu mới';
	}
	if(!$verify_password){
		$error[] = '- Bạn chưa nhập Xác nhận mật khẩu';
	}
	if($verify_password != $password){
		$error[] = '- Mật khẩu mới và xác nhận mật khẩu không giống nhau';
	}
	if($error){
		echo implode("\n", $error);
		exit;
	}
	$arrData = array( 'password' => UserCommon::encodePassword($password),'time_last_change_password' => date('Y-m-d H:i:s'));
	$id = $classUser->updateData($arrData, $userId);
	if($id){
		echo 1;
	}
}
else{
	joc()->set_file('register', TEMPLATE_PATH.'user/backend/change_password.htm');
	joc()->set_var('user_name', $user['user_name']);
	joc()->set_var('user_id', $userId);
	echo joc()->output('register');
	joc()->reset_var();
}
?>