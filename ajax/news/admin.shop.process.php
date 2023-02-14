<?php
require_once 'application/news/includes/category_shopping.php';
$action=SystemIO::post('action','def','');
$user_info=UserCurrent::$current->data;
$cateShop=new CategoryShopping();

$id=SystemIO::post('id','int',0);
switch($action)
{
	case 'load-cate':
		$cate_id=SystemIO::post('cate_id','int');
		$cateShop=new CategoryShopping();
		$list_cate=$cateShop->getList('property=1 AND parent_id='.$cate_id,'arrange ASC',50,'id');
		echo SystemIO::getOption(SystemIO::arrayToOption($list_cate,'id','name'),0);
		break;
	case 'load-district':
		
		$province_id=SystemIO::post('province_id','int');
		$sql="SELECT * FROM district WHERE province_id=".$province_id;
		dbObject()->setProperty('news','district');
		dbObject()->query($sql);
		$list_privilege=dbObject()->fetchAll('');
		echo SystemIO::getOption(SystemIO::arrayToOption($list_privilege,'id','name'),0);
		break;
	case 'do-public':
		$type=SystemIO::post('type','int',0);
		if($type==0)
			$sql="UPDATE store_shopping SET time_public=0,censor_id=".$user_info['id']." WHERE id=".$id;
		else
			$sql="UPDATE store_shopping SET time_public=".time().",censor_id=".$user_info['id']." WHERE id=".$id;
		dbObject()->setProperty('news','district');
		if(dbObject()->query($sql))
			echo 1;
		else 
			echo 0;	
		break;			
}