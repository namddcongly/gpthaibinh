<?php
class News_Search
{
	function __construct()
	{
	}
	function index()
	{
		joc()->set_file('News_Search', Module::pathTemplate()."frontend".DS."news_search.htm");
		require_once UTILS_PATH.'pagination.php';
		require_once UTILS_PATH.'convert.php';
		global $LIST_CATEGORY_ALIAS;
		global $LIST_CATEGORY;
		
		$frontendObj=new FrontendNews();
		$convert=new Convert();
		$item_per_page=10;		
		
		$page_no=SystemIO::get('page_no','int',1);
		if ($page_no<1) $page_no=1;
		$stt=($page_no-1)*$item_per_page+1;
		$limit=(($page_no-1)*$item_per_page).','.$item_per_page;
		
		$q=SystemIO::get('q','def','');
		
		$temp_k = $q;
		
		$q=str_replace('_',' ',$q);
		$q=str_replace('-','/',$q);
		
		$key = explode('"',$q);

		$leng_key = count($key);
		
		$wheres = "";
		
		if($leng_key > 1)	
			for($i=1;$i<$leng_key;$i+=2)
				if(trim($key[$i]) != "" && trim($key[$i]) != "/")
					$wheres .= ($wheres == "" ? "" : " AND ")."keyword like '% ".$convert->convertUtf8ToTelex(trim($key[$i]))." %'";

		$accurate=false;
		if($wheres != "") $accurate=true;
		
		joc()->set_var('q',str_replace(array('<','>','/',"'"),array('&lt','&gt','',"\'"),$q));
		Page::setHeader(trim($q,'/'),trim($q,'/'),trim($q,'/'));
		$q=str_replace(array('<','>','/','"',"'",'“','”'),array('&lt','&gt','','','','',''),$q);
		$q=mysql_escape_string($q);
		
		if($q){
			$list_search=$frontendObj->searchFullTextNews($convert->convertUtf8ToTelex($q),$wheres,$limit,$accurate);
			#print_r($list_search);
			$totalRecord=$list_search["TOTAL_ROW"];
			unset($list_search["TOTAL_ROW"]);
			//$wh="(tag LIKE '%{$q}%' OR title LIKE '%{$q}%') AND time_public > 0";
			//$list_search=$frontendObj->getNews('store','id,title,cate_id,img2,description,time_created,time_public',$wh,'time_public DESC',$limit);
			//$totalRecord=$frontendObj->countRecord('store',$wh);
			
		}
		joc()->set_block('News_Search','ListRow','ListRow');
		$text_html='';
		if(count($list_search))
		{
		  #var_dump($list_search);
			foreach($list_search as $row){
				if($row["id"])
				{
					$this->showIcon($row);
					joc()->set_var('title',$row['title']);
					joc()->set_var('description',SystemIO::strLeft($row['description'],300));
					joc()->set_var('img',IMG::Thumb($row['time_created'],$row['img1'],'cnn_135x90'));                    
					joc()->set_var('href',Url::Link(array('cate_id' =>$row['cate_id'],'cate_alias'=>$LIST_CATEGORY_ALIAS[$row['cate_id']],'id'=>$row['id'] ,'title'=>$row['title']),'news','congly_detail'));
					joc()->set_var('date',date('d/m/Y H:i',$row['time_public']));
					$text_html.=joc()->output("ListRow");
				}			
			}
		}
		else
		{
			$text_html='<p style="margin-top: 10px; text-align: center">Không có kết quả phù hợp với từ khóa: <strong>'.str_replace(array('<','>','/'),array('&lt','&gt',''),$q).'</strong>.</p>';
		}
		joc()->set_var('ListRow',$text_html);
		global $TOTAL_ROWCOUNT;
        joc()->set_var('total_row',$TOTAL_ROWCOUNT);
        $paging = new Pagination();
		
		$paging->total = $totalRecord;
		
		$paging->per_page = $item_per_page;
		
		$paging->page = $page_no-1;
		
		joc()->set_var('paging',$paging->create1_rewrite("search/".urlencode(trim($temp_k,"/"))));
            
		joc()->set_var('root_url',ROOT_URL);
		$html= joc()->output("News_Search");
		joc()->reset_var();
		return $html;
	}
	function showIcon($row,$suffix='')
	{
		if($row['is_video'])
			joc()->set_var('is_video'.$suffix,' <img src="webskins/skins/news/images/video.gif"  style="float:none" />');
		else
			joc()->set_var('is_video'.$suffix,'');
		if($row['is_img'])
			joc()->set_var('is_img'.$suffix,' <img src="webskins/skins/news/images/image.gif"  style="float:none" />');
		else
			joc()->set_var('is_img'.$suffix,'');
	}
}
?>