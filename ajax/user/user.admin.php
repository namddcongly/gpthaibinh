<?php
require_once 'application/user/includes/user.common.php';
$action=SystemIO::post('action','def','');
$id= SystemIO::post('id','int',0);
$userObj=new UserCommon();
switch($action)
{
	case 'lock':
		$type=SystemIO::post('type','def','');
		if($userObj->updateData(array('is_lock'=>$type),$id))
		echo 1;
		else echo 0;
		break;
}
?>