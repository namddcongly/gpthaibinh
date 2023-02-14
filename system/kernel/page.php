<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class Page
{
	private static $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml">';
	private static $title;
	private static $keyword;
	private static $description;

	private static $css;
	private static $js;

	private static $meta;

	private $header;
	private $footer;

	private $cached;
	private $content_cached;
	private $module_cached;

	function __construct()
	{

	}

	public static function setMeta($meta)
	{
		self::$meta .= $meta;
	}
	/**
	 * dang ky file su dung
	 *
	 * @param ten file $file_name
	 * @param duong dan $path
	 * @param vi tri $where
	 * @param kieu fil $file_type
	 */
	public static function registerFile($file_name, $path, $where = "footer", $file_type="js",$hack="")
	{
		if($file_type != "js" && $file_type != "css")
		return;

		if($file_type == "js")
		{
			self::$js[$where][$file_name]['path']  = ROOT_URL.$path;
			if($hack != "")
				self::$js[$where][$file_name]['hack'] = $hack;
		}
		else
		{
			self::$css[$where][$file_name]['path'] = ROOT_URL.$path;

			if($hack != "")
				self::$css[$where][$file_name]['hack'] = $hack;
		}
	}

	
	// fix
	// public function setHeader($title = '', $keyword = '', $description = '')
	public static function setHeader($title = '', $keyword = '', $description = '')
	{
		self::$title 		= htmlspecialchars($title);
		self::$keyword 		= htmlspecialchars($keyword);
		self::$description 	= htmlspecialchars($description);
	}
	
	
	// public function setDoctype($str = '')
	public function setDoctype($str = '')
	{
		self::$doctype = $str;
	}
	// endfix
	
	/**
	 * return header of page
	 *
	 */
	public function getHeader()
	{
		$css = self::$css;
		$js  = self::$js;

		$this->header  = self::$doctype;
		$this->header .= '<head>';
		$this->header .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="X-UA-Compatible" content="IE=8" />';
		$this->header .= '<base href="'.ROOT_URL.'" />';
		$this->header .= '<meta http-equiv="REFRESH" content="180000" />';
		$this->header .= '<title>'.self::$title.'</title>';
		$this->header .= '<meta name="keywords" content="'.self::$keyword.'" />';
		$this->header .= '<meta name="description" content="'.self::$description.'" />';

		$this->header .= self::$meta;

		$head_css = $css['header'];
		$leng = count($head_css);

		if($leng > 0)
		foreach ($head_css as $key => $hs)
		{
			if(isset($hs['hack']))
				$this->header .= $hs['hack'].'<link rel="stylesheet" type="text/css" href="'.$hs['path'].'?ver='.JS_VERSION.'" />'.($hs['hack'] != "" ? "<![endif]-->" : "");
			else
				$this->header .='<link rel="stylesheet" type="text/css" href="'.$hs['path'].'?ver='.CSS_VERSION.'" />';
		}
		
		
		
		// fix
		//$foot_css=array();
		//if(isset($css['footer']))
		//$foot_css = $css['footer'];
		//$leng = count($foot_css);
		$leng = count($foot_css = isset($css['footer']) ? $css['footer'] : array());
	
		if($leng > 0)
		foreach ($foot_css as $key => $fs)		
			$this->footer .= '<link rel="stylesheet" type="text/css" href="'.$fs['path'].'" />';
		
		//$leng = count($head_js);
		$leng = count($head_js = isset($js['header']) ? $js['header'] : array());
		// endfix

		if($leng > 0)
		foreach ($head_js as $key => $hj)
		{
			if(isset($hj["hack"]))
				$this->header .= $hj["hack"].'<script  type="text/javascript" src="'.$hj['path'].'"></script><![endif]-->';
			else			
				$this->header .= '<script  type="text/javascript" src="'.$hj['path'].'"></script>';
		}
		$foot_js=array();
		if(isset($js['footer']))
			$foot_js = $js['footer'];

		$leng = count($foot_js);
		if($leng > 0)
		foreach ($foot_js as $key => $fj)
		{
			if(isset($fj["hack"]))
				$this->footer .= $fj["hack"].'<script  type="text/javascript" src="'.$fj['path'].'"></script><![endif]-->';
			else
				$this->footer .= '<script type="text/javascript" src="'.$fj["path"].'"></script>';
		}

		$this->header .= '<script type="text/javascript">var root_url = "'.ROOT_URL.'";</script>';

		$this->header .= '<link rel="shortcut icon" href="favicon.ico" />';

		//$this->header .= '<link rel="stylesheet" type="text/css" href="'.trim(ROOT_URL,'/').'/css.php" />';
		
		/*link js cua qc*/
		$this->header.="</head>";
	
	
		$this->header.="<body>";
		return $this->header;
	}
	/**
	 * get body of page
	 *
	 */
	public function getBody($portal, $page)
	{
		$this->cached = SystemIO::get('cached', 'int', 0);

		//get info page
		$pageObj = SystemIO::createObject('PageModel');

		$page_info = $pageObj->getOnePage("*", 0, "name='".$page."'");

		if(isset($page_info['name']))
		{
			$layout_name = "admin_platform";

			//get layout
			if((int)$page_info['layout_id'] > 0)
			{
				$layoutObj 		= SystemIO::createObject('LayoutModel');

				$layout_page 	= $layoutObj->getOneLayout("name,blocks",0, "id=".$page_info['layout_id']);

				if(isset($layout_page['name']))
				$layout_name = $layout_page['name'];
			}

			$regions = explode("|",$layout_page['blocks']);

			//get module of page
			$moduleObj = SystemIO::createObject('ModuleModel');

			$modules = $moduleObj->getModuleOfPage($page_info['id'], $page_info['master_id']);



			//show module
			$blocks = array();

			$tmpmodules = array();

			if(count($modules) && is_array($modules))
			{
				$len = count($regions);
				if($len > 0)
				for($i=0;$i<$len;++$i)
				foreach ($modules as $tmp)
				if($tmp['possition'] == $regions[$i])
				$tmpmodules[] = $tmp;

				foreach ($tmpmodules as $module)
				{
					$mode = '<?php require(\''.APPLICATION_PATH.$module['path'].'\');';

					$mode.= '$_class = "'.$module['name'].'";$blockObj = new $_class;echo $blockObj->index();?>';

					if($this->cached)
					$this->module_cached[$module['possition']] .= $mode;

					require(APPLICATION_PATH.$module['path']);

					$_class = $module['name'];

					$blockObj = new $_class;
					if(!isset($blocks[$module['possition']])) $blocks[$module['possition']]='';

					$blocks[$module['possition']] .= $blockObj->index();
				}
			}

			$fp = fopen(LAYOUT_PATH.$layout_name.".php","r");

			$body = stream_get_contents($fp);

			$this->content_cached = $body;

			fclose($fp);

			if(count($blocks) > 0)
			{
				foreach ($blocks as $key => $value)
				$body = str_replace("[[$key]]", $value,$body);
					
				if(is_array($this->module_cached) && count($this->module_cached) > 0)
				{
					foreach ($this->module_cached as $key => $mc)
					$this->content_cached = str_replace("[[$key]]", $mc, $this->content_cached);
				}
			}
			else
			$body = "";

			return $body;
		}

		return false;
	}
	/**
	 * get footer of page
	 *
	 */
	public function getFooter()
	{
		return $this->footer.'</body></html>';
	}
	/**
	 * lay noi dung html
	 *
	 */
	public function getContent($portal, $page)
	{
		if(file_exists(CACHE_PATH.'system'.DS.$portal.DS.$page.'.php'))
		{
			ob_start();

			require(CACHE_PATH.'system'.DS.$portal.DS.$page.'.php');

			$temp_html = ob_get_contents();

			ob_clean();

			$html = $this->getHeader().$temp_html;

			$html .= $this->getFooter();

			return $html;
		}

		$body = $this->getBody($portal, $page);

		if($body !== false)
		{
			$html = $this->getHeader();

			flush();

			$html .= $body;

			$html .= $this->getFooter();

			if($this->cached)
			{
				$fp = fopen(CACHE_PATH.'system'.DS.$portal.DS.$page.'.php', "w+");

				$this->content_cached = preg_replace('/\>(\s\s+)\</', '><', $this->content_cached);

				fwrite($fp, $this->content_cached);

				fclose($fp);
			}

			return $html;
		}
		else
		return "Không tồn tại trang bạn yêu cầu";

	}
}

?>