<?php

require_once 'application/news/frontend/includes/frontend.news.php';
require_once 'application/news/includes/comment_model.php';
require_once UTILS_PATH.'captchar.php';

class review
{
	function __construct()
	{
		
	}
	function index()
	{		
	    /**
	     * **************************
	     * Cache my document vtn
	     * **************************
	     * 
	     */
        //ini_set('display_errors',1);
		joc()->set_file('Detail', Module::pathTemplate()."frontend".DS."review.htm");	
        
		Page::registerFile('giaitri.css','webskins'.DS.'skins'.DS.'news'.DS.'css'.DS.'xahoi.css' , 'header', 'css');
		
		Page::registerFile('home.css'     , 'webskins'.DS.'skins'.DS.'news'.DS.'css'.DS.'home.css' , 'header', 'css');
		Page::registerFile('main.css'     , 'webskins'.DS.'skins'.DS.'news'.DS.'css'.DS.'main.css' , 'header', 'css');
		Page::registerFile('news.js'      , 'webskins'.DS.'skins'.DS.'news'.DS.'js'.DS.'news.js', 'footer', 'js');
        
		joc()->set_block('Detail','OTHER','OTHER');
		joc()->set_block('Detail','OTHERS','OTHERS');
		joc()->set_block('Detail','UPDATE','UPDATE');
		joc()->set_block('Detail','VIDEO');
		joc()->set_block('Detail','PLAYLIST');
		$id = SystemIO::get('id', "int", 0);
		$from=SystemIO::get('from','def');
		if($id > 0)
		{
		    $newsObj = new FrontendNews();
		    
		    if($from=='store')
		    {		    	
		    	$detail = $newsObj->newsOne($id);
		    	$detail['content']=$newsObj->detail($id);
		    }
		    	
		    else
		    	$detail = $newsObj->getNewsReview($id);
		    
			if(!count($detail))
				Url::redirectUrl(array(),false,'error','main');
		    Page::setHeader($detail['title']." - congly.com.vn", $detail['tag'], $detail['description']);
		    
		    joc()->set_var('title'        , $detail['title']);
		    
		    if($detail["is_video"] && $detail["file"] != "")
		    {
		    	$file = explode(",", $detail["file"]);		    
		
				$html_f = "";
				
				for($i=1;$i<count($file);$i++)
				{
					joc()->set_var('file_flv', $file[$i]);
					$html_f .= joc()->output('PLAYLIST');
				}
				
				joc()->set_var('file_flv'       ,	 trim($file[0]));
				joc()->set_var('play_list'       ,	 $html_f);
				
		    }		    
		    else 
		    	joc()->set_var('VIDEO' , '');
		    
		    joc()->set_var('PLAYLIST' , '');
			joc()->set_var('time_created',date('d/n/Y H:i',$detail['time_created']));
		    joc()->set_var('description'  , $detail['description']);
		    joc()->set_var('detail'       , $newsObj->showContent($detail['content']));
		    joc()->set_var('link_detail'  , Url::Link(array("id" => $id,"title" => $detail['title']), "news", "detail"));
		    joc()->set_var('author'       , $detail['author']!= "" ? '<strong>Tác giả: '.$detail['author'].'</strong> ': '<strong> Theo: '.$detail['origin'].'</strong> ');
		    
		    if($detail['relate']!='')
		        $relate = $newsObj->getNews("store","id,title", "id IN(".rtrim($detail['relate'],",").")","id DESC", "0,5","id");
		    $html_r = '<div class="main-related">';
		    if(count($relate) > 0)
    	        foreach ($relate as $r)
            	    $html_r.= '<a href="'.Url::Link(array("id" => $r['id'],"title" => $r['title']),"news","detail").'" class="x-more" title="'.htmlspecialchars($r['title']).'">'.$r['title'].'</a>'; 
		    $html_r .= '</div>';
            joc()->set_var('related', $html_r);	    

		    //cac tin moi cap nhat cung danh muc
		    $detail['cate_id'] = isset($detail['cate_id']) ? $detail['cate_id'] : 1;
		    $newsest = $newsObj->newsOther("cate_id=".$detail['cate_id']." AND id >".$id);
		    
		    if(count($newsest) > 0)
		    {
		        foreach ($newsest as $n)
		        {
		            joc()->set_var('other_link'   , Url::Link(array("id" => $n['nw_id'], "title" => $n['title']), "news", "detail"));
		            joc()->set_var('other_title'  , $n['title']);
		            joc()->set_var('other_date'   , date("d/n", $n['time_public']));
		            $html_newest .= joc()->output('OTHER');
		        }
		    }
		    
		    //cac tin da dang
		    $others = $newsObj->newsOther("cate_id=".$detail['cate_id']." AND id <".$id);
		    
		    if(count($others) > 0)
		    {
		        foreach ($others as $other)
		        {
		            joc()->set_var('other_link'   , Url::Link(array("id" => $other['nw_id'], "title" => $other['title']), "news", "detail"));
		            joc()->set_var('other_title'  , $other['title']);
		            joc()->set_var('other_date'   , date("d/n", $other['time_public']));
		            $html_other .= joc()->output('OTHER');
		        }
		    }
		}
		
		if($html_newest != "")
		    joc()->set_var('news_update'       , $html_newest);
		else 
		    joc()->set_var('UPDATE'      , '');
		
		if($html_other != "")		
		    joc()->set_var('news_other'   , $html_other);
		else
		    joc()->set_var('OTHERS'       , '');
		
		joc()->set_var('OTHER'            , '');
		
		
		$tag='';
	    if($detail['tag'])
		{
			$arr_tag=explode(',',$detail['tag']);
			$tag='<div class="main-tag"><strong>Tags:</strong> ';
			$tag.='<a href="'.Url::link(array('q'=>$arr_tag['0']),'news','search').'">'.$arr_tag['0'].'</a>';
			for($k=1; $k < count($arr_tag);++$k)
			{
				$tag.='<a href="'.Url::link(array('q'=>$arr_tag[$k]),'news','search').'">,'.$arr_tag[$k].'</a>';
			}
			$tag.='</div>';
		}
	    joc()->set_var('tag',$tag);
		$html= joc()->output("Detail");
		joc()->reset_var();
		return $html;
	}
}
?>