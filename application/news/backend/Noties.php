<?php
require_once 'application/news/backend/includes/backend.news.php';
class Noties extends Form
{
	function __construct()
	{
		Form::__construct($this);
	}
	function index()
	{
		Page::setHeader("Quản lý chỉ đạo điều hành", "Quản lý chỉ đạo điều hành", "Quản lý chỉ đạo điều hành");		
		joc()->set_file('Noties', Module::pathTemplate()."backend".DS."noties.htm");
		$user_info=UserCurrent::$current->data;	
		$newsObj=new BackendNews();
		$list_noties=$newsObj->getNews('noties','*','1=1','time_public DESC');
		joc()->set_block('Noties','ListRow','ListRow');
		$txt_html='';
		foreach($list_noties as $row)
		{
			joc()->set_var('title',$row['title']);
			joc()->set_var('description',$row['descriptin']);
			joc()->set_var('user_name',$row['user_id']);
			$txt_html.=joc()->output('ListRow');
			
		}
		joc()->set_var('Row',$txt_html);
		$html= joc()->output("Noties");	
		joc()->reset_var();	
		return $html;
	}
}
?>