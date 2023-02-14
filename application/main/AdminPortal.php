<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

if(!UserCurrent::havePrivilege('ADMIN_PORTAL'))
{
	Url::urlDenied();
}
require_once 'application/main/includes/portal_model.php';

class AdminPortal extends Form
{
	function __construct(){
		Form::__construct($this);
	}
	function on_submit()
	{
		$name		= SystemIO::post('name','def','');
		$desc		= SystemIO::post('description','def','');
		$alias		= SystemIO::post('alias','def','');
		$portalObj	= SystemIO::createObject('PortalModel');

		if(ereg('^[a-z_]+$',$name) && !$portalObj->existPortal($name))
		{
			$arrNewData=array('name'=>$name,'description'=>$desc,'alias'=>$alias);
			$portalObj->insertData($arrNewData);
		}
		else
		echo '<script type="text/javascript">alert("Tên Portal không hợp lệ hoặc Portal đã tồn tại!");</script>';
	}
	function index()
	{
		Page::setHeader("Quản trị Portal", "Quản trị Portal", "Quản trị Portal");
		joc()->set_file('AdminPortal', Module::pathTemplate()."admin_portal.htm");

		Page::registerFile('AdminPortal', Module::pathJS().'AdminPortal.js' , 'footer', 'js');
		joc()->set_var('begin_form' , Form::begin( false, "POST", ''));
		joc()->set_var('end_form' 	, Form::end());
		$portalObj=new PortalModel();
		$list_portal=$portalObj->getList();
		$text_html='';
		joc()->set_block('AdminPortal','Portal','Portal');
		if(count($list_portal))
		{
			foreach($list_portal as $row)
			{
				joc()->set_var('name',$row['name']);
				joc()->set_var('description',$row['description']);
				joc()->set_var('alias',$row['alias']);
				joc()->set_var('id',$row['id']);
				$text_html.=joc()->output('Portal');
			}
		}
		joc()->set_var('Portal',$text_html);
		$html= joc()->output("AdminPortal");
		joc()->reset_var();
		return $html;
	}
}

?>
