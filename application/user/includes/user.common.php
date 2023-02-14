<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class UserCommon extends DatabaseObject
{
	public $tableName='user';
	public $listField='id,user_name,full_name,password,email,avatar,phone,address,active,last_login,nick_skype,nick_yahoo,time_last_login,time_register,is_lock';

	function __construct()
	{
		$this->setProperty('com', $this->tableName);
	}

	function insertData($data)
	{
		$this->setNewData($data);
		return $this->insert();
	}

	function updateData($data, $id = 0 , $condition = "")
	{
		$this->setNewData($data);
		return $this->update($id, $condition);
	}

	function getList( $condition = "" , $order = "", $limit = "", $key = "")
	{
		return $this->select($this->listField, $condition, $order, $limit, $key);
	}
	function readData($id,$cond='')
	{
		settype($id,'int');
		if(!$id && ($cond=='' || $cond==null)) return array();
		$row=$this->selectOne($this->listField,$id,$cond);
		return $row;
	}
	function deleteData($id)
	{
		return $this->delete($id);
	}

	/**
	 *	encodePassword
	 */
	static public function encodePassword($password) {
		return md5($password.DEFAULT_PREFIX_PASSWORD);
	}

	function login($userName, $password){
		if(!$userName){
			$error[] = ' - Bạn chưa nhập tài khoản muốn đăng nhập';
		}
		if(!$password){
			$error[] = ' - Bạn chưa nhập mật khẩu';
		}
		if(!$error){
			$user = $this->readData(0, 'user_name="'.$userName.'"');
			if(!$user){
				$error[] = ' - Tài khoản này không tồn tại';
			}else
			{
				$password = $this->encodePassword($password);
				if($password != $user['password']){
					$error[] = ' - Mật khẩu bạn nhập không đúng, vui lòng kiểm tra lại';
				}
				else if($user['is_lock'] == 1){
					$error[] = ' - Tài khoản này đang bị khóa, hãy liên hệ với ban quản trị để được trợ giúp';
				}
			}
		}
		if($error){
			return $error;
		}
		else
		{
			$this->updateData(array('time_last_login' => time()), $user['id']);
			UserCustomer::logIn($user);
			return true;
		}
	}

	static function linkLogout($urlReturn = ''){
		if($urlReturn){
			$arr = array('ref' => $urlReturn);
		}
		return Url::buildUrlRewrite($arr, 'main', 'logout');
	}

	static function getMenuProfile($pageCurrent){
		$arrPage = array('profile' => array('url' => Url::Link('', 'main', 'profile'),
											'name' => 'Thông tin tài khoản'
											),
						 'raovat'	=>  array('url' => Url::Link(array('cmd'=>'raovat'), 'main', 'profile'),
											'name' => 'Rao vặt'
											)
											);
											foreach ($arrPage as $key => $page){
												$listMenu .= '<li';
												if($key == $pageCurrent)
												$listMenu .= ' class="current"';
												$listMenu .= '><span><a title="'.$page['name'].'" href="'.$page['url'].'">'.$page['name'].'</a></span></li>'	;
											}
											return $listMenu;
	}
}