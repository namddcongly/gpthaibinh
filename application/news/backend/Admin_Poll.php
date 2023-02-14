<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
/*if(!UserCurrent::havePrivilege('HIT_CONFIG'))
{
    Url::urlDenied();
}*/

//require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/includes/poll_model.php';

require_once 'application/news/includes/poll_option_model.php';

class Admin_Poll extends Form
{
	private $pollObj;
	
	private $oppollObj;
	
	private $msg;
	
	private $query;
	
	private $item;
	
	function __construct()
	{
	    $this->pollObj 	= new PollModel();
	    
	    $this->opollObj = new PollOptionModel();
	    
	    $this->id 		= SystemIO::get("id", "int" , 0);
	    
	    $this->msg 		= SystemIO::get("msg", "str" , "");
	    
	    $this->query 	= SystemIO::get("query", "str" , "");
	    
	    $cmd 			= SystemIO::get("cmd", "str" , "");
	    
	    Form::__construct($this);
	    
	    if($this->id > 0)
	    {
	    	if($cmd == "delete")
	    	{
	    		$this->pollObj->deleteData($this->id);
	    		
	    		$this->opollObj->deleteData(0, "poll_id=".$this->id);
	    		
	    		header("Location:?portal=news&page=admin_poll");
	    	}
	    	else
	    	{
	    		$this->item = $this->pollObj->PollOne("*",$this->id);

	    		$this->item["options"] = $this->opollObj->getList("*","poll_id=".$this->id,"id asc");
	    	}
	    }
	}
	
	function on_submit()
	{
		$data 	= SystemIO::post("data", "arr", array());
		
		$option = SystemIO::post("poll", "arr", array());
				
		if($data["end_time"] != "")
			$data["end_time"] = strtotime(str_replace("/","-", $data["end_time"]));
		else			
			$data["end_time"] = time() + 10*86400;
		
		if($this->id == 0)
		{
			$data["time_created"] = time();
		
			$data["status"] = 1;
		}
		//them poll
		
		if($this->id == 0)
		{
			$poll_id = $this->pollObj->insertData($data);
			
			if($poll_id > 0)
			{
				$option = $option[0];
				if(count($option) > 0)
				{
					foreach($option as $p)
					{
						if($p != "")
						{
							$pl["text"] 	= $p;
							
							$pl["poll_id"] 	= $poll_id;
							
							$pl["voted"] 	= 0;
							
							$this->opollObj->insertData($pl);
						}
					}
				}
				$this->msg = 'Thêm mới thành công';
			}
			else
				$this->msg = 'Thêm mới không thành công';
		}
		else
		{
			$this->pollObj->updateData($data, $this->id);
			$o_tmp = $option[0];
			unset($option[0]);
			
			if(count($option) > 0)
			{			
				foreach($option as $k => $v)
				{
					if(trim($v[0]) != "")					
						$this->opollObj->updateData(array("text" => $v[0]), $k);
					else
						$this->opollObj->deleteData($k);
				}
			}
			if(count($o_tmp) > 0)
			{
				foreach($o_tmp as $ot)
				{
					if($ot != "")
					{
						$pl["text"] 	= $ot;
						
						$pl["poll_id"] 	= $this->id;
						
						$pl["voted"] 	= 0;
						
						$this->opollObj->insertData($pl);
					}
				}
			}
				
			$this->msg = "Cập nhật thành công";
		}
	}
	
	function index()
	{
        if (!UserCurrent::isLogin()) {
            @header('Location:?app=main&page=admin_login');
        }
		Page::setHeader("Quản lý bình chọn", "news, tin tức", "Quản lý danh mục tin tức");		
		
		joc()->set_file('Poll', Module::pathTemplate()."backend".DS."admin_poll.htm");	
		
		Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');	
		
		Page::registerFile('admin_news.js', Module::pathJS().'admin_news.js' , 'footer', 'js');
		
		Page::registerFile('date Js', Module::pathSystemJS().'date.js' , 'header', 'js');
				
		Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');		
		
		joc()->set_block('Poll', 'LIST');
		
		joc()->set_var('begin_form' , Form::begin(false, "POST"));
		
		joc()->set_var('end_form' , Form::end());
		
		joc()->set_var('submit', $this->id > 0 ? 'Cập nhật' : 'Thêm mới');
		
		joc()->set_var('show' , $this->id > 0 ? '' : 'display:none');
		
		$types = array(1 => array("id" => 1,"name" => "Kiểu checkbox"), 2 => array("id" => 2,"name" => "Kiểu radio") );
			
		joc()->set_var("option_type", SystemIO::selectBox($types, array(isset($this->item["type"]) ? $this->item["type"] : 1),"id","id", "name"));
		
		joc()->set_var('display' , $this->msg != "" ? '' : 'display:none');
		
		joc()->set_var('msg' , $this->msg);
		
		joc()->set_var('query' , $this->query);
		
		$cond = "1=1";
		
		if($this->query != "")
			$cond .= " AND name like '%".$this->query."%'";
		
		$type = SystemIO::get("type", "int", 0);
		
		if($type > 0)
			$cond .= " AND type=$type";
		
		$polls = $this->pollObj->get_List($cond,"id desc","0,10");
		
		$total = $polls["total"];
		
		$polls = $polls["items"];
		
		if($this->id > 0)
		{
			$ops = $this->item["options"];
			
			foreach($ops as $op)
			{
				$html_edit .= '<p style="margin-bottom:5px"><label for="name">Sửa option</label>';
				$html_edit .= '<input type="text" style="width:250px" name="poll['.$op["id"].'][]" value="'.$op["text"].'"></p>';
			}			
			
			joc()->set_var('edit_poll', $html_edit);
			joc()->set_var("poll_name", $this->item["name"]);
			joc()->set_var("poll_end_time", date("d/m/Y" , $this->item["end_time"]));
		}
		else
		{
			joc()->set_var("poll_name", '');
			joc()->set_var("poll_end_time" , '');
			joc()->set_var('edit_poll', '');
		}
		
		$html_poll = "";
		
		if(count($polls) > 0)
		{
			foreach($polls as $p)
			{
				joc()->set_var('name', $p["name"]);
				
				joc()->set_var('end_time', date("d/m/Y", $p["end_time"]));
				
				joc()->set_var('time_created', date("d/m/Y", $p["time_created"]));
				
				joc()->set_var('id', $p["id"]);
				
				joc()->set_var('type', $types[$p["type"]]["name"]);
				
				$html_poll .= joc()->output('LIST');
			}
		}
		
		joc()->set_var('LIST', $html_poll);

		$html= joc()->output("Poll");
		
		joc()->reset_var();
		
		return $html;
	}
}

?>