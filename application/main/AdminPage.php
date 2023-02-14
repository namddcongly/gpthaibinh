<?php
//ini_set('display_errors',1);
if(defined(IN_JOC)) die("Direct access not allowed!");

if(!UserCurrent::havePrivilege('ADMIN_PAGE'))
{
	Url::urlDenied();
}

require_once(UTILS_PATH.'cache.file.php');
require_once(APPLICATION_PATH.'main/includes/portal_model.php');

class AdminPage extends Form
{
	private $msg = "";
	private $pageObj;
	private $layoutObj;
	private $masters;

	function __construct()
	{
		$this->pageObj = SystemIO::createObject('PageModel');
		$this->layoutObj = SystemIO::createObject('LayoutModel');

		$page_id = SystemIO::get('page_id'		, 'int', 0);

		$this->masters = $this->pageObj->getPage("id,name", "type=1".($page_id > 0 ? ' AND id!='.$page_id : ''));

		Form::__construct($this);
	}

	function on_submit()
	{
		$data = SystemIO::post('data','def',null);

		if($data['name'] != "")
		{
			if($data['layout_id'] > 0)
			{
				$page_id = SystemIO::get('page_id', 'int', 0);
				if($page_id == 0)
				{
					if(!$this->pageObj->addPage($data))
					$this->msg .= "Lỗi hệ thống không thêm được trang mới";
				}
				else
				{
					if($this->pageObj->updatePage($data, $page_id))
					{
						//@unlink(CACHE_PATH.'system'.DS.$data['portal_name'].DS.$data['name'].'.php');
						CacheFile::requestUrl('?app='.$data['portal_name'].'&page='.$data['name'].'&cached=1');
					}
					else
					$this->msg .= "Lỗi hệ thống không cập nhật được trang";
				}
			}
			else
			$this->msg .= "Chưa chọn layout.";
		}
		else
		$this->msg .= "Chưa điền tên trang.";
		if($this->msg == "")
		@header("Location:".ROOT_URL.'?'.$_SERVER['QUERY_STRING']);
	}

	function index()
	{
		$page_id = SystemIO::get('page_id', 'int', 0);
		if($page_id == 0)
		return $this->listPage();
		else
		return $this->editPage();
	}

	function listPage()
	{
		Page::setHeader("Danh sách trang", "", "");

		Page::registerFile('admin_platform.js', Module::pathJS().'platform.js' , 'footer', 'js');

		joc()->set_file('HOME', Module::pathTemplate()."admin_page.htm");

		joc()->set_block('HOME', 'PAGE');

		joc()->set_block('HOME', 'PORTAL');

		joc()->set_var('begin_form' , Form::begin( false, "POST", 'onsubmit="return add_page()"'));

		joc()->set_var('end_form' 	, Form::end());

		$layouts = $this->layoutObj->getLayout();

		joc()->set_var('select_layout', SystemIO::selectBox($layouts, array(0), "id", "id","name"));

		//if(isset($page)){
			joc()->set_var('select_master_page'	, SystemIO::selectBox($this->masters, array(), "id", "id","name"));
		//}

		$portalObj = SystemIO::createObject('PortalModel');

		$html_portal = "";

		$portals = $portalObj->getList();

		$portal_name = SystemIO::get('portal_name', 'str', 'main');

		$portal_id = SystemIO::get('portal_id'  , 'int', 1);

		joc()->set_var('msg', $this->msg);



		joc()->set_var('portal_id'	, $portal_id);

		joc()->set_var('portal_main', $portal_name);

		if(is_array($portals) && count($portals) > 0)
		{
			foreach ($portals as $portal)
			{
				joc()->set_var('portal_alias'	, $portal['alias']);

				if($portal_name == $portal['name'])
				joc()->set_var('portal_link'	, '?app=main&page=admin_page');
				else
				joc()->set_var('portal_link'	, '?app=main&page=admin_page&portal_id='.$portal['id'].'&portal_name='.$portal['name']);

				$html_portal .= joc()->output('PORTAL');
			}
		}

		joc()->set_var('PORTAL', $html_portal);

		$pages = $this->pageObj->getPage("*", "portal_id=".$portal_id);

		$html_page = "";

		joc()->set_var('portal_name' , $portal_name);

		if(is_array($pages) && count($pages) > 0)
		{
			$i = 0;
			foreach ($pages as $page)
			{
				joc()->set_var('page_stt'			, $i+1);
				joc()->set_var('page_desc'			, $page['description']);
				joc()->set_var('page_class'			, $i%2 == 0 ? 'bg-grey heightMin' : 'heightMin');
				joc()->set_var('page_name'			, $page['name']);
				
				if(array_key_exists($page['layout_id'], $layouts)){
					joc()->set_var('page_layout'		, $layouts[$page['layout_id']]['name']);
				}
				joc()->set_var('id',$page['id']);
				joc()->set_var('page_link'			, '?app='.$page['portal_name'].'&page='.$page['name']);
				joc()->set_var('page_edit_link'		, '?app=main&page=admin_page&page_id='.$page['id']);
				joc()->set_var('page_link_delete'	, 'ajax.php?path='.$page['portal_name'].'&fnc=page.process&page_id='.$page['id']);

				$html_page .= joc()->output('PAGE');
				$i++;
			}
		}

		joc()->set_var('PAGE', $html_page);

		$html= joc()->output("HOME");

		joc()->reset_var();

		return $html;
	}

	function editPage()
	{
		Page::setHeader("Sửa và cắm module vào trang", "", "");

		Page::registerFile('admin_platform.js', Module::pathJS().'platform.js' , 'footer', 'js');

		joc()->set_file('HOME'				, Module::pathTemplate()."admin_edit_page.htm");

		joc()->set_block('HOME'				, 'BLOCK');

		joc()->set_block('HOME'				, 'MODULE');

		joc()->set_var('begin_form' 		, Form::begin( false, "POST", 'onsubmit="return add_page()"'));

		joc()->set_var('end_form' 			, Form::end());

		joc()->set_var('msg' 				, $this->msg);

		$page_select = array(array("id" => 0, "name" => "Trang thông thường"), array("id" => 1, "name" => "Trang master_page"));

		$page_id = SystemIO::get('page_id'		, 'int', 0);

		$page = $this->pageObj->getOnePage("*"	, $page_id);

		$layouts = $this->layoutObj->getLayout();

		joc()->set_var('select_layout'		, SystemIO::selectBox($layouts, array($page['layout_id']), "id", "id","name"));

		joc()->set_var('select_page_type'	, SystemIO::selectBox($page_select, array($page['type']), "id", "id","name"));

		joc()->set_var('select_master_page'	, SystemIO::selectBox($this->masters, array($page['master_id']), "id", "id","name"));

		joc()->set_var('name' 				, $page['name']);

		joc()->set_var('portal_id' 			, $page['portal_id']);

		joc()->set_var('portal_name' 		, $page['portal_name']);

		joc()->set_var('description' 		, $page['description']);

		$layout = $this->layoutObj->getOneLayout("id,name", $page['layout_id']);

		$content = @file_get_contents(LAYOUT_PATH.$layout['name'].".php");
			
		$patern = "/\[\[(.*)\]\]/";

		preg_match_all($patern, $content, $block);

		$block = $block[1];

		$html_block = "";

		$moduleObj = SystemIO::createObject('ModuleModel');

		$page_modules = $moduleObj->getModuleOfPage($page_id);

		$modules = array();

		if(is_array($page_modules) && count($page_modules) > 0)
		foreach ($page_modules as $pm)
		$modules[$pm['possition']][] = $pm;
			
		if(count($block) > 0 && is_array($block))
		{
			$i = 1;
			foreach ($block as $bl)
			{
				joc()->set_var('stt' , $i);
				joc()->set_var('layout_name' , $bl);

				$html_module = "&nbsp;";
				
				if(array_key_exists($bl,$modules)){				
					$mds = $modules[$bl];
				}else{
					$mds = array();
				}	
				
				$in_mod = "";

				if(count($mds) > 0)
				{
					foreach ($mds as $md)
					{
						$in_mod .= $md['id'].",";

						joc()->set_var('module_name', $md['name'].'.php');

						joc()->set_var('module_path', $md['path']);

						joc()->set_var('link_delete', 'ajax.php?path=main&fnc=package.process&cmd=one&page_id='.$page_id.'&module_id='.$md['id']);
						joc()->set_var('link_up'	, 'ajax.php?path=main&fnc=package.process&cmd=up&page_id='.$page_id.'&module_id='.$md['id'].'&region='.$md['possition'].'&arrange='.$md['arrange']);
						joc()->set_var('link_down'	, 'ajax.php?path=main&fnc=package.process&cmd=down&page_id='.$page_id.'&module_id='.$md['id'].'&region='.$md['possition'].'&arrange='.$md['arrange']);

						$html_module .= joc()->output('MODULE');
					}
				}

				joc()->set_var('module_in', $html_module);
					
				joc()->set_var('link_add_module', '?app=main&page=admin_package&region='.$bl."&page_id=".$page_id);

				joc()->set_var('link_delete_all', 'ajax.php?path=main&fnc=package.process&cmd=all&page_id='.$page_id.'&module_id='.trim($in_mod, ","));

				$i++;
				$html_block .= joc()->output('BLOCK');
			}
		}
		joc()->set_var('BLOCK', $html_block);

		joc()->set_var('MODULE', '');

		$html= joc()->output("HOME");

		joc()->reset_var();

		return $html;
	}
}

?>
