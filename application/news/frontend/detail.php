<?php
require_once 'application/news/frontend/includes/frontend.news.php';
require_once 'application/news/includes/comment_model.php';
require_once 'application/user/includes/comment.php';
class Detail
{
	function __construct()
	{
	}
	function index()
	{		
		joc()->set_file('Detail', Module::pathTemplate()."frontend".DS."detail.htm");	
		joc()->set_block('Detail','OTHER','OTHER');
		global $LIST_CATEGORY;
        global $LIST_CATEGORY_ALIAS;
        global $infoNews;

		$id = SystemIO::get('id', "int", 0); 
        joc()->set_var('id',$id);
		if($infoNews['time_public'] == 0 ) Url::redirectUrl(array(),'http://m.congly.com.vn',false,'');
		if($id > 0 && $infoNews['time_public'] > 0 )
		{
		    
			$newsObj = new FrontendNews();
		    $detail= $infoNews;
            $cate_id_current = $detail['cate_id'];
			if($LIST_CATEGORY[$detail['cate_id']]['cate_id1']==0)
    		{
    			joc()->set_var('navigation','<a href="'.Url::Link(array('cate_id'=>$detail['cate_id'],'title'=>$LIST_CATEGORY_ALIAS[$detail['cate_id']]),'news','congly_cate').'" title="'.$LIST_CATEGORY[$detail['cate_id']]['name'].'">'.$LIST_CATEGORY[$detail['cate_id']]['name'].'</a>');
    		}
    		else
    		{
    			$cate_id_parent=$LIST_CATEGORY[$detail['cate_id']]['cate_id1'];
    			joc()->set_var('navigation','<a  href="'.Url::Link(array('cate_id'=>$cate_id_parent,'title'=>$LIST_CATEGORY_ALIAS[$cate_id_parent]),'news','congly_cate').'" title="'.$LIST_CATEGORY[$cate_id_parent]['name'].'">'.$LIST_CATEGORY[$cate_id_parent]['name'].'</a> <a class="last" href="'.Url::Link(array('cate_id'=>$detail['cate_id'],'title'=>$LIST_CATEGORY_ALIAS[$detail['cate_id']]),'news','congly_cate').'" title="'.$LIST_CATEGORY[$detail['cate_id']]['name'].'">'.$LIST_CATEGORY[$detail['cate_id']]['name'].'</a>');
    		}
    		
    		$url_uri 	= $_SERVER['REQUEST_URI'];
    		if(substr_count($url_uri,'ngoisao'))
    		{
    			//ini_set('display_errors',1);
    			joc()->set_var('navigation','<a>Ngôi sao</a>');
    			$config = array ('username' => 'frontend', 'password' => 'dat@fr0nt#joc', 'host' => '192.168.0.4','host_reserve'=>'192.168.0.3', 'dbname' => 'ngoisao_news');
		    	$newsObj = new FrontendNews($config);
		    	$detail= $newsObj->newsOne($id);
	            $detail_content =$newsObj->detail($id);     
	            $infoNews=$detail;
	            $infoNews['content']=$detail_content;
	            $arr_search=array('src="data/news/',"<div","</div>");
	            $arr_replace=array('src="http://img.ngoisao.vn/news/',"<p","</p>");
	            $infoNews['content']=str_replace($arr_search,$arr_replace,$infoNews['content']);
	            $detail=$infoNews;
	            $detail['cate_id'] =1000;
	            $LIST_CATEGORY_ALIAS['1000']='ngoisao';
	            $detail['time_public']=$detail['time_public']-600;
	            $cate_id=1000;
    		}
			if(substr_count($url_uri,'xahoi'))
    		{
    			//ini_set('display_errors',1);
    			joc()->set_var('navigation','<a>Xã hội</a>');
    			$config = array ('username' => 'backend', 'password' => 'b@ck3nd#xahoinet', 'host' => '192.168.0.4','host_reserve'=>'192.168.0.4', 'dbname' => 'joc_news');
		    	$newsObj = new FrontendNews($config);
		    	$detail= $newsObj->newsOne($id);
	            $detail_content =$newsObj->detail($id);     
	            $infoNews=$detail;
	            $infoNews['content']=$detail_content;
	            $arr_search=array('src="data/news/',"<div","</div>");
	            $arr_replace=array('src="http://image1.xahoi.com.vn/news/',"<p","</p>");
	            $infoNews['content']=str_replace($arr_search,$arr_replace,$infoNews['content']);
	            $detail=$infoNews;
	            $detail['time_public']=$detail['time_public']-600;
	            $detail['cate_id'] =2000;
	            $LIST_CATEGORY_ALIAS['2000']='xahoi';
	            $cate_id=2000;
    		}
    			
			Page::setHeader($detail['title'],$detail['tag'],strip_tags($detail['description']));
		    
			joc()->set_var('title'        , $detail['title']);
			joc()->set_var('time_public',date('d/n/Y H:i',$detail['time_public']).' UTC+7');
		    if($detail['type_post']==3) $detail['description']='Congly.vn - '.$detail['description'];
			joc()->set_var('description'  , $detail['description']);
            joc()->set_var('img',IMG::show($newsObj->getPathNews($detail['time_created']),$detail['img1']));
            $detail["content"] = str_replace('http://congly.com.vn/http://', 'http://', $detail["content"]);
            $detail["content"] = str_replace('logo.png', '', $detail["content"]);
		    joc()->set_var('detail'       ,$newsObj->showContent($detail['content']));
		 
		    //if(time() > 1374202238) //ngay 19/7/2013 mới thực hiện đóng logo công lý vào bài
		    //joc()->set_var('detail',$newsObj->showContent(str_replace('_have_logo_','have_logo_congly',$detail['content'])));
		    
		    joc()->set_var('link_detail'  , ROOT_URL.Url::Link(array("id" => $id,"title" => $detail['title'],'cate_id' =>$detail['cate_id'],'cate_alias'=>$LIST_CATEGORY_ALIAS[$detail['cate_id']]), "news", "congly_detail"));
		    joc()->set_var('author'       , ($detail['author']!= "" ? '<p style="color:#000;"><strong>'.$detail['author'].'</strong></p>': "").($detail['origin'] != "" ? '<p style="color:#000;"><em>Theo </em><strong>'.$detail['origin'].'</strong></p>' : ""));
		    //joc()->set_var('origin'  , $detail['origin']);
            $relate=array();
		    if($detail['relate']!='')
		        $relate = $newsObj->getNews("store","id,title,cate_id,time_public", "id IN(".rtrim($detail['relate'],",").")","time_public DESC", "0,5","id",true);
		    $html_r = '';
		    if(count($relate) > 0)
		    {
    	        foreach ($relate as $r)
    	        {
    	            if($LIST_CATEGORY_ALIAS[$r['cate_id']] != "")
            	       $html_r.= '<li><a style="color :#7A7A7A" href="'.Url::Link(array("id" => $r['id'],"title" => $r['title'],'cate_id' =>$r['cate_id'],'cate_alias'=>$LIST_CATEGORY_ALIAS[$r['cate_id']]),"news","congly_detail").'" class="x-more" title="'.htmlspecialchars($r['title']).'">'.$r['title'].'<span> ('.date("d/n/Y", $r['time_public']).')</span></a></li>'; 
    	        }
		    }
		    if($html_r != "")
                joc()->set_var('related', ' <div id="related">
                	<ul>'.$html_r.'</ul></div>');
            else 
                joc()->set_var('related', "");
            //cac tin moi cap nhat cung danh muc
		    $detail['cate_id'] = isset($detail['cate_id']) ? $detail['cate_id'] : 1;
		    $date=strtotime(date('d-n-Y',time()));
		    $newsest = $newsObj->newsOther("cate_id=".$detail['cate_id']." AND id !=".$id.' AND time_public > 0','0,10');
		    
		    $html_newest='';
		    if(count($newsest) > 0)
		    {
		        foreach ($newsest as $n)
		        {
		            joc()->set_var('other_link'   , Url::Link(array("id" => $n['nw_id'], "title" => $n['title'],"cate_id"=>$n['cate_id'],"cate_alias"=>$LIST_CATEGORY_ALIAS[$n['cate_id']]), "news", "congly_detail"));
		            joc()->set_var('other_title'  , $n['title']);
		            joc()->set_var('other_html_title'  , htmlspecialchars($n['title']));
		            joc()->set_var('other_date'   , date("d/n", $n['time_public']));
                    if(date('d/n',$n['time_public'])== date('d/n',time()))
                        joc()->set_var('icon_new_other','<img src="/webskins/skins/news/images/new.gif">');
                    else    
                        joc()->set_var('icon_new_other','');
		            $html_newest .= joc()->output('OTHER');
		        }
		    }
		    // cac tin  moi khac muc
		    $newsnew=$newsObj->getNews("store","id,title,cate_id,time_public", "cate_id!=".(int)$detail['cate_id'],"time_public DESC", "0,10","id",true);
		   	joc()->set_block('Detail','NEWS_NEW','NEWS_NEW');
		   	$html_new='';
			foreach ($newsnew as $n)
	        {
	            if($detail['cate_id'] =='1000')  $n['cate_id'] =1000;
	            if($detail['cate_id'] =='2000')  $n['cate_id'] =2000;
	        	joc()->set_var('new_link'   , Url::Link(array("id" => $n['id'], "title" => $n['title'],"cate_id"=>$n['cate_id'],"cate_alias"=>$LIST_CATEGORY_ALIAS[$n['cate_id']]), "news", "congly_detail"));
	            joc()->set_var('new_title'  , $n['title']);
	            joc()->set_var('new_html_title'  , htmlspecialchars($n['title']));
	            joc()->set_var('new_date'   , date("d/n", $n['time_public']));
                    if(date('d/n',$n['time_public'])== date('d/n',time()))
                        joc()->set_var('icon_new_new','<img src="/webskins/skins/news/images/new.gif">');
                    else    
                        joc()->set_var('icon_new_new','');
	            $html_new .= joc()->output('NEWS_NEW');
	        }
	        joc()->set_var('NEWS_NEW',$html_new);
		   	 
		    
        $tag='';
		$class_tag='';
	    if($detail['tag'])
		{
			include_once UTILS_PATH.'convert.php';
			$arr_tag=explode(',',$detail['tag']);
			$tag='<div class="main-tag">Tags: ';
			$tag.='<a href="'.Url::link(array('q'=>str_replace(' ','_',trim($arr_tag['0']))),'news','search').'">'.$arr_tag['0'].'</a>';
			for($k=1; $k < count($arr_tag);++$k)
			{
				$tag.=', <a href="'.Url::link(array('q'=>str_replace(' ','_',trim($arr_tag[$k]))),'news','search').'">'.$arr_tag[$k].'</a>';
				$class_tag.='tag-'.Convert::convertLinkTitle($arr_tag[$k]).' ';
			}
			$tag.='</div>';
		}
	    joc()->set_var('tag',$tag);
	    joc()->set_var('class_tag',trim($class_tag));
            joc()->set_var('OTHER',$html_newest);
		}
        /*show comment*/
        joc()->set_block('Detail','comment_view','comment_view');
        joc()->set_block('Detail','comment','comment');
        $comment = new Comment();
        $comments = $comment->getList("nw_id = $id and status = 1",'time_post desc');
        $html_comment = '';
        $html_comment_view = '';
        $i = 1;
        if($comments){
            joc()->set_var('display','');
            foreach($comments as $c){
                if($i <= 2)
                {
                    joc()->set_var('full_name_1',$c['full_name']);
                    list($domain) = explode('@',$c['email']);
                    joc()->set_var('email_1',$domain.'......');
                    joc()->set_var('content_1',$c['content']);
                    joc()->set_var('time_post_1',date('H:i, d/m/Y',$c['time_post']));
                    $html_comment_view .= joc()->output('comment_view');
                }
                else
                {
                    joc()->set_var('full_name',$c['full_name']);
                    list($domain) = explode('@',$c['email']);
                    joc()->set_var('email',$domain.'......');
                    joc()->set_var('content',$c['content']);
                    joc()->set_var('time_post',date('H:i, d/m/Y',$c['time_post']));
                    $html_comment .= joc()->output('comment');
                }
               $i++;
            }
        }
        else
            joc()->set_var('display','style="display:none;"');
        global $TOTAL_ROWCOUNT;
        if($TOTAL_ROWCOUNT <=2)
            joc()->set_var('display_button','style="display:none;"');
        else
            joc()->set_var('display_button','');
        
        joc()->set_var('total',$TOTAL_ROWCOUNT);
            
        joc()->set_var('comment',$html_comment);
        joc()->set_var('comment_view',$html_comment_view);
		$html= joc()->output("Detail");
		joc()->reset_var();
		return $html;
	}
}
?>