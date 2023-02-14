<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/news/backend/includes/administration.baogiay.news.php';
require_once 'application/main/includes/user.php';
require_once UTILS_PATH.'paging.php';
class AdministrationBaogiayTab
{
	function __construct()
	{
	}
	function index()
	{
		joc()->set_file('AdminNewsTab', Module::pathTemplate()."backend/administration_baogiay_tab.htm");
		
		$cmd=SystemIO::get('cmd','def','');
		
		$newsObj = new AdministrationBaogiayNews();
		$user_info=UserCurrent::$current->data;	
		if(UserCurrent::havePrivilege('IS_PHONGVIEN'))
			$wh='status = 1 AND user_id = '.$user_info['id'];
		elseif(UserCurrent::havePrivilege('IS_BIENTAP'))
			$wh='((status = 2 AND is_return = 1) OR is_return =2)';
		elseif(UserCurrent::havePrivilege('IS_TRUONGBANBIENTAP'))
			$wh='((status = 3 AND is_return = 1) OR is_return =2)';
		elseif(UserCurrent::havePrivilege('IS_THUKYBIENTAP'))
			$wh='((status = 4 AND is_return = 1) OR is_return =2)';
		elseif(UserCurrent::havePrivilege('IS_TRUONGBANTHUKY'))
			$wh='((status = 5 AND is_return = 1) OR is_return =2)';					
		else
			$wh='1=0';
		$total_back = $newsObj->getListData('store','*',$wh);
		
		$wh=' 1= 1';
		if(UserCurrent::havePrivilege('IS_BIENTAP')){
			$wh.=' AND status=2 AND is_return =0';
		}	
		elseif(UserCurrent::havePrivilege('IS_TRUONGBANBIENTAP')){
			$wh.=' AND status=3 AND is_return =0';
		}	
		elseif(UserCurrent::havePrivilege('IS_THUKYBIENTAP')){
			$wh.=' AND status=4 AND is_return =0';
		}	
		elseif(UserCurrent::havePrivilege('IS_TRUONGBANTHUKY')){
			$wh.=' AND status=5 AND is_return =0';	
		}
		elseif(UserCurrent::havePrivilege('IS_TONGBIENTAP')){
			$wh.=' AND status=6 AND is_return =0';	
		}
		
		$total_tin_cho_duyet=$newsObj->countRecord('store',$wh);
		
		/*tin luu tru*/
		$wh='status=0';
		if(UserCurrent::$current->data['user_name']!='namdd')
			$wh.=' AND user_id='.(int)UserCurrent::$current->data['id'];
			
		$total_tin_luutru=$newsObj->countRecord('store',$wh);	
		
		$total_tinnhan=$newsObj->countRecord('message');
		
		$tab_news=array('news_store'=>'Kho dữ liệu','pending_censor'=>'Tin chờ duyệt <b style="color:red">('.$total_tin_cho_duyet.')</b>','news_return'=>'Tin trả về <b style="color:red">('.(count($total_back)).')</b>','news_private'=>'Tin lưu trữ<b style="color:red"> ('.$total_tin_luutru.')</b>','message'=>'Các chỉ đạo<b style="color:red"> ('.$total_tinnhan.')</b>','news_create'=>'Tạo tin');
		$tab_privilege=array('news_store'=>'NEWS_STORE','pending_censor'=>'NEWS_PENDING_CENSOR','news_return'=>'NEWS_RETURN','news_private'=>'NEWS_CREATE','news_create'=>'NEWS_CREATE','message'=>'VIEW_MESSAGE');
		joc()->set_block('AdminNewsTab','Tab','Tab');
		$html_tab='';
		foreach($tab_news as $cmd_news=>$title)
		{
			
			if(UserCurrent::havePrivilege($tab_privilege[$cmd_news]))
			{
				
				$link='?app=news&page=administration_baogiay&cmd='.$cmd_news;
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
		$html= joc()->output("AdminNewsTab");
		joc()->reset_var();
		return $html;
	}
}	