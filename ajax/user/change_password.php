<?php
if (defined(IN_JOC)) {
    die("Direct access not allowed!");
}
if (!UserCustomer::isLogin()) {
    echo 'Bạn phải đăng nhập mới có thể thực hiện chức năng này';
    exit;
}
$cmd = SystemIO::get('cmd', 'str', '');
if ($cmd == 'submit') {
    $old_password = SystemIO::post('old_password', 'str', '');
    $password = SystemIO::post('password', 'str', '');
    $verify_password = SystemIO::post('verify_password', 'str', '');

    require_once 'application/user/includes/user.common.php';
    $classUser = new UserCommon();
    $error = '';
    if (!$old_password) {
        $error[] = '- Bạn chưa nhập mật khẩu cũ';
    } else {
        $userName = UserCustomer::$current->data['user_name'];
        if (!$classUser->readData(0,
            'user_name="' . $userName . '" AND password = "' . $classUser->encodePassword($old_password) . '"')) {
            $error[] = 'Mật khẩu cũ không chính xác';
        }
    }
    if (!$password) {
        $error[] = '- Bạn chưa nhập mật khẩu mới';
    }
    if (!$verify_password) {
        $error[] = '- Bạn chưa nhập Xác nhận mật khẩu';
    }
    if ($verify_password != $password) {
        $error[] = '- Mật khẩu mới và xác nhận mật khẩu không giống nhau';
    }
    if ($error) {
        echo implode("\n", $error);
        exit;
    }
    $arrData = array('password' => UserCommon::encodePassword($password), 'time_last_change_password' => date('Y-m-d H:i:s'));
    $id = $classUser->updateData($arrData, UserCustomer::$current->data['id']);
    if ($id) {
        echo 1;
    }
} else {
    joc()->set_file('register', TEMPLATE_PATH . 'user/frontend/change_password.htm');

    echo joc()->output('register');
    joc()->reset_var();
}
?>