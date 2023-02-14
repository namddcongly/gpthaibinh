<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/includes/property_news.php';
require_once 'application/main/includes/user.php';
require_once UTILS_PATH.'paging.php';
class AdminNewsTab
{
	function __construct()
	{
	}
	function index()
	{
		joc()->set_file('AdminNewsTab', Module::pathTemplate()."backend/admin_news_tab.htm");
		if(!UserCurrent::isLogin()){
            @header('Location:?app=main&page=admin_login');
        }
		$user_info=UserCurrent::$current->data;
		$cmd=SystemIO::get('cmd','def','');
		$newsObj = new BackendNews();
		$wh='status=1';
		if(!UserCurrent::havePrivilege('NEWS_VIEW_ALL'))
			$wh.=' AND user_id='.(int)UserCurrent::$current->data['id'];
		$total_back = $newsObj->getListReview($wh);

		$tab_news=array('home'=>'Tin trang chủ','news_region'=>'Tin vùng','news_map_region'=>'Map tin vùng','pending_public'=>'Chờ xuất bản','pending_censor'=>'Chờ duyệt','news_return'=>'Tin trả về <b style="color:red">('.(count($total_back)).')</b>','news_store'=>'Kho dữ liệu','news_private'=>'Tin lưu trữ','news_create'=>'Tạo tin');
		$tab_privilege=array('home'=>'NEWS_HOME','news_region'=>'NEWS_REGION','news_map_region'=>'NEWS_REGION','pending_public'=>'NEWS_PENDING_PUBLIC','pending_censor'=>'NEWS_PENDING_CENSOR','news_return'=>'NEWS_RETURN','news_store'=>'NEWS_STORE','news_private'=>'NEWS_CREATE','news_create'=>'NEWS_CREATE');
		joc()->set_block('AdminNewsTab','Tab','Tab');
		$html_tab='';
		foreach($tab_news as $cmd_news=>$title)
		{

			if(UserCurrent::havePrivilege($tab_privilege[$cmd_news]))
			{
				if($cmd_news=='news_region' || $cmd_news=='news_map_region')
					$link='?app=news&page=admin_map_news_region&cmd='.$cmd_news;
				else
					$link='?app=news&page=admin_news&cmd='.$cmd_news;
				joc()->set_var('link',$link);
				joc()->set_var('title',$title);
				if($cmd==$cmd_news)
					$class_active='header-menu-active';
				else
					$class_active='';
				joc()->set_var('class_active',$class_active);
				$html_tab.=joc()->output('Tab');
			}

		}
		joc()->set_var('Tab',$html_tab);

		$count_topic = $newsObj->countRecord('register_topic','is_public = 1 AND status = 0');
		joc()->set_var('count_topic',$count_topic);

		joc()->set_block('AdminNewsTab','TOPIC','TOPIC');
		$userObj=new User();
		$list_user = $userObj->userIdToNameAll();
		$html = '';
		if(UserCurrent::havePrivilege('CENSOR_TOPIC'))
		{
			$list = $newsObj->getListData('register_topic','*','is_public = 1 AND status = 0');
			foreach($list as $row)
			{
				joc()->set_var('name_topic',$row['name_topic']);
				joc()->set_var('user_name',$list_user[$row['user_id']]['user_name']);
				joc()->set_var('date_start',$row['date_start']);
				joc()->set_var('date_end',$row['date_end']);
				$html.=joc()->output('TOPIC');
			}
		}
		joc()->set_var('TOPIC',$html);

		$list_user_topic = $newsObj->getListData('register_topic','*','user_id = '.$user_info['id'].' AND is_public = 1 AND date_start >= '.date('Y-m-d').' AND date_end >= '.date('Y-m-d'));
		$text_topic = '';
		if(count($list_user_topic) == 0)
			$text_topic = 'Bạn chưa đăng ký ĐỀ TÀI Click <b><a style="color: #d9251d" href="?app=news&page=admin_register_topic&cmd=add_edit">VÀO ĐẦY</a></b> đăng ký';
		else
			$text_topic = 'Bạn đã đăng ký ĐỀ TÀI';
		joc()->set_var('report',$text_topic);
		joc()->set_block('AdminNewsTab','TOPIC_USER','TOPIC_USER');
		$html = '';
		foreach($list_user_topic as $row)
		{
			joc()->set_var('name_topic',$row['name_topic']);
			joc()->set_var('user_name',$list_user[$row['user_id']]['user_name']);
			joc()->set_var('date_start',$row['date_start']);
			joc()->set_var('date_end',$row['date_end']);
			joc()->set_var('status',$row['status'] ? 'ĐƯỢC DUYỆT' :  ( $row['censor_id'] ? 'KO ĐƯỢC DUYỆT' : ' CHƯA DUYỆT'));
			$html.=joc()->output('TOPIC_USER');
		}
		joc()->set_var('TOPIC_USER',$html);

		$html= joc()->output("AdminNewsTab");
		joc()->reset_var();
		return $html;
	}
}