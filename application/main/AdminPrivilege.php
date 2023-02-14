<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
if(!UserCurrent::havePrivilege('ADMIN_PRIVILEGE'))
{
	Url::urlDenied();
}
class AdminPrivilege extends Form
{
	function __construct()
	{
	}
	function on_submit()
	{

	}
	function index()
	{
		$cmd=SystemIO::get('cmd','def','admin_privilege');
		if($cmd=='admin_privilege')
		return $this->adminPrivilege();
		elseif($cmd=='allocation_privilege')
		return $this->allocationPrivilege();
		elseif($cmd=='not_found_user')
		return $this->notFoundUser();
		else
		return $this->adminPrivilege();
			
	}
	function notFoundUser()
	{
		joc()->set_file('AdminPrivilege', Module::pathTemplate()."not_found_user.htm");
		joc()->set_var('ListRow',$text_html);
		$html= joc()->output("AdminPrivilege");
		joc()->reset_var();
		return $html;
	}
	function adminPrivilege()
	{
		joc()->set_file('AdminPrivilege', Module::pathTemplate()."admin_privilege.htm");
		Page::setHeader("Quản trị quyền", "Quản trị quyền", "Quản trị quyền");
		joc()->set_var('begin_form' , Form::begin( false, "POST", 'onsubmit="return add_page()"'));
		joc()->set_var('end_form' 	, Form::end());
		require_once 'application/main/includes/privilege.php';
		$privilegeObj=new Privilege();
		$privilege_list=$privilegeObj->getList();
		joc()->set_block('AdminPrivilege','ListRow','ListRow');
		$text_html='';
		$stt=0;
		foreach($privilege_list as $row)
		{
			++$stt;
			joc()->set_var('stt',$stt);
			joc()->set_var('id',$row['id']);
			joc()->set_var('name',$row['name']);
			joc()->set_var('description',$row['description']);
			$text_html.=joc()->output('ListRow');
		}
		joc()->set_var('ListRow',$text_html);
		$html= joc()->output("AdminPrivilege");
		joc()->reset_var();
		return $html;
	}
	function allocationPrivilege()
	{
		joc()->set_file('AdminPrivilege', Module::pathTemplate()."allocation_privilege.htm");
		Page::registerFile('popup-css', 'webskins/javascript/admin.js' , 'header', 'js');
		Page::setHeader("Cập phát quyền cho user", "Cập phát quyền cho user", "Cập phát quyền cho user");
		require_once 'application/main/includes/privilege.php';
		require_once 'application/main/includes/group_user.php';
		require_once 'application/main/includes/user.php';
		require_once 'application/main/includes/group_privilege.php';
		require_once 'application/main/includes/user_privilege.php';
		/*Set var thong tin user*/
		$userObj=new User();
		$user_name=SystemIO::get('user_name','def');
		$user_id=SystemIO::get('user_id','int',UserCurrent::$current->data['id']);
		if($user_name)
		{
			$user_info=$userObj->readData('',"user_name='{$user_name}'");
		}
		else
		$user_info=$userObj->readData($user_id);
		$user_id	= $user_info['id'];
		if(!$user_info['id'])
		{
			Url::redirectUrl(array(),'?portal=main&page=admin_privilege&cmd=not_found_user');
		}

		joc()->set_var('user_name',$user_info['user_name']);
		joc()->set_var('user_id',$user_info['id']);


		$groupUserObj=new GroupUser();
		$groupPrivilegeObj= new GroupPrivilege();
		$userPrvilegeObj= new UserPrivilege();
		$list_group_of_user = $groupUserObj->getList('user_id='.$user_id);// lay danh sach nhom cua User
		$group_ids='';
		foreach($list_group_of_user as $_temp)
		{
			$group_ids.=$_temp['group_id'].',';
		}
		$group_ids=trim($group_ids,',');
		$list_privilege_of_groups=array();
		if($group_ids)
		$list_privilege_of_groups=$groupPrivilegeObj->getList('group_id IN ('.$group_ids.')');// lay cac quyen cua nhom

		$privilege_of_user=array();// mang chua toan bo quyen cua user
		$privilege_of_user_in_group=array();
		if(count($list_privilege_of_groups))
		foreach($list_privilege_of_groups as $_temp)
		{
			$privilege_of_user_in_group[]=$_temp['privilege_id'];
		}

			
		$privilegeObj=new Privilege();
		$privilege_list=$privilegeObj->getList();
		/*Set var cac quyen trong group*/
			
		joc()->set_block('AdminPrivilege','ListRowGroup','ListRowGroup');
		$text_html_group='';
		$stt_g=0;

		foreach($privilege_list as $_temp)
		{
			if(in_array($_temp['id'],$privilege_of_user_in_group)){

				joc()->set_var('name_g',$_temp['name']);
				joc()->set_var('description_g',$_temp['description']);
				++$stt_g;
				joc()->set_var('stt_g',$stt_g);
				$text_html_group .= joc()->output('ListRowGroup');
			}
		}

		joc()->set_var('ListRowGroup',$text_html_group);
		/*Lay quyen rieng le cua user*/
		$list_privlege_of_user=$userPrvilegeObj->getList('user_id='.$user_id);

		if(count($list_privlege_of_user))
		{
			foreach($list_privlege_of_user as $_temp)
			{
				if(!in_array($_temp['privilege_id'],$privilege_of_user))
				$privilege_of_user[]=$_temp['privilege_id'];
			}
		}

		joc()->set_block('AdminPrivilege','ListRow','ListRow');
		$text_html='';
		$stt=0;
		foreach($privilege_list as $row)
		{
			++$stt;
			joc()->set_var('id',$row['id']);

			if(in_array($row['id'],$privilege_of_user)){
				$checked='checked="checked"';
			}
			else $checked='';

			joc()->set_var('checked',$checked);
			joc()->set_var('stt',$stt);
			joc()->set_var('id',$row['id']);
			joc()->set_var('name',$row['name']);
			joc()->set_var('description',$row['description']);
			$text_html.=joc()->output('ListRow');
		}
		joc()->set_var('ListRow',$text_html);
		$html= joc()->output("AdminPrivilege");
		joc()->reset_var();
		return $html;
	}
}