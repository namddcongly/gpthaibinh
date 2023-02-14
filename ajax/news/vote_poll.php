<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />		
<?php

require_once 'application/news/includes/poll_option_model.php';

$voted_id = SystemIO::post("option", "str", "0");
$poll_id = SystemIO::post("poll_id", "int", "0");

$poll_cookie = isset($_COOKIE["poll"])? $_COOKIE["poll"] : "0,";

if($poll_cookie == "0,")
	$poll_cookie = isset($_SESSION["poll"]) ? $_SESSION["poll"] : "0,";
	
if(strpos($poll_cookie, ",$poll_id,") === false)
{	
	$poll_cookie .= "$poll_id,";
	
	$opollObj = new PollOptionModel();
	
	if($voted_id != "0")
	{
		$sql = 'update poll_option set voted=voted+1 where id in('.$voted_id.')';
		$opollObj->mysqlQuery($sql);		
		
		$_SESSION["poll"] .= $poll_cookie;
	}
}	
setcookie("poll", $poll_cookie);