<?php
class UserCustomer
{
	public static $current = false;
	public $data = false;

	public function __construct($user = false) {
		UserCustomer::$current = &$this;
		if(!$user){
			if (isset($_SESSION[NAME_SESSION_USER_CUSTOMEM]['id'])&& $_SESSION[NAME_SESSION_USER_CUSTOMEM]['id'] > 0)
			{
				$user = $_SESSION[NAME_SESSION_USER_CUSTOMEM];
				if ($user ['last_online_time'] < time() - TIME_RELOAD_USER) {
					require_once 'application/user/includes/user.common.php';
					$userObj=new UserCommon();
					$user =	$userObj->readData($_SESSION[NAME_SESSION_USER_CUSTOMEM]['id']);
				}
				UserCustomer::$current->data = $user;
				self::registerSession (NAME_SESSION_USER_CUSTOMEM, $user );
				return;
			} else {
				UserCustomer::$current->data = false;
				return;
			}
		} else {
			$user ['last_online_time']=time();
			self::registerSession (NAME_SESSION_USER_CUSTOMEM, $user );
			UserCustomer::$current->data = $user;
			return;
		}
	}

	/**
	 * 	registerSession
	 *	@name: Session Name
	 *	@value: Session Value
	 */
	static function registerSession($name, $value) {
		$_SESSION[$name] = $value;
	}
	/**
	 * 	clearSession
	 */
	static function clearSession() {
		unset ( $_SESSION [NAME_SESSION_USER_CUSTOMEM] );
	}
	/**
	 * 	isLogin
	 */
	static public function isLogin() {
		return isset( $_SESSION [NAME_SESSION_USER_CUSTOMEM]);
	}
	/**
	 *	logIn
	 *	@user
	 */
	static public function logIn($user) {
		UserCustomer::registerSession (NAME_SESSION_USER_CUSTOMEM, $user );
		UserCustomer::$current = new UserCustomer ($user);
	}

	/**
	 *	logOut
	 */
	static public function logOut() {
		self::clearSession();
	}
}
UserCustomer::$current = new UserCustomer();
?>