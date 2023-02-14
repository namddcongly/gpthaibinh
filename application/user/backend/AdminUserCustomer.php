<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/user/includes/user.common.php';

class AdminUserCustomer extends Form
{
	function __construct(){
		Form::__construct($this);
	}
	function on_submit()
	{
		$phone = SystemIO::post('phone','def');
		$address= SystemIO::post('address','def');
		$nick_yahoo= SystemIO::post('nick_yahoo','def');
		$nick_skype=SystemIO::post('nick_skype','def');
		$full_name=SystemIO::post('full_name','def');
		/*Creat user*/

		$email=SystemIO::post('email','def');


		$userObj = new UserCommon();
		$user_id = SystemIO::get('user_id','int', 0);
		if($user_id){
			$arrNewData=array('phone'=>$phone,
							  'address'=>$address,
							  'nick_yahoo'=>$nick_yahoo,
							  'nick_skype'=>$nick_skype,
							  'full_name'=>$full_name,
							  'email'	=> $email,
			);
			if($userObj->updateData($arrNewData,$user_id))
			{
				$user =	$userObj->readData($user_id);
				UserCustomer::$current = new UserCustomer ($user);
				Url::redirectUrl(array('user_id' => $user_id), '',  'admin_user_customer', 'main');
			}
		}

	}
	function index()
	{
		Page::setHeader("Thông tin tài khoản & khởi tạo tài khoản ", "Thông tin tài khoản & khởi tạo tài khoản", "Thông tin tài khoản & khởi tạo tài khoản");
		joc()->set_file('AdminUser', Module::pathTemplate()."admin_user.htm");
		Page::registerFile('thickbox.css',WEBSKINS_PATH.'css/thickbox.css' , 'header', 'css');
		Page::registerFile('thickbox.js',JAVASCRIPT_PATH.'thickbox.js' , 'footer', 'js');

		$cmd=SystemIO::get('cmd','def','info');
		if($cmd=='edit'){
			if(!UserCurrent::havePrivilege('ADMIN_USER'))
			{
				Url::urlDenied();
			}
			return $this->editUser();
		}
		else
		{
			if(!UserCurrent::havePrivilege('ADMIN_USER'))
			{
				Url::urlDenied();
			}
			return $this->listUser();
		}
	}
	function editUser()
	{
		joc()->set_var('begin_form' , Form::begin( false, "POST", 'onsubmit="return checkData();"'));
		joc()->set_var('end_form' 	, Form::end());
		joc()->set_file('AdminUser', Module::pathTemplate('user')."backend/edit_user.htm");
		$userId = SystemIO::get('user_id', 'int', 0);
		if($userId){
			$classUser = new UserCommon();
			$user = $classUser->readData($userId);
			joc()->set_var('arr', $user);
		}
		$html= joc()->output("AdminUser");
		joc()->reset_var();
		return $html;
	}
	function listUser()
	{
		joc()->set_file('AdminUser', Module::pathTemplate('user')."/backend/list_user.htm");
		joc()->set_block('AdminUser','ListRow','ListRow');
		require_once UTILS_PATH.'paging.php';

		$userObj= new UserCommon();

		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;

		/*Search*/
		$wh='1=1';
		$arr_active=array('2'=>'Tình trạng Khóa','1'=>'Đang khóa','0'=>'Đang mở');
		$active=(int)SystemIO::get('active','int','2');
		$q=SystemIO::get('q','def','');
		$q=str_replace(array('%','"',"'"),array('','',''),$q);
		joc()->set_var('q',$q);
		if($active!=2)
		$wh.=" AND is_lock={$active}";
		if($q)
		$wh.=" AND (user_name LIKE '%{$q}%' OR email LIKE '%{$q}%' OR address LIKE '%{$q}%' OR phone = '$q')";

		joc()->set_var('action_option',SystemIO::getOption($arr_active,$active));
		$user_list=$userObj->getList($wh,'time_register DESC',$limit,'');
		$text_html='';

		foreach($user_list as $row)
		{
			joc()->set_var('stt',$stt);
			++$stt;
			joc()->set_var('user_id',$row['id']);
			joc()->set_var('user_name',$row['user_name']);
			joc()->set_var('email',$row['email']);
			joc()->set_var('time_register',date('H:i d/m/y',$row['time_register']));
			joc()->set_var('email',$row['email']);
			joc()->set_var('time_last_login',date('H:i d/m/y',$row['time_last_login']));
			joc()->set_var('address',$row['address']);
			joc()->set_var('phone',$row['phone']);

			if($row['is_lock']==0)  $lock='<a href="javascript:;" onclick="return lock('.$row['id'].',1);">Khóa</a>';
			else	$lock='<a href="javascript:;" onclick="lock('.$row['id'].',0);">Mở khóa</a>';

			joc()->set_var('lock',$lock);

			$text_html.=joc()->output('ListRow');
		}
		joc()->set_var('ListRow',$text_html);

		$totalRecord=$userObj->count($wh);
		joc()->set_var('paging','<li>Tổng số: '.$totalRecord.'</li>'.Paging::paging ($totalRecord,$item_per_page,10));

		$html= joc()->output("AdminUser");
		joc()->reset_var();
		return $html;
	}
}

?>
