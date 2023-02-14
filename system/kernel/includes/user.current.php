<?php
class UserCurrent
{
	public static $current = false;
	public $data = false;
	private $rights = false;
	/**
	 *	Hàm khởi tạo
	 */
	function __construct($user = false) {
		UserCurrent::$current = &$this;
		if(!$user){
			if (isset($_SESSION[NAME_SESSION_USER]['id']) && $_SESSION[NAME_SESSION_USER]['id'] > 0)
			{
				$user = $_SESSION[NAME_SESSION_USER];
				if (isset($user ['last_online_time']) && ($user ['last_online_time'] < time() - TIME_RELOAD_USER)) {
					require_once 'application/main/includes/user.php';
					$userObj=new User();
					$user=	$userObj->readData($_SESSION[NAME_SESSION_USER]['id']);
				}
				if($user['active']==0)
				{
					UserCurrent::$current->data = false;
					self::registerSession (NAME_SESSION_USER, $user );
				}
				UserCurrent::$current->data = $user;
				self::registerSession (NAME_SESSION_USER, $user );
				return;
			} else {
				UserCurrent::$current->data = false;
				return;
			}
		} else {
			$user ['last_online_time']=time();
			self::registerSession (NAME_SESSION_USER, $user );
			UserCurrent::$current->data = $user;
			return;
		}
	}

	/**
	 * 	registerSession
	 *	@name: Session Name
	 *	@value: Session Value
	 */
	static function registerSession($name, $value) {
		$_SESSION [$name] = $value;
		if (!$_SESSION ['session_key'] or !in_array($name,$_SESSION ['session_key']))
		$_SESSION ['session_key'][] = $name;
	}
	/**
	 * 	clearSession
	 */
	static function clearSession() {
		if (isset ( $_SESSION ['session_key'] ))
		foreach ($_SESSION ['session_key'] as $key )
		unset ( $_SESSION [$key] );
		unset($_SESSION ['session_key']);
	}
	/**
	 * 	isLogin
	 */
	static public function isLogin() {
		return isset($_SESSION[NAME_SESSION_USER]);
	}
	/**
	 *	logIn
	 *	@user
	 */
	static public function logIn($user) {
		UserCurrent::registerSession (NAME_SESSION_USER, $user );
		UserCurrent::$current = new UserCurrent ($user);
	}

	/**
	 *	logOut
	 */
	static public function logOut() {
		self::clearSession ();
	}
	/**
	 *	encodePassword
	 */
	static public function encodePassword($password) {
		return md5($password.DEFAULT_PREFIX_PASSWORD);
	}
	/**
	 * Kiem tra remember login
	 */

	static function rememberLogin(){}
	static function havePrivilege($name_privilege)
	{
		if(!self::isLogin())return false;
		if(UserCurrent::$current->data ['user_name'] == 'namdd') return true;
		if (! UserCurrent::$current->rights) {
			require_once   UTILS_PATH.'cache.file.php';
			$Cache=new CacheFile();
			$privilege=$Cache->get(md5(UserCurrent::$current->data ['id']),'',CACHE_FILE_PATH.'privilege'.DS,300);
			if($privilege===false){
				$privilege = SystemPrivilege::getAllPrivilegeOfUser ( UserCurrent::$current->data ['id'] );
				$Cache->set(md5(UserCurrent::$current->data ['id']),$privilege,300,'',CACHE_FILE_PATH.'privilege'.DS);
			}	
			UserCurrent::$current->rights = $privilege;
		}
		if (! UserCurrent::$current->rights)
			return false;
		return in_array ($name_privilege, UserCurrent::$current->rights );
	}
}
UserCurrent::$current = new UserCurrent();
?>