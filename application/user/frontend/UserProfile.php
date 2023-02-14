<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/user/includes/user.common.php';
if(!UserCustomer::isLogin()){
	Url::redirectUrl(false, ROOT_URL);
}
class UserProfile extends Form
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
		$user_id = UserCustomer::$current->data['id'];
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
				Url::redirectUrl('', '',  'profile', 'main');
			}
			else
			{
				echo '<script type="text/javascript">alert("Đã có lỗi xay ra, bạn vui lòng kiểm tra các thông tin đã nhập")</script>';
			}
		}
	}
	function index()
	{
		$cmd=SystemIO::get('cmd','def','profile');
		switch($cmd)
		{
			case 'profile':
				return $this->profile();
				break;
			case 'raovat':
				return $this->raovat();
				break;
		}
	}
	function profile()
	{

		Page::setHeader("Thông tin tài khoản", "Thông tin tài khoản", "Thông tin tài khoản");
		Page::registerFile('raovat.css','webskins'.DS.'skins'.DS.'raovat'.DS.'css'.DS.'raovat.css' , 'header', 'css');
		joc()->set_file('UserProfile', Module::pathTemplate('user')."/frontend/user_profile.htm");
		joc()->set_var('begin_form' , Form::begin( false, "POST"));
		joc()->set_var('end_form' 	, Form::end());
		Page::registerFile('user_profile.css',Module::pathCSS('bds').'user_profile.css' , 'header', 'css');
		Page::registerFile('thickbox.css',WEBSKINS_PATH.'css/thickbox.css' , 'header', 'css');
		Page::registerFile('thickbox.js',JAVASCRIPT_PATH.'thickbox.js' , 'footer', 'js');
		$user = UserCustomer::$current->data;
		joc()->set_var('arr', $user);
		joc()->set_var('menu', UserCommon::getMenuProfile('profile'));
		$html= joc()->output("UserProfile");
		joc()->reset_var();
		return $html;
	}
	function raovat()
	{

		Page::setHeader("Quản lý tin rao vặt", "Quản lý tin rao vặt", "Quản lý tin rao vặt");
		joc()->set_file('Raovat', Module::pathTemplate('user')."/frontend/user_raovat.htm");
		Page::registerFile('raovat.css','webskins'.DS.'skins'.DS.'raovat'.DS.'css'.DS.'raovat.css' , 'header', 'css');
		Page::registerFile('user_profile.css',Module::pathCSS('bds').'user_profile.css' , 'header', 'css');
		$user = UserCustomer::$current->data;
		joc()->set_var('arr', $user);
		joc()->set_var('menu', UserCommon::getMenuProfile('raovat'));
		require_once 'application/raovat/includes/store.model.php';
		$user = UserCustomer::$current->data;
		$storeModel=new Store();
		$wh="user_id={$user['id']}";
		$list_raovat=$storeModel->getList($wh,'time_public DESC','0,20');
		joc()->set_block('Raovat','ListRow','ListRow');
		$text_html='';
		if(count($list_raovat))
		{
			foreach($list_raovat as $row)
			{
				joc()->set_var('title',$row['title']);
				joc()->set_var('id',$row['id']);
				$href=Url::Link(array('id'=>$row['id'],'title'=>$row['title']),'raovat','detail_raovat');
				$href_edit=Url::Link(array('id'=>$row['id'],'title'=>$row['title']),'raovat','post_item');

				if($row['time_public'])
				$public="Đang hiển thị";
				else
				$public="Đang chờ duyệt";
				joc()->set_var('public',$public);
				joc()->set_var('href_edit',$href_edit);
				joc()->set_var('href',$href);
				$text_html.=joc()->output('ListRow');
			}
		}
		else
		$text_html='<li class="none-result">Bạn chưa có tin rao vặt nào!<li>';
		joc()->set_var('ListRow',$text_html);
		$html= joc()->output("Raovat");
		joc()->reset_var();
		return $html;

	}
}

?>
