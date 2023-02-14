<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/includes/property_news.php';
require_once 'application/news/backend/includes/get.news.php';
require_once 'application/main/includes/user.php';
require_once 'application/news/backend/includes/define.config.database.php';
require_once UTILS_PATH.'paging.php';
require_once UTILS_PATH.'image.upload.php'; 
class ConglyCensor
{
	function __construct()
	{
		$user_info=UserCurrent::$current->data;	
		if(!$user_info['id'])
		{
			 Url::urlDenied();
		}
	}
	function delete_files($path, $del_dir = FALSE, $level = 0){	
	// Trim the trailing slash
		$path = preg_replace("|^(.+?)/*$|", "\\1", $path);
		if ( ! $current_dir = @opendir($path))	return;
		while(FALSE !== ($filename = @readdir($current_dir))){
			if ($filename != "." and $filename != ".."){
				if (is_dir($path.'/'.$filename)){
					// Ignore empty folders
					if (substr($filename, 0, 1) != '.'){
						delete_files($path.'/'.$filename, $del_dir, $level + 1);
					}
				}else{
					unlink($path.'/'.$filename);
				}
			}
		}
		@closedir($current_dir);
		if ($del_dir == TRUE AND $level > 0){
			@rmdir($path);
		}
	}
	function index()
	{	
		ini_set('display_errors',1);
		$src=SystemIO::get('src','str','ngoisao.vn');
		$news_id=SystemIO::get('news_id','int',0);
		switch($src)
		{
			case 'ngoisao.vn':
				$objNewsMain=new BackendNews();
				$list_category=$objNewsMain->getListCategory('property & 1 =1','',500,'id');
				return $this->getNews($list_category,'ngoisao.vn',$url_img='http://img2.ngoisao.vn/news/');
				break;
			case 'xahoi.com.vn':
				$objNewsMain=new BackendNews();
				$list_category=$objNewsMain->getListCategory('property & 1 =1','',500,'id');
				return $this->getNews($list_category,'xahoi.com.vn',$url_img='http://image.xahoi.com.vn/news/');
				break;
			default:
				$objNewsMain=new BackendNews();
				$list_category=$objNewsMain->getListCategory('property & 1 =1','',500,'id');
				return $this->getNews($list_category,'xahoi.com.vn',$url_img='http://image.xahoi.com.vn/news/');
				break;
		}
	}
	function getNews($list_cate_site,$src_site='ngoisao.vn',$url_img='http://img2.ngoisao.vn:8001/news/')
	{
		
		joc()->set_file('GetNews', Module::pathTemplate()."backend/congly_censor.htm");
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		
		if($src_site=='ngoisao.vn'){
			Page::setHeader("Lấy tin trang NGOISAO.VN", "Lấy tin trang NGOISAO.VN", "Lấy tin trang NGOISAO.VN");
			$newsObj=new GetNews(NGOISAO_USER_NAME,NGOISAO_PASSWORD,NGOISAO_HOSTING,NGOISAO_DB_NAME);
			joc()->set_var('active_ngoisao','header-menu-active');
			$list_category=$newsObj->getListCategory('cate_id1=0','',110,'id',false);
			$list_category1=$newsObj->getListCategory('cate_id1 >0 AND cate_id2=0','',110,'id',false);
		}
		elseif($src_site=='xahoi.com.vn')
		{
			joc()->set_var('active_xahoi','header-menu-active');
			Page::setHeader("Duyệt tin trang XAHOI.COM.VN", "Duyệt tin trang XAHOI.COM.VN", "Duyệt tin trang XAHOI.COM.VN");
			$newsObj=new GetNews(XAHOI_USER_NAME,XAHOI_PASSWORD,XAHOI_HOSTING,XAHOI_DB_NAME);
			$list_category=$newsObj->getListCategory('cate_id1=0','',300,'id',false);
			$list_category1=$newsObj->getListCategory('cate_id1 >0 AND cate_id2=0','',300,'id',false);
		}
		$userObj=new User();
		/*Tìm kiếm*/
		$item_per_page=20;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		$q=SystemIO::get('q','def','');
		
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$cate_id=SystemIO::get('cate_id','int',0);
		$cate_id1=SystemIO::get('cate_id1','int',0);
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_category,'id','name'),$cate_id));
		joc()->set_var('option_category1',SystemIO::getOption(SystemIO::arrayToOption($list_category1,'id','name'),$cate_id1));		
		$wh='status = 3';
		if($q) $wh.=" AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
		if($cate_id1) $wh.=" AND cate_path LIKE '%,{$cate_id1},%'";
		elseif($cate_id) $wh.=" AND cate_path LIKE '%,{$cate_id},%'";
		
		$date_begin=SystemIO::get('date_begin','def');
		joc()->set_var('date_begin',$date_begin);
		if($date_begin)
		{
			$date_begin=strtotime(str_replace('/','-',$date_begin));
			$wh.= " AND time_created >= {$date_begin}";
		}
		
		$date_end=SystemIO::get('date_end','def');
		joc()->set_var('date_end',$date_end);
		if($date_end)
		{
			$date_end=strtotime(str_replace('/','-',$date_end));
			$wh.= " AND time_created <= {$date_end}";
		}	
		$list_news=$newsObj->getListReview($wh,'time_created DESC',$limit);
		$news_ids='';
		$cate_ids='';
		foreach($list_news as $_temp)
		{
			$cate_ids.=trim($_temp['cate_path'],',').',';
		}
		$cate_ids=trim($cate_ids,',');
		$list_path_news=$newsObj->getMultiPathNews($cate_ids);
		joc()->set_block('GetNews','ListRow','ListRow');
		$text_html='';
		global $NEWS_PROPERTY;
		$cate_option='';
		foreach($list_cate_site as $cate)
		{
				if(($cate['property'] & 1) ==1 && ($cate['cate_id1'] ==0) && ($cate['id']!=1) ){
					$k=0;
					$option_group='';
					foreach($list_cate_site as $cate1)
					{
						if($cate1['cate_id1']==$cate['id'])
						{
							++$k;
							$option_group.='<option value="'.$cate1['id'].'"> '.$cate1['name'].'</option>';
						}
					}
					if($k==0)
						$cate_option.='<option value="'.$cate['id'].'" style="font-weight:bold">'.$cate['name'].'</option>';
					else{
						$cate_option.='<optgroup label="'.$cate['name'].'">';
						$cate_option.=$option_group;
						$cate_option.='</optgroup>';
					}
				}
		}
		foreach($list_news as $row)
		{
			joc()->set_var('title',$row['title']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('path',$list_path_news[$row['cate_id']]);
			joc()->set_var('time_created',date('H:i d-m-Y',$row['time_created']));
			joc()->set_var('description',$row['description']);
			joc()->set_var('stt',$stt);
			joc()->set_var('tag',$row['tag']);
			joc()->set_var('src_site',$src_site);
			joc()->set_var('href','http://'.$src_site.'/?app=news&page=review&id='.$row['id']);
			joc()->set_var('cate_site_option',$cate_option);
			joc()->set_var('origin',$row['origin'] ? $row['origin'] : 'N/A');
			$path_img=$newsObj->getPathNews($row['time_created']);
			if($row['img1']) $src=$row['img1'];
			elseif($row['img2']) $src=$row['img2'];
			elseif($row['img3']) $src=$row['img3'];
			else $src='';
			joc()->set_var('src',$url_img.date('Y/n/j',$row['time_created']).'/'.$src);
			++$stt;
			$text_html.=joc()->output('ListRow');
		}
		joc()->set_var('ListRow',$text_html);
		global $TOTAL_ROWCOUNT;
		$totalRecord=$TOTAL_ROWCOUNT;
		joc()->set_var('total_rowcount',$totalRecord);
		joc()->set_var('paging','<li>Tổng số: '.$totalRecord.'</li>'.Paging::paging ($totalRecord,$item_per_page,10));
		$html= joc()->output("GetNews");
		joc()->reset_var();
		return $html;
	}
}