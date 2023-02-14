<?php
class AdminMapNewsRegion extends Form
{
	function __construct()
	{
		Form::__construct($this);
	}
	function on_submit()
	{
	}
	
	function mapNews()
	{
		joc()->set_file('AdminMapNewsRegion', Module::pathTemplate()."backend/admin_map_news.htm");
		Page::setHeader("Quản trị tin bài, map bài vào vùng", "Quản trị tin bài, map bài vào vùng", "Quản trị tin bài, map bài vào vùng");	
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		$newsObj=new BackendNews();
		$userObj=new User();
		require_once UTILS_PATH.'pagination.php';
		$pageObj= new Pagination();
		$list_category=$newsObj->getListCategory('cate_id1=0','',100,'id');
		$cate_id=SystemIO::get('cate_id','int',0);
		$cate_id_2=SystemIO::get('cate_id_2','int',0);
		joc()->set_var('option_category',SystemIO::getOption(SystemIO::arrayToOption($list_category,'id','name'),$cate_id));
		if($cate_id)
		{
			$list_category_1=$newsObj->getListCategory('cate_id1='.$cate_id,'',50,'id');
			joc()->set_var('option_category_1',SystemIO::getOption(SystemIO::arrayToOption($list_category_1,'id','name'),''));			
		}
		else
			joc()->set_var('option_category_1','');
		
		
		
		$item_per_page=20;
		$pageObj->per_page=$item_per_page;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		/*Tìm kiếm*/
		$q=SystemIO::get('q','def','');
		joc()->set_var('q',$q);
		$q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
		$wh='1=1';
		$wh_ajax='';
		if($q){ 
			$wh.=" AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
			$wh_ajax.="&q={$q}";
		}
		if($cate_id) {
			if($cate_id_2==0){
				$wh.=" AND cate_path LIKE '%,{$cate_id},%'";
				$wh_ajax.="&cate_id={$cate_id}";
			}
			else
			{
				$wh.=" AND cate_path LIKE '%,{$cate_id_2},%'";
				$wh_ajax.="&cate_id={$cate_id_2}";
			}
				
			

		}

		/*Lay thông tin news*/
		
		$wh .= " AND time_public < ".time();
		
		$list_news=$newsObj->getListStore($wh,'time_public DESC',$limit);
		
		
		$news_ids='';
		$cate_ids='';
		foreach($list_news as $_temp)
		{
			$cate_ids.=trim($_temp['cate_path'],',').',';
		}
		$cate_ids=trim($cate_ids,',');
		$list_path_news=$newsObj->getMultiPathNews($cate_ids);
		
		joc()->set_block('AdminMapNewsRegion','ListNews','ListNews');
		
		$text_html='';
		foreach($list_news as $row)
		{
			++$stt;
			joc()->set_var('title',$row['title']);
			joc()->set_var('nw_id',$row['id']);
			joc()->set_var('path',$list_path_news[$row['cate_id']]);
			joc()->set_var('description',$row['description']);
			joc()->set_var('stt',$stt);
			joc()->set_var('tag',$row['tag'] ? $row['tag'] : 'N/A');
			$text_html.=joc()->output('ListNews');
		}
		joc()->set_var('ListNews',$text_html);
		global $TOTAL_ROWCOUNT;
		$pageObj->total =$TOTAL_ROWCOUNT;
		$pageObj->portal = "news";
		$pageObj->pagename = "admin.news.process";
		joc()->set_var('paging', $pageObj->create1_ajax($wh_ajax));
					
		/*region*/
		$text_region='';
		joc()->set_block('AdminMapNewsRegion','ListRegion','ListRegion');
		$where_region='';
		$list_region=$newsObj->getListRegion($where_region,null,1000);
		$list_id_region='';
		$list_id_cate='';
		$arr_region_cate=array();
		foreach($list_region as $_temp)
		{
			$list_id_region.=$_temp['id'].',';
		}
		$list_id_region=trim($list_id_region,',');
		if($list_id_region){
			$list_id_cate='';
			$list_category=$newsObj->getListRegionCate("region_id IN ({$list_id_region})");
			if(count($list_category))
			{
				foreach($list_category as $_temp)
				{
					$list_id_cate.=$_temp['cate_id'].',';
					$arr_region_cate[$_temp['region_id']][]=$_temp['cate_id'];
			
				}
				$list_id_cate=trim($list_id_cate,',');
			}
		}
		if($list_id_cate){
			$list_cate=$newsObj->getListCategory("id IN ({$list_id_cate})",null,1000,'id');
		}
		$arr_region_cate_name=array();
		foreach($arr_region_cate as $region_id =>$cate_ids)
		{
			foreach($cate_ids as $key=>$id)
			{
				$arr_region_cate_name[$region_id][]=$list_cate[$id]['name'];
			}
		}
		$stt=0;
		foreach($list_region as $region)
		{
			joc()->set_var('name',$region['name']);
			++$stt;
			joc()->set_var('stt',$stt);
			$in_cate='';
			foreach($arr_region_cate_name[$region['id']] as $cate_name)
			{
				$in_cate.=$cate_name.', ';
			}
			
			joc()->set_var('cate_name',trim($in_cate,', '));
			joc()->set_var('region_id',$region['id']);
			joc()->set_var('description',$region['description']);
			$text_region.=joc()->output('ListRegion');
		}
		joc()->set_var('ListRegion',$text_region);
		$html= joc()->output("AdminMapNewsRegion");
		joc()->reset_var();
		return $html;
	}
	/*Tin vung da map*/
	function listMapNews()
	{
		joc()->set_file('AdminMapNewsRegion', Module::pathTemplate()."backend/admin_map_news_region.htm");
		Page::setHeader("Quản trị tin bài, map bài vào vùng", "Quản trị tin bài, map bài vào vùng", "Quản trị tin bài, map bài vào vùng");	
		Page::registerFile('admin Js'		 , Module::pathSystemJS().'admin.js' , 'header', 'js');
		$newsObj=new BackendNews();
		$userObj=new User();

		/*Các tin đã được map*/
		$item_per_page=10;
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		joc()->set_block('AdminMapNewsRegion','ListNewsMap','ListNewsMap');
		$region_id=SystemIO::get('region_id','int',0);
		
		$list_region=$newsObj->getListRegion();
		$list_category=$newsObj->getListCategory(null,'',200,'id');
		$arr_level1=array();
		$arr_level2=array();
		foreach($list_category as $_temp)
		{
			if($_temp['cate_id1']==0) $arr_level1[]=$_temp;
			else
			{
				if($_temp['cate_id2']==0) $arr_level2[]=$_temp;
			}
		}
		$cate_id=SystemIO::get('cate_id','int',0);
		joc()->set_var('option_category_1',SystemIO::getOption(SystemIO::arrayToOption($arr_level1,'id','name'),$cate_id));
		joc()->set_var('option_category_2',SystemIO::getOption(SystemIO::arrayToOption($arr_level2,'id','name'),$cate_id));
		joc()->set_var('option_region',SystemIO::getOption(SystemIO::arrayToOption($list_region,'id','name'),$region_id));
		$wh='';
		if($region_id)
			$wh='region_id='.$region_id;
			
			
		$list_news_map=$newsObj->getNewsMapRegion($wh);
		$arr_news_region=array();
		$list_news_id='';
		$region_ids='';
		foreach($list_news_map as $_temp)
		{
			$arr_news_region[$_temp['nw_id']][]=$_temp['region_id'];
			$list_news_id.=$_temp['nw_id'].',';
			$region_ids.=$_temp['region_id'].',';
		}
		$list_news_id=trim($list_news_id,',');
		$region_ids=trim($region_ids,',');
		if($list_news_id)
			$list_news_map_info=$newsObj->getListStore("id IN ({$list_news_id})",'id DESC',$limit,'');
		
		if(count($list_news_map_info))
		foreach($list_news_map_info as $_temp)
		{
			$cate_ids.=trim($_temp['cate_path'],',').',';
		}
		$cate_ids=trim($cate_ids,',');
		$list_path_news=$newsObj->getMultiPathNews($cate_ids);
		if($region_ids)
			$list_region_map=$newsObj->getListRegion("id IN ($region_ids)",null,1000,'id');
		$text_html='';
		$stt=($page_no-1)*$item_per_page;
		if(count($list_news_map_info))
		foreach($list_news_map_info as $row)
		{
			++$stt;
			joc()->set_var('title',$row['title']);
			joc()->set_var('nw_id',$row['id']);
            joc()->set_var('time_public',date('H:i d/m/Y',$row['time_public']));
			joc()->set_var('path',$list_path_news[$row['cate_id']]);
			$region_name='';
			foreach($arr_news_region[$row['id']] as $region_id)
			{
				$region_name.=$list_region_map[$region_id]['name'].' (<a href="javascript:;" onclick="delMapNewsregion('.$row['id'].','.$region_id.')" style="color:red">x</a>)<br/>';
			}
			joc()->set_var('region_name',$region_name);
			
			joc()->set_var('description',$row['description']);
			joc()->set_var('stt',$stt);
			
			joc()->set_var('tag',$row['tag'] ? $row['tag'] : 'N/A');
			$text_html.=joc()->output('ListNewsMap');
		}
		joc()->set_var('ListNewsMap',$text_html);
		global $TOTAL_ROWCOUNT;
		$totalRecord=$TOTAL_ROWCOUNT;
		joc()->set_var('total_rowcount',$totalRecord);
		joc()->set_var('paging','<li>Tổng số: '.$totalRecord.'</li>'.Paging::paging ($totalRecord,$item_per_page,10));
		$html= joc()->output("AdminMapNewsRegion");
		joc()->reset_var();
		return $html;
	}
	function index()
	{
		$cmd=SystemIO::get('cmd','def','news_region');
		switch($cmd)
		{
			case 'news_region':
				if(!UserCurrent::havePrivilege('NEWS_REGION'))
				{
				    Url::urlDenied();
				}
				return $this->listMapNews();
				break;
			case 'news_map_region':
				if(!UserCurrent::havePrivilege('NEWS_REGION'))
				{
				    Url::urlDenied();
				}
				return $this->mapNews();
				break;	
		}		
	}
}	