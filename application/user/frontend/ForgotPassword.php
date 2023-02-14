<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/user/includes/user.common.php';
class ForgotPassword
{
	function index()
	{
		Page::setHeader("Lấy lại mật khẩu", "Lấy lại mật khẩu", "Lấy lại mật khẩu");
		$email = SystemIO::get('email', 'str', '');
		$check = SystemIO::get('check', 'str', '');
		$time = SystemIO::get('time', 'int', 0);
		if(md5($email.$time.'joc@abc') !== $check){
			//var_dump($time);
			//var_dump(md5('dinhthihanh84@gmail.com'.$time.'joc@abc'));
			$html = 'Có lỗi xảy ra, Bạn vẫn chưa lấy lại được mật khẩu';
		}
		else {
			if($time <= time() - 7*86400){
				$html = 'Thời hạn lấy lại mật khẩu đã hết';
			}
			else{
				require_once 'application/user/includes/user.common.php';
				$classUser = new UserCommon();
				$user = $classUser->readData(0, 'email="'.$email.'"');
				if(!$user){
					$html = 'Email này không tồn tại trong hệ thống';
				}
				else{
					$pass = $this->randomPassword();
					$update = $classUser->updateData(array('password' => $classUser->encodePassword($pass)), $user['id']);
					if($update){
						require_once 'system/utils/mail.class.php';
						$mail = new Mail();
						$title = 'Quen mat khau tai khoan thanh vien tren xahoi.com.vn';
						$check = md5($email.$time.'joc@abc');
						$content = 'Chào bạn '.$user['full_name'].',<br><br>
									Bạn vừa gởi yêu cầu khôi phục lại mật khẩu tài khoản trên xahoi.com.vn.<br>
									Thông tin tài khoản của bạn như sau:<br><br>
									    - Tên tài khoản: '.$user['user_name'].'<br>
									    - Mật khẩu: '.$pass.'<br><br>
									Để bảo mật, xin bạn vui lòng thay đổi mật khẩu ngay khi bạn đăng nhập.<br><br>
									Chúc bạn thành công,<br>
									Xahoi.com.vn';
						$send = $mail->sendEmail($user['email'], $user['full_name'], $title, $content);
						if($send){
							$html = 'Mật khẩu mới của bạn đã được gửi vào email "'.$email.'"';
						}
						else $html =  'Có lỗi xảy ra. Vẫn chưa gửi được email thay đổi mật khẩu';
					}
					else $html =  'Có lỗi xảy ra. Vẫn chưa gửi được email thay đổi mật khẩu';
				}
			}
		}
		return '<p>'.$html.'<br><br></p>';
	}
	function randomPassword() {
		$allow = "abcdefghijklmnopqrstuvwxyz0123456789";
		$i = 1;
		while ($i <= 7) {

			$max  = strlen($allow)-1;
			$num  = rand(0, $max);
			$temp = substr($allow, $num, 1);
			$ret  = $ret . $temp;
			$i++;
		}
		return $ret;
			
	}
}

?>
