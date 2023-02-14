<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
if(!UserCurrent::havePrivilege('ADMIN_LAYOUT'))
{
    Url::urlDenied();
}


class FootballConfig extends Form
{
    private $file_name = "";
    private $cmd;
    
	function __construct()
	{	    
	    $this->file_name = SystemIO::get('file_name', 'str', '');
	    
	    $this->cmd = SystemIO::get('cmd', 'str', "");
	    
	    if($this->cmd == "delete")
	    {	    
	    	@unlink('cache/utility/bongda/'.$this->file_name);
	    	URL::redirectUrl(array(),'?app=news&page=admin_football_configuration');
	    }
		Form::__construct($this);	
	}
	
	function on_submit()
	{
	    $data = SystemIO::post('data', 'arr', array());

	    $time = array("Chủ nhật", "Thứ hai","Thứ ba","Thứ tư","Thứ năm","Thứ sáu","Thứ bảy");
	    
	    $data["code"] = preg_replace('/\<span class=\"fb-league-time\"\>(.*?)\<\/span\>/', '<span class="fb-league-time">'.$time[date("w")].date(' j/n/Y| H:i').'</span>' , $data["code"]);
	    if($this->file_name != "")
	        file_put_contents('cache/utility/bongda/'.$this->file_name,$data['code']);
        else 
            file_put_contents('cache/utility/bongda/'.$data['file_name'],$data['code']);
	}
	
	function index()
	{
		Page::setHeader("Quản lý file dữ liệu bóng đá", "news, tin tức", "Quản lý danh mục tin tức");		
		
		Page::registerFile('ckeditor.js'	, 'webskins/richtext/ckeditor/ckeditor.js', 'footer', 'js');
		Page::registerFile('jquery.adapter.js' , 'webskins/richtext/ckeditor/adapters/jquery.adapter.js', 'footer', 'js');
		
		joc()->set_file('Football', Module::pathTemplate()."backend".DS."football_config.htm");	
		
		Page::registerFile('admin_news.js', Module::pathJS().'admin_news.js' , 'footer', 'js');
		
		joc()->set_var('begin_form' , Form::begin(false, "POST"));
		
		joc()->set_var('end_form' , Form::end());
		
		joc()->set_block('Football' , 'SLIDE');
		
		$files = scandir('cache/utility/bongda/');
		
		$html_file = "";
		
		if($this->file_name != "" && $this->cmd == "")
		{
		    joc()->set_var('filename',$this->file_name);
		    joc()->set_var('code_html',file_get_contents('cache/utility/bongda/'.$this->file_name));
		}
		else 
		{
		    joc()->set_var('filename', '');
		    joc()->set_var('code_html','');
		}
		
		if(count($files) > 0)
		{
		    foreach ($files as $file)
		    {
		        if($file !== "." && $file !== ".." && $file !== ".svn")
				{
    		        joc()->set_var('file_name', $file);
    		        joc()->set_var('file_path', 'cache/utility/bongda/'.$file);
    		        $html_file .= joc()->output('SLIDE');
				}
		    }
		}
		
		joc()->set_var('SLIDE', $html_file);

		$html= joc()->output("Football");
		
		joc()->reset_var();
		
		return $html;
	}
}

?>