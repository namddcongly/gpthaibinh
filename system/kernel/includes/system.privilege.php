<?php
class SystemPrivilege
{
	function __construct()
	{


	}
	/**
	 * Lay danh quyen rieng le cua mot user
	 * @param unknown_type $user_id
	 */
	static function getPrivilegeByUserID($user_id)
	{
		settype($user_id,'int');
		if(!$user_id) return array();
		$privileges=array();
		$sql="SELECT privilege_id FROM user_privilege WHERE user_id={$user_id}";
		dbObject()->setProperty('db','user_privilege');
		dbObject()->query($sql);
		$list_privilege=dbObject()->fetchAll('');
		if(count($list_privilege))
		foreach($list_privilege as $_temp)
		{
			$privileges[]=$_temp['privilege_id'];
		}
		return 	$privileges;

	}
	static function getPrivilegeByGroupID($group_id)
	{
		settype($group_id,'int');
		if(!$group_id) return array();
		$privileges=array();
		$sql="SELECT privilege_id FROM group_privilege WHERE group_id ={$group_id}";
		dbObject()->setProperty('db','group_privilege');
		dbObject()->query($sql);
		$list_privilege=dbObject()->fetchAll($sql);
		if(count($list_privilege))
		foreach($list_privilege as $_temp)
		{
			$privileges[]=$_temp['privilege_id'];
		}
		return 	$privileges;

	}
	static function getAllPrivilegeOfUser($user_id)
	{
		settype($user_id,'int');
		if(!$user_id) return array();
		$privileges_of_user=self::getPrivilegeByUserID($user_id);

		$sql="SELECT GROUP_CONCAT(group_id) AS group_ids FROM group_user WHERE user_id = {$user_id}";
		dbObject()->setProperty('db','group_user');
		dbObject()->query($sql);
		$group=dbObject()->fetch('');
		if($group['group_ids'])// lay cac quyen trong group
		{
			$sql_g="SELECT privilege_id FROM group_privilege WHERE group_id IN ({$group['group_ids']})";
			dbObject()->setProperty('db','group_privilege');
			dbObject()->query($sql_g);
			$list_privilege=dbObject()->fetchAll('');
			if(count($list_privilege))
			foreach($list_privilege as $_temp)
			{
				if(!in_array($_temp['privilege_id'],$privileges_of_user))
				$privileges_of_user[]=$_temp['privilege_id'];
			}
		}
		$_privilege_ids = implode(',',$privileges_of_user);
		$arr_name_privilege=array();
		if($_privilege_ids){
			dbObject()->setProperty('db','privilege');
			$sql_privilege="SELECT name FROM privilege WHERE id IN ({$_privilege_ids})";
			dbObject()->query($sql_privilege);
			$list_name_privilege=dbObject()->fetchAll('');
			foreach($list_name_privilege as $_temp)
			{
				$arr_name_privilege[]=$_temp['name'];
			}
		}
		else
		return array();

		return $arr_name_privilege;
	}
	static function checkUserPermision($user_id,$privilege_id)
	{
		$privilege_of_user=self::getAllPrivilegeOfUser($user_id);
		if(count($privilege_of_user) && in_array($privilege_id,$privilege_of_user)) return true;
		return false;
	}
	static function checkGroupPermision($group_id,$privilege_id)
	{
		$privilege_of_group=self::getPrivilegeByGroupID($group_id);
		if(count($privilege_of_group) && in_array($privilege_id,$privilege_of_group)) return true;
		return false;
	}

}