<?php
class UserLogout
{
	function __construct()
	{
		UserCustomer::logOut();
		$url = urldecode(SystemIO::get('ref', 'get',''));
		if($url =="") $url = Url::buildUrlRewrite(false,'news','home');
		Url::redirectUrl(false,$url);

	}
}
?>