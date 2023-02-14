<?php
if(!UserCurrent::havePrivilege('ADMIN_LAYOUT'))
{
	Url::urlDenied();
}
if(defined(IN_JOC)) die("Direct access not allowed!");
class AdminLayout extends Form
{
	private $msg = "";

	private $layoutObj;

	private $layouts;

	function __construct()
	{
		$this->layoutObj = SystemIO::createObject('LayoutModel');

		$this->layouts = $this->layoutObj->getLayout("id,name");

		Form::__construct($this);
	}
	function on_submit()
	{
		$files = scandir(LAYOUT_PATH);

		if(count($files) > 0)
		{
			foreach ($files as $file)
			{
				if($file !== "." && $file !== ".." && $file !== ".svn" && is_file(LAYOUT_PATH.$file) && !$this->layoutObj->existLayout($file, $this->layouts))
				{
					$content = file_get_contents(LAYOUT_PATH.$file);

					$patern = "/\[\[(.*)\]\]/";

					preg_match_all($patern, $content, $block);

					$bloc = "";

					if(count($block[1]) > 0)
					foreach ($block[1] as $blk)
					$bloc .= $blk."|";
					$bloc = trim($bloc, "|");
					$file = str_replace(".php", "", $file);
					$arrInsert = array("name" => $file, "description" => "" , "blocks" => $bloc );

					if($this->layoutObj->addLayout($arrInsert))
					$this->msg .= "Cập nhật được layout: <b>".$file."</b><br />";
					else
					$this->msg .= "Không cập nhật được layout: ".$file."<br />";
				}
			}
			/*scan on server
			 if(isset($_REQUEST['submit_server']))
				$this->layoutObj->deleteLayout();
				*/

		}
		if($this->msg == "")
		$this->msg = "Không có layout nào được cập nhật";
	}

	function index()
	{
		Page::setHeader("Danh sách Layout", "", "");

		Page::registerFile('admin_platform.js', Module::pathJS().'platform.js' , 'footer', 'js');

		joc()->set_file('HOME'		, Module::pathTemplate()."admin_layout.htm");

		joc()->set_block('HOME'		, 'LAYOUT');
			
		joc()->set_var('begin_form' , Form::begin(false, "POST", 'onsubmit="return add_page()"'));

		joc()->set_var('end_form' 	, Form::end());

		$html_layout = "";

		$layout_name = SystemIO::get('layout_name', 'str', '');
			
		if($layout_name != "")
		{
			$content = file_get_contents(LAYOUT_PATH.$layout_name.'.php');

			$patern = "/\[\[(.*)\]\]/";

			preg_match_all($patern, $content, $block);

			$bloc = "";

			if(count($block[1]) > 0)
			foreach ($block[1] as $blk)
			$bloc .= $blk."|";

			$bloc = trim($bloc, "|");

			if($this->layoutObj->updateLayout(array("blocks" => $bloc), 0, "name='$layout_name'"))
			$this->msg .= "<br />Cập nhật các block trong layout <b>$layout_name</b> thành công";
		}

		joc()->set_var('msg'		, $this->msg);

		if(is_array($this->layouts) && count($this->layouts) > 0)
		{
			$pageObj = new PageModel();

			$pages = $pageObj->getPage("id,layout_id,name", "layout_id IN(".(implode(",", array_keys($this->layouts))).")");

			$i = 0;
			foreach ($this->layouts as $layout)
			{
				joc()->set_var('layout_id' 			, $i+1);

				joc()->set_var('layout_class' 		, $i%2 == 0 ? 'bg-grey heightMin' : 'heightMin');

				joc()->set_var('layout_name' 		, $layout['name']);

				$page_of = "";

				if($pages)
				{
					foreach ($pages as $page)
					if((int)$page['layout_id'] == (int)$layout['id'])
					$page_of .= $page['name']." | ";
				}

				joc()->set_var('page_using_layout' 	, $page_of);

				$i++;

				$html_layout .= joc()->output('LAYOUT');
			}
		}

		joc()->set_var('LAYOUT', $html_layout);

		$html= joc()->output("HOME");

		joc()->reset_var();

		return $html;
	}
}

?>
