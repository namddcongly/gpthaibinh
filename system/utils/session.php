<?php
class session {
	var $expire		= 720; //in minute, 0.5 day :D
	var $limiter 	= 'private'; //public,private,nocache,private_no_expire


	public function __construct() {
		if (! $expire) {
			if (function_exists ( 'session_cache_expire' )) {
				session_cache_expire ( $this->expire );
			}
		}

		if (! $limiter) {
			if (function_exists ( 'session_cache_limiter' )) {
				session_cache_limiter ( $this->limiter );
			}
		}

		session::start ();
	}


	static function start() {
		return session_start ();
		//bool session_regenerate_id  ([ bool $delete_old_session= false  ] )
		return session_regenerate_id();
	}


	static function register($var, $value) {
		$_SESSION [$var] = $value;
	}


	static  function unRegister($var) {
		return session_unregister ( $var );
	}


	static  function isRegistered($var) {
		return (session_is_registered ( $var ));
	}
	static  function get($var) {
		if ($this->isRegistered ( $var ))
		$this->$var = $_SESSION [$var];
		else if (isset ( $GLOBALS [$var] ))
		$this->$var = $GLOBALS [$var];
		else if (isset ( $_REQUEST [$var] ))
		$this->$var = $_REQUEST [$var];
		else
		$this->$var = "empty";
		return ($this->$var);
	}


	static  function id() {
		return (session_id ());
	}
	static function name() {
		//Default, name:PHPSESSID
		return session_name ();
	}
	static function savePath() {
		/*
		 * In default, name:PHPSESSID
		 */
		return session_save_path();
	}
	static  function destroy() {
		session_destroy ();
		$_COOKIE = array (); // to change identity of the cookie
	}

}