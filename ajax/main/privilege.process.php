<?php
require_once 'application/main/includes/privilege.php';
require_once 'application/main/includes/group_privilege.php';
require_once 'application/main/includes/user_privilege.php';
$action=SystemIO::post('action','def','');
$id= SystemIO::post('id','int',0);
$privilegeObj=new Privilege();
switch($action)
{
	case 'delete':
		if($privilegeObj->delData($id)){
			$userPrivilegeObj=new UserPrivilege();
			$userPrivilegeObj->delMultiData('privilege_id='.$id);
			$groupPrivilegeObj=new GroupPrivilege();
			$groupPrivilegeObj->delMultiData('privilege_id='.$id);
			echo 1;
		}
		else
		echo 0;
		break;
	case 'load-data':
		$row=$privilegeObj->readData($id);
		echo '<ul>
			<li><span style="width:120px;">Tên quyền </span><input  type="text"  name="name" id="name" value="'.$row['name'].'"/> Tên quyền gồm các chữ cái A-Z_</li>
			<li><span style="width:120px;">Mô tả quyền</span><input  type="text" name="description" id="description" value="'.$row['description'].'" style="width:350px;"/> Mô tả về quyền</li>
			<li><span style="width:120px;">&nbsp;</span><input  type="button" value="Sửa quyền" class="button" onclick="updateData('.$row['id'].');"/></li>
		</ul>';
		break;
	case 'insert-data':
		$name=SystemIO::post('name','def');
		$description=SystemIO::post('description','def');
		if(ereg('^[A-Z_]+$',$name))
		{
			if($privilegeObj->insertData(array('name'=>$name,'description'=>$description)))
			echo 1;
			else
			echo 0;
		}
		else
		{
			echo 0;
		}
		break;
	case 'update-data':
		$name=SystemIO::post('name','def');
		$description=SystemIO::post('description','def');
		if(ereg('^[A-Z_]+$',$name))
		{
			if($privilegeObj->updateData(array('name'=>$name,'description'=>$description),$id))
			echo 1;
			else
			echo 0;
		}
		else
		{
			echo 0;
		}
	case 'set-privilege':
		$userPrivilegeObj=new UserPrivilege();
		$privilege_ids=SystemIO::post('privilege_ids','def');
		$user_id=SystemIO::post('user_id','int');



		$type=SystemIO::post('type','def');
		if($type==1){
			if($privilege_ids && $user_id)
			{

				$list_privilege_of_user=$userPrivilegeObj->getList("user_id={$user_id} AND privilege_id IN ({$privilege_ids})");

				$__arr_privilege_old=array();
				if(count($list_privilege_of_user))
				foreach($list_privilege_of_user as $_temp)
				{
					$__arr_privilege_old[]=$_temp['privilege_id'];
				}
				$sql="INSERT INTO user_privilege (user_id,privilege_id) VALUES ";
				$__value='';
				$__arr_privilege=explode(',',$privilege_ids);
				for($i=0; $i < count($__arr_privilege); ++$i)
				{
					if(!in_array($__arr_privilege[$i],$__arr_privilege_old))
					$__value.='('.$user_id.','.$__arr_privilege[$i].'),';
				}
				$__value=trim($__value,',');
				if($__value){
					$sql.=$__value;
					if($userPrivilegeObj->query($sql))
					echo 1;
					else
					echo 0;
				}
				else
				echo -1;
			}
			else
			echo 0;
		}
		else
		{
			if($privilege_ids && $user_id){
				$cond="user_id = {$user_id} AND privilege_id IN ({$privilege_ids})";
				if($userPrivilegeObj->delMultiData($cond))
				echo 1;
				else
				echo 0;
			}
			else
			echo 0;
		}
		break;
}