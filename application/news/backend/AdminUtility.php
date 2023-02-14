<?php
ini_set('display_errors',0);
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/main/includes/user.php';
require_once UTILS_PATH.'paging.php';
require_once UTILS_PATH.'image.upload.php';
class AdminUtility extends Form
{
	function __construct()
	{
		Form::__construct($this);
	}

	function on_submit()
	{

		$newsObj=new BackendNews();
		$user_info=UserCurrent::$current->data;
		$id=SystemIO::post('id','int',0);
		$name_topic=SystemIO::post('name_topic','def');
		$description_topic=SystemIO::post('description_topic','def');
		$number= SystemIO::post('number','int');
		$date_start=SystemIO::post('date_start','def');
		$date_end=SystemIO::post('date_end','def');
		$add_topic=SystemIO::post('add_topic','def');
		$arrNewData=array(
			'user_id'=>$user_info['id'],
			'name_topic'=>$name_topic,
			'description_topic'=>$description_topic,
			'number'=>$number,
			'date_start'=>  date('Y-m-d',strtotime(str_replace('/','-',$date_start))),
			'date_end'=>date('Y-m-d',strtotime(str_replace('/','-',$date_end))),
			'add_topic'=>$add_topic,
			'is_public' => SystemIO::post('is_public','def',0),
			'time_created'=>date('Y-m-d H:i:s'),
			'time_updated'=>date('Y-m-d- H:i:s'),
		);

		if($id)
		{
			if($newsObj->updateData('register_topic',$arrNewData,'id='.$id))
			{
				Url::redirectUrl(array(),'?app=news&page=admin_register_topic&cmd=admin_topic');
			}
		}
		else
		{
			if($newsObj->insertData('register_topic',$arrNewData))
			{
				Url::redirectUrl(array(),'?app=news&page=admin_register_topic&cmd=admin_topic');
			}

		}

	}
	function index()
	{
		$cmd=SystemIO::get('cmd','str','add_edit');
		$id=SystemIO::get('id','int',0);
		switch($cmd)
		{
			case 'admin_topic':
				return $this->adminTopic();
				break;
			case 'add_edit':
				return $this->addAndEdit($id);
				break;
		}
	}
	function adminTopic()
	{
		$user_info=UserCurrent::$current->data;
		joc()->set_file('AdminTopic', Module::pathTemplate()."backend/register_topic.htm");
		Page::setHeader("Đăng ký đề tài", "Đăng ký đề tài", "Đăng ký đề tài");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');
		Page::registerFile('popup-css',	 Module::pathSystemCSS().'popup.css' , 'header', 'css');
		Page::registerFile('popup-js', JAVASCRIPT_PATH.'popup.js' , 'footer', 'js');
		Page::registerFile('jqDnR', JAVASCRIPT_PATH.'jqDnR.js' , 'footer', 'js');
		$newsObj=new BackendNews();
		$userObj=new User();
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q = SystemIO::get('q','def');
		joc()->set_var('q',$q);
		$wh = '';
		if(UserCurrent::havePrivilege('CENSOR_TOPIC'))
			$wh = 'is_public = 1';
		else
			$wh = 'user_id = '.$user_info['id'];

		$wh = 'is_public = 1';

		$list_data=$newsObj->getListData('register_topic','*',$wh,'id DESC',$limit);
		$list_user = $userObj->userIdToNameAll();
		joc()->set_block('AdminTopic','ListRow','ListRow');
		$txt_html = '';
		foreach($list_data as $row)
		{
			if($row['status'] == 1)
				$bg = '#EBBFF2';
			else
				$bg ='#FFF';
			joc()->set_var('bg',$bg);
			joc()->set_var('stt',$stt);
			++$stt;
			joc()->set_var('id',$row['id']);
			joc()->set_var('censor_name',($row['censor_id']) ? $list_user[$row['censor_id']]['user_name'] : 'Chưa duyệt');
			joc()->set_var('user_name',$list_user[$row['user_id']]);
			joc()->set_var('name_topic',$row['name_topic']);
			joc()->set_var('description_topic',$row['description_topic']);
			joc()->set_var('date_start',$row['date_start']);
			joc()->set_var('date_end',$row['date_end']);
			joc()->set_var('date_censor',($row['censor_id']) ? $row['date_end'] : '');
			joc()->set_var('censor_noties',$row['censor_noties'] ? $row['censor_noties'] : ' Đồng ý thực hiện ngay./');
			$action = '';
			if(UserCurrent::havePrivilege('CENSOR_TOPIC'))
			{
				if($row['status'] == 0 && (int)$row['censor_id'] == 0)
				{
					$action = '<a rel="reason-censor" class="show-list" href="javascript:;" onclick="getIdCensor('.$row['id'].')"">Duyệt</a> | <a onclick="getId('.$row['id'].')" rel="reason-return" class="show-list" href="javascript:;">Không duyệt</a><br/>';
				}
				else
				{
					if($row['status'] == 1)
						$action = 'ĐƯỢC DUYỆT';
					else
						$action = 'KO ĐƯỢC DUYỆT';
				}

			}
			else
			{
				if($row['user_id'] == $user_info['id'])
				{
					if($row['status'] == 0 && $row['censor_id'])
					{
						$action = '<font color="red">KO ĐƯỢC DUYỆT</font>';
					}
					else
					{
						$action = 'ĐƯỢC DUYỆT';
					}
				}
				else
				$action = 'Không có quyền!';

			}

			$delete = '';
			if($row['user_id'] == $user_info['id'] && $row['status'] == 0)
			{
				$delete = '<a href="javascript:void;" onclick="deleteRow('.$row['id'].')">Xóa đề tài</a>';
			}

			joc()->set_var('delete',$delete);
			joc()->set_var('action',$action);
			joc()->set_var('reason',$row['reason_no_censor'] ? 'Lý do không duyệt:'.$row['reason_no_censor'] : '');
			joc()->set_var('add_topic',$row['add_topic']);
			joc()->set_var('number',$row['number']);
			$txt_html.=joc()->output('ListRow');
		}
		joc()->set_var('ListRow',$txt_html);
		global $TOTAL_ROWCOUNT;
		$totalRecord=$TOTAL_ROWCOUNT;
		joc()->set_var('total_rowcount',$totalRecord);
		joc()->set_var('paging','<li>Tổng số: '.$totalRecord.'</li>'.Paging::paging ($totalRecord,$item_per_page,10));
		$html= joc()->output("AdminTopic");
		joc()->reset_var();
		return $html;
	}
	function addAndEdit($id)
	{
		joc()->set_file('AdminTopic', Module::pathTemplate()."backend/admin_register_topic_add_edit.htm");
		Page::setHeader("Đăng ký đề tài", "Đăng ký đề tài", "Đăng ký đề tài");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');
		Page::registerFile('popup-css',	 Module::pathSystemCSS().'popup.css' , 'header', 'css');
		Page::registerFile('popup-js', JAVASCRIPT_PATH.'popup.js' , 'footer', 'js');
		Page::registerFile('jqDnR', JAVASCRIPT_PATH.'jqDnR.js' , 'footer', 'js');
		joc()->set_var('begin_form' , Form::begin(false, "POST", ' enctype="multipart/form-data" onsubmit="return checkForm()"'));
		joc()->set_var('end_form' 	, Form::end());
		$newsObj=new BackendNews();
		if($id)
		{
			$list_data=$newsObj->getListData('register_topic','*','id='.$id);
			$row=current($list_data);
		}

		joc()->set_var('id',(int)$row['id']);
		joc()->set_var('name_topic',$row['name_topic']);
		joc()->set_var('description_topic',$row['description_topic']);
		joc()->set_var('date_start',$row['date_start']);
		joc()->set_var('date_end',$row['date_end']);
		joc()->set_var('number',$row['number']);
		joc()->set_var('add_topic',$row['add_topic']);
		$html= joc()->output("AdminTopic");
		joc()->reset_var();
		return $html;

	}

}