<?php
require_once(APPLICATION_PATH . 'news'. DS . 'frontend' . DS . 'includes' . DS . 'frontend.news.php');
require_once 'application/news/frontend/includes/define.php';
//require_once(UTILS_PATH .'pagination_mobile.php');
require_once UTILS_PATH.'paging.php';
class Congly_browse
{
	function __construct()
	{
	}
	function index()
	{		
		joc()->set_file('Congly_browse', Module::pathTemplate('news')."frontend".DS."cate.htm");
		//Page::registerFile('style.css','webskins'.DS.'skins'.DS.'news'.DS.'css'.DS.'style.css' , 'header', 'css');
        $cate_id=SystemIO::get('cate_id','int',1);
       
		$frontendObj=new FrontendNews();
		global $LIST_CATEGORY;
		global $LIST_CATEGORY_ALIAS;
		$page_no = SystemIO::get('page_no', 'int', 0);
		if($page_no==0)
			$limit='0,25';// 31: vi phan trang xem còn dữ liệu không?
		else
			$limit=15* $page_no . ',25';
		if($LIST_CATEGORY[$cate_id]['cate_id1']==0)
		{
			joc()->set_var('navigation','<a href="'.Url::Link(array('cate_id'=>$cate_id,'title'=>$LIST_CATEGORY_ALIAS[$cate_id]),'news','congly_cate').'" title="'.$LIST_CATEGORY[$cate_id]['name'].'">'.$LIST_CATEGORY[$cate_id]['name'].'</a>');
		}
		else
		{
			$cate_id_parent=$LIST_CATEGORY[$cate_id]['cate_id1'];
			joc()->set_var('navigation','<a  href="'.Url::Link(array('cate_id'=>$cate_id_parent,'title'=>$LIST_CATEGORY_ALIAS[$cate_id_parent]),'news','congly_cate').'" title="'.$LIST_CATEGORY[$cate_id_parent]['name'].'">'.$LIST_CATEGORY[$cate_id_parent]['name'].'</a> <h1><a class="last" href="'.Url::Link(array('cate_id'=>$cate_id,'title'=>$LIST_CATEGORY_ALIAS[$cate_id]),'news','congly_cate').'" title="'.$LIST_CATEGORY[$cate_id]['name'].'">'.$LIST_CATEGORY[$cate_id]['name'].'</h1></a>');
		}
		
		if($page_no > 1)
        	Page::setHeader($LIST_CATEGORY[$cate_id]['title'].'- Trang '.$page_no,$LIST_CATEGORY[$cate_id]['keyword'],$LIST_CATEGORY[$cate_id]['description'].' Trang '.$page_no);
        else
        	Page::setHeader($LIST_CATEGORY[$cate_id]['title'],$LIST_CATEGORY[$cate_id]['keyword'],$LIST_CATEGORY[$cate_id]['description']);
		
		if($LIST_CATEGORY[$cate_id]['cate_id1'])// danh muc con
			$list_news=$frontendObj->getNews('store','id,title,description,img1,time_public,time_created,cate_id','cate_id='.$cate_id,'time_public DESC',$limit,'id');
		else
			$list_news=$frontendObj->getNews('store','id,title,description,img1,time_public,time_created,cate_id','cate_path LIKE "%,'.$cate_id.',%"','time_public DESC',$limit,'id');   
		joc()->set_block('Congly_browse','ROW','ROW'); 
		joc()->set_block('Congly_browse','OTHER','OTHER');
        $first = current($list_news);
        joc()->set_var('f_title',$first['title']);
        joc()->set_var('f_img',IMG::Thumb($first['time_created'],$first['img1'],'cnn_225x150'));
        joc()->set_var('f_html_title',htmlspecialchars($first['title']));
		joc()->set_var('f_description',$first['description']);
		joc()->set_var('error_img',IMG::show($frontendObj->getPathNews($first['time_created']),$first['img1']));
        joc()->set_var('f_href'   , Url::Link(array('id'=>$first['id'],'title'=>$first['title'],'cate_id'=>$first['cate_id'],'cate_alias' => $LIST_CATEGORY_ALIAS[$first['cate_id']]),'news','congly_detail'));
		$txt_html_other='';#var_dump($list_news);
		$txt_html='';
        $k=1;
        
		foreach($list_news as $row)
		{
		  
		 	
			if($k>1){
    		 if($k < 16){
    			joc()->set_var('title',$row['title']);
    			joc()->set_var('html_title',htmlspecialchars($row['title']));
    			joc()->set_var('public',date('H:i d/n/Y',$row['time_public']));
    			joc()->set_var('description',SystemIO::strLeft(strip_tags($row['description']),250,''));
                $href = Url::Link(array('id'=>$row['id'],'title'=>$row['title'],'cate_id'=>$row['cate_id'],'cate_alias'=>$LIST_CATEGORY_ALIAS[$row['cate_id']]),'news','congly_detail');
    			joc()->set_var('href',$href);
    			$img = IMG::Thumb($row['time_created'],$row['img1'],'cnn_135x90');
    			if($row['img1'] =='')
                    joc()->set_var('img_html',''); 
                else
                    joc()->set_var('img_html','<a class="thumb" href="'.$href.'"><img onerror=\'this.src="'.IMG::show($frontendObj->getPathNews($row['time_created']),$row['img1']).'"\' title="'.htmlspecialchars($row['title']).'" alt="'.htmlspecialchars($row['title']).'" src="'.$img.'"></a>');                    
                     
    			$txt_html.=joc()->output('ROW');
    	   	 }
    	 	 else
    		 {
    			joc()->set_var('o_title',$row['title']);	
    			joc()->set_var('o_html_title',htmlspecialchars($row['title']));
    			joc()->set_var('o_public',date('d/n',$row['time_public']));
    			joc()->set_var('o_href_',Url::Link(array('id'=>$row['id'],'title'=>$row['title'],'cate_id'=>$row['cate_id'],'cate_alias'=>$LIST_CATEGORY_ALIAS[$row['cate_id']]),'news','congly_detail'));
                if(date('d/n',$row['time_public'])== date('d/n',time()))
                        joc()->set_var('icon_new_other','<img src="/webskins/skins/news/images/new.gif">');
                    else    
                        joc()->set_var('icon_new_other','');
    			$txt_html_other.=joc()->output('OTHER');
    		 }
          }   
          ++$k;
		}
		joc()->set_var('ROW',$txt_html);
		joc()->set_var('OTHER',$txt_html_other);
        $totalRecord=$frontendObj->countRecord('store','cate_path LIKE "%,'.$cate_id.',%"');
		if(count($list_news) > 24)
		{
			array_pop($list_news);
			$cate_title = SystemIO::get('title','str', '');
			
		    if($province_id)
				$view_more = Url::Link(array('cate_id'=>$cate_id,'title'=>$LIST_CATEGORY_ALIAS[$cate_id],'province_id'=>$province_id,'page_no'=>($page_no+1)),'news','congly_cate');
		    else
		    	$view_more = Url::Link(array("cate_id" => $cate_id,"title"=>$LIST_CATEGORY_ALIAS[$cate_id],"page_no" => ($page_no+1)), "news", "congly_cate");
		    joc()->set_var('view_more', '<a href="'.$view_more.'" title="Xem tiếp">Xem tiếp</a>');
            //joc()->set_var('other_display','');
		
		}
		else
        {
            joc()->set_var('view_more','');
            //joc()->set_var('other_display','style="display:none;"');
        }
        /*Su kien nong*/
		joc()->set_block('Congly_browse','FOCUS_2');
		$news_focus = $this->news_region(EVENT_HOT_ID);
		$txt_focus='';
        $k=1;
		foreach($news_focus as $row)
		{
			if($k==1) $class='first';
			else $class="";
			joc()->set_var('class',$class);
            joc()->set_var('stt',$k);
            joc()->set_var('html_title_focus2',htmlspecialchars($row['title']));
            joc()->set_var('title_focus2',$row['title']);
            joc()->set_var('href_focus2',Url::Link(array('id'=>$row['id'],'title'=>$row['title'],'cate_id' =>$row['cate_id'],'cate_alias'=>$LIST_CATEGORY_ALIAS[$row['cate_id']]),'news','congly_detail'));
            $txt_focus.=joc()->output('FOCUS_2');
            ++$k; 
            if($k > 7) break; 
        }
        joc()->set_var('FOCUS_2',$txt_focus);
        /* tieu diem */
		joc()->set_block('Congly_browse','FOCUS_1');
	    $news_focus_1 = $this->news_region(TIEUDIEM_ID);   
		$txt_focus='';
		$k=1;
		foreach($news_focus_1 as $row)
		{
			if($k==1) $class='first';
			else $class="";
			joc()->set_var('class_focus1',$class);
            joc()->set_var('stt_focus1',$k);
            joc()->set_var('html_title_focus1',htmlspecialchars($row['title']));
            joc()->set_var('title_focus1',$row['title']);
            joc()->set_var('href_focus1',Url::Link(array('id'=>$row['id'],'title'=>$row['title'],'cate_id'=>$row['cate_id'],'cate_alias'=>$LIST_CATEGORY_ALIAS[$row['cate_id']]),'news','congly_detail'));
            $txt_focus.=joc()->output('FOCUS_1');
			++$k;
			if($k > 7) break;
		}
		joc()->set_var('FOCUS_1',$txt_focus);
		$html= joc()->output("Congly_browse");
		joc()->reset_var();
		return $html;
	}
    function news_region($region_id)
    {
   	    $frontendObj=new FrontendNews();
        $list_news_ids=$frontendObj->getNewsInRegion($region_id);
		$news_ids='';
        $news_ids_1='';
        $news_ids_128='';
		$k=1;
		foreach($list_news_ids as $_temp)
		{
			$news_ids.=$_temp['nw_id'].',';
			++$k;
			if($k > 7) break; 
		}
		$news_ids=rtrim($news_ids,',');
		if($news_ids)
			$news_focus=$frontendObj->getNews('store','id,relate,title,img3,description,time_public,time_created,cate_path,cate_id',"id IN ({$news_ids})",'time_public DESC',null,'id',true);
            
            return $news_focus;
    }
}
?>