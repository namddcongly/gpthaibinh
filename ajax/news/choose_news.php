<?php

$action=SystemIO::post('action','def','');

switch($action)
{
	case 'insert':
		$id=SystemIO::post('id','int',0);
		$cate_id=SystemIO::post('cate_id','int',0);
		$hit=SystemIO::post('hit','int',0);
		dbObject()->setProperty('news','store_week');
		
		if(dbObject()->count('id='.$id,'*'))
		{
			echo 2;
		}
		else
		{
			$sql="INSERT INTO store_week (id,cate_id,hit) VALUES ({$id},{$cate_id},{$hit})";
			if(dbObject()->query($sql))
				echo 1;
			else 
				echo 0;	
		}	
		break;
	case 'delete':
		$id=SystemIO::post('id','int',0);
		dbObject()->setProperty('news','store_week');
		$sql="DELETE FROM store_week WHERE id=".$id;
		if(dbObject()->query($sql))
			echo 1;
		else 
			echo 0;		
		break;	
		
}		