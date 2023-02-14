<?php
require_once 'application/raovat/includes/store.model.php';
$user = UserCustomer::$current->data;
$action=SystemIO::post('action','def');
$storeModel=new Store();
switch($action)
{
	case 'delete':
		$id=SystemIO::post('id','int');
		if($storeModel->delData($id))
		echo 1;
		else
		echo 0;
		break;
}