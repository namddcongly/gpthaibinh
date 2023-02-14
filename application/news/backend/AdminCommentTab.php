<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/includes/property_news.php';
require_once 'application/main/includes/user.php';
require_once UTILS_PATH.'paging.php';
class AdminCommentTab
{
	function __construct()
	{
	}
	function index()
	{
		joc()->set_file('AdminCommentTab', Module::pathTemplate()."backend/admin_comment_tab.htm");
		
		$cmd=SystemIO::get('cmd','def','comment');
		
		$newsObj = new BackendNews();
		
		$total_back = $newsObj->getListReview('status=1');
		
		$tab_news=array( 'comment'=>'Bình luận','pending_censor'=>'Bình luận chờ duyệt');
		$tab_privilege=array('pending_censor'=>'NEWS_COMMENT','comment'=>'NEWS_COMMENT');
		joc()->set_block('AdminCommentTab','Tab','Tab');
		$html_tab='';
		foreach($tab_news as $cmd_news=>$title)
		{
			
			if(UserCurrent::havePrivilege($tab_privilege[$cmd_news]))
			{
				$link='?app=news&page=admin_comment&cmd='.$cmd_news;
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
		$html= joc()->output("AdminCommentTab");
		joc()->reset_var();
		return $html;
	}
}	