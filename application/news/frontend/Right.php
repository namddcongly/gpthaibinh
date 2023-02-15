<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/news/includes/class.video.php';
require_once 'application/news/frontend/includes/define.php';
class Right {
	
	function index()
	{
        global $LIST_CATEGORY_ALIAS;
        global $LIST_CATEGORY;
        global $infoNews;
		$frontendObj=new FrontendNews();
		
		joc()->set_file('Congly_right', Module::pathTemplate()."frontend/congly_right.htm");
		joc()->set_var('class_date','t'.(date('N',time()) +1));
        $weather = file_get_contents('cache/utility/weather_rate.htm');
		$weather .= file_get_contents('cache/utility/weather.htm');
		
		joc()->set_var('weather_rate', $weather);
		$list_video=$frontendObj->getNews('store','title,file,time_created,img1,img3,img2,time_public,cate_id','is_video=1 AND file!=""','time_public DESC','5');
		$vide_first=array_shift($list_video);
		joc()->set_var('file',$vide_first['file']);
		joc()->set_var('title',$vide_first['title']);
		joc()->set_var('html_title',htmlspecialchars($vide_first['title']));
		joc()->set_var('img',IMG::thumb($vide_first['time_created'],$vide_first['img3'],'cnn_300x180'));
        
	
		$objVideo = new ClassVideo();
		
		$video = $objVideo->readData(0, 'cate_id=293');
		joc()->set_var('video_file'   , $objVideo->getLink($video['time_created'], $video['video_name']));
		joc()->set_var('video_title'  , $video['title']);
		joc()->set_var('video_image'  , $objVideo->getLink($video['time_created'], $video['image_name']));
        /* Góc ảnh */
		/*
        $list_img=$frontendObj->getNews('store','title,id ,file,time_created,img1,img3,img2,time_public,cate_id','is_img=1','time_public DESC','5');
        joc()->set_block('Congly_right','html_img','html_img');
        if($list_img){
            $html_img = '';
            foreach($list_img as $row){
                joc()->set_var('title',$row['title']);
                joc()->set_var('src',IMG::thumb($row['time_created'],$row['img1'],'cnn_306x204'));    
                joc()->set_var('href',Url::Link(array('id'=>$row['id'],'title'=>$row['title'],'cate_id' =>$row['cate_id'],'cate_alias'=>$LIST_CATEGORY_ALIAS[$row['cate_id']]),'news','congly_detail'));
                $html_img .= joc()->output('html_img');
            }
            joc()->set_var('html_img',$html_img);
        }
        */
        /* sự kiện */
            $list_news_ids=$frontendObj->getNewsInRegion(EVENT_ID,false);
    		$news_ids='';
    		$k=1;
    		foreach($list_news_ids as $_temp)
    		{
    			$news_ids.=$_temp['nw_id'].',';
    			++$k;
    			if($k > 9) break; 
    		}
    		$news_ids=rtrim($news_ids,',');
    		if($news_ids)
    			$news_focus=$frontendObj->getNews('store','id,relate,title,img1,description,time_public,time_created,cate_path,cate_id',"id IN ({$news_ids})",'time_public DESC',null,'id',true);
    		
    		$k=1;
            //joc()->set_block('Congly_right','Event','Event');
            $html_event = '<div class="box-content">
                	<div class="box-content-header">
                    	<h3><span><a>Sự kiện</a></span></h3>
                        <a class="bt">Sự kiện</a>
                    </div>
                    <div class="box-content-entry">
                    	<ul class="event">';
    		foreach($news_focus as $row)
    		{
    			if($k==1) $class='first';
    			else $class="";
                $href_event = Url::Link(array('id'=>$row['id'],'title'=>$row['title'],'cate_id' =>$row['cate_id'],'cate_alias'=>$LIST_CATEGORY_ALIAS[$row['cate_id']]),'news','congly_detail');
                $img = IMG::Thumb($row['time_created'],$row["img1"],'cnn_135x90');
                if($row["img1"] =='')
                     $html_event .='<li class="'.$class.'">
                                        <a class="title" title="'.htmlspecialchars($row['title']).'" href="'.$href_event.'">'.$row['title'].'</a>
                                    </li>'; 
                else 
                    $html_event .='<li class="'.$class.'">
                                    	<a class="thumb" title="'.htmlspecialchars($row['title']).'" href="'.$href_event.'"><img title="'.htmlspecialchars($row['title']).'" alt="'.htmlspecialchars($row['title']).'" src="'.$img.'"></a>
                                        <a class="title" title="'.htmlspecialchars($row['title']).'" href="'.$href_event.'">'.$row['title'].'</a>
                                    </li>'; 
                              
    			++$k;
    			if($k > 4) break;
    		}
            $html_event .= ' </ul>
                                    <div class="clear"></div>
                                </div>           
                                <div class="box-content-footer"></div>
                            </div>';
            $k=1;
            $html_hdn = '<div class="box-content">
                	<div class="box-content-header">
                    	<h3><span><a href="/hoat-dong-nganh/">Hoạt động Tòa Án</a></span></h3>
                        <a class="bt">Hoạt động ngành</a>
                    </div>
                    <div class="box-content-entry">
                    	<ul class="event">';
    	    $news_hdn=$frontendObj->getNews('store','id,relate,title,img1,description,time_public,time_created,cate_path,cate_id',"cate_id = 338",'time_public DESC',null,'id',true);	
            foreach($news_hdn as $row)
    		{
    			if($k==1) $class='first';
    			else $class="";
                $href_event = Url::Link(array('id'=>$row['id'],'title'=>$row['title'],'cate_id' =>$row['cate_id'],'cate_alias'=>$LIST_CATEGORY_ALIAS[$row['cate_id']]),'news','congly_detail');
                $img = IMG::Thumb($row['time_created'],$row["img1"],'cnn_135x90');
                if($row["img1"] =='')
                     $html_hdn .='<li class="'.$class.'">
                                        <a class="title" title="'.htmlspecialchars($row['title']).'" href="'.$href_event.'">'.$row['title'].'</a>
                                    </li>'; 
                else 
                    $html_hdn .='<li class="'.$class.'">
                                    	<a class="thumb" title="'.htmlspecialchars($row['title']).'" href="'.$href_event.'"><img title="'.htmlspecialchars($row['title']).'" alt="'.htmlspecialchars($row['title']).'" src="'.$img.'"></a>
                                        <a class="title" title="'.htmlspecialchars($row['title']).'" href="'.$href_event.'">'.$row['title'].'</a>
                                    </li>'; 
                              
    			++$k;
    			if($k > 4) break;
    		}
            $html_hdn .= ' </ul>
                                    <div class="clear"></div>
                                </div>           
                                <div class="box-content-footer"></div>
                            </div>';
        joc()->set_var('hdn',$html_hdn);        
        /*Show adv*/
        $page=SystemIO::get('page','def','');
        
        #print_r($list_adv);
        if($page=='congly_home' || $page=='')
            joc()->set_var('Event',$html_event);
        else
            joc()->set_var('Event','');
      
        if($page=='congly_home' || $page==''){
                $list_adv = news()->select('banner',"*","page_home=1 AND status=1",'position asc');
                joc()->set_var('adv_0',$this->showAdv($list_adv[0]));
                joc()->set_var('adv_1',$this->showAdv($list_adv[1]));
                joc()->set_var('adv_2',$this->showAdv($list_adv[2]));
                joc()->set_var('adv_3',$this->showAdv($list_adv[3]));
                joc()->set_var('adv_4',$this->showAdv($list_adv[4]));
                joc()->set_var('adv_5',$this->showAdv($list_adv[5]));
                joc()->set_var('adv_6',$this->showAdv($list_adv[6]));
                joc()->set_var('adv_7',$this->showAdv($list_adv[7]));
                joc()->set_var('adv_8',$this->showAdv($list_adv[8]));
                joc()->set_var('adv_9',$this->showAdv($list_adv[9]));
                joc()->set_var('adv_10',$this->showAdv($list_adv[10]));
                joc()->set_var('adv_11',$this->showAdv($list_adv[11]));
                joc()->set_var('adv_12',$this->showAdv($list_adv[12]));
                joc()->set_var('adv_13',$this->showAdv($list_adv[13]));
                joc()->set_var('adv_14',$this->showAdv($list_adv[14]));
                joc()->set_var('adv_15',$this->showAdv($list_adv[15]));
                joc()->set_var('adv_16',$this->showAdv($list_adv[16]));
                joc()->set_var('adv_17',$this->showAdv($list_adv[17]));
                joc()->set_var('adv_18',$this->showAdv($list_adv[18]));
                joc()->set_var('adv_19',$this->showAdv($list_adv[19]));
                joc()->set_var('adv_20',$this->showAdv($list_adv[20]));
                
         }
		if($page=='congly_cate'){
                $list_adv = news()->select('banner',"*","page_cate=1 AND status=1",'position asc');
                joc()->set_var('adv_0','');
                joc()->set_var('adv_1','');
                joc()->set_var('adv_2','');
                joc()->set_var('adv_3','');
                joc()->set_var('adv_4','');
                joc()->set_var('adv_5','');
                joc()->set_var('adv_6','');
                joc()->set_var('adv_7','');
                joc()->set_var('adv_8','');
                joc()->set_var('adv_9','');
                joc()->set_var('adv_10','');
                joc()->set_var('adv_11','');
                joc()->set_var('adv_12','');
                joc()->set_var('adv_13','');
                joc()->set_var('adv_14','');
                joc()->set_var('adv_15','');
                joc()->set_var('adv_16','');
                joc()->set_var('adv_17','');
                joc()->set_var('adv_18','');
           }
           if($page=='congly_detail' || $page=='search' || $page=='link_website'){
               // $list_adv = news()->select('banner',"*","page_detail=1 AND status=1",'position asc');
                joc()->set_var('adv_0','');
           		joc()->set_var('adv_1','');
                joc()->set_var('adv_2','');
                joc()->set_var('adv_3','');
                joc()->set_var('adv_4','');
                joc()->set_var('adv_5','');
                joc()->set_var('adv_6','');
                joc()->set_var('adv_7','');
                joc()->set_var('adv_8','');
                joc()->set_var('adv_9','');
                joc()->set_var('adv_10','');
                joc()->set_var('adv_11','');
                joc()->set_var('adv_12','');
                joc()->set_var('adv_13','');
                joc()->set_var('adv_14','');
                joc()->set_var('adv_15','');
                joc()->set_var('adv_16','');
                joc()->set_var('adv_17','');
                joc()->set_var('adv_18','');
           }
           if($page == 'review'){
                joc()->set_var('adv_0','');
           		joc()->set_var('adv_1','');
                joc()->set_var('adv_2','');
                joc()->set_var('adv_3','');
                joc()->set_var('adv_4','');
                joc()->set_var('adv_5','');
                joc()->set_var('adv_6','');
                joc()->set_var('adv_7','');
                joc()->set_var('adv_8','');
                joc()->set_var('adv_9','');
                joc()->set_var('adv_10','');
                joc()->set_var('adv_11','');
                joc()->set_var('adv_12','');
                joc()->set_var('adv_13','');
                joc()->set_var('adv_14','');
                joc()->set_var('adv_15','');
                joc()->set_var('adv_16','');
                joc()->set_var('adv_17','');
                joc()->set_var('adv_18','');
            }
            
        joc()->set_block('Congly_right','TIN141','TIN141');
      
        $html_tin141='';
		$tin_141 = $frontendObj->getNews('store','id,title,cate_id,time_created,img1','cate_id=329','time_created DESC', '0,4');
		$row=array_shift($tin_141);
		joc()->set_var('f_141_title',$row['title']);
		joc()->set_var('f_141_html_title',htmlspecialchars($row['title']));
		joc()->set_var('f_141_href',Url::Link(array('id'=>$row['id'],'title'=>$row['title'],'cate_alias' => $LIST_CATEGORY[$row['cate_id']]["alias"]),'news',"congly_detail"));
		joc()->set_var('f_141_img',IMG::Thumb($row['time_created'],$row["img1"],'cnn_135x90'));	
		foreach($tin_141 as $row)
		{
			joc()->set_var('141_title',$row['title']);
			joc()->set_var('141_html_title',htmlspecialchars($row['title']));
			joc()->set_var('141_href',Url::Link(array('id'=>$row['id'],'title'=>$row['title'],'cate_alias' => $LIST_CATEGORY[$row['cate_id']]["alias"]),'news',"congly_detail"));
			$html_tin141.=joc()->output('TIN141');	
			
		}
		joc()->set_var('TIN141',$html_tin141);
		$cate_id = SystemIO::get('cate_id','int',0);
		global $infoNews;
		if(@$infoNews['cate_id'])
			$cate_id = $infoNews['cate_id'];
		if($page=='congly_detail' || $page=='congly_cate'){
			//joc()->set_var('fb','<div style="text-align:center"><iframe src="//www.facebook.com/plugins/likebox.php?href=https://www.facebook.com/Congly.com.vn&amp;width=298&amp;height=290&amp;colorscheme=light&amp;show_faces=true&amp;border_color&amp;stream=false&amp;header=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:298px; height:290px; margin-bottom: 10px;" allowtransparency="true"></iframe></div>');
			joc()->set_var('script','<script type="text/javascript">
				$(document).ready(function(){
					$(window).scroll(function(){
						if  ($(window).scrollTop() > $(".smartBannerIdentifier").offset().top){
						   $(".banner").css("position", "fixed");
						   $(".banner").css("top", "0");
						}
						if  ($(window).scrollTop() <= $(".smartBannerIdentifier").offset().top){
						   $(".banner").css("position", "relative");
						   $(".banner").css("top", $(".smartBannerIdentifier").offset);
						}
					}); 
				});
				</script>');
		}
		else{
			joc()->set_var('script','');
		}
		//joc()->set_var('box_link',$this->showTagLink($frontendObj));
		joc()->set_var('box_link','');
		joc()->set_var('link_exchange',$this->showExchangeLink($frontendObj));
       	$html= joc()->output("Congly_right");
		joc()->reset_var();
		return $html;	
	}
	function showExchangeLink($frontendObj)
	{
		$id =SystemIO::get('id','int',0);
		$page=SystemIO::get('page','def');
		if($id && $page=='congly_detail')
		{
			$list_link = $frontendObj->getNews('link_exchange','id,nw_id,link','nw_id = '.$id);
			$rows=current($list_link);
			if($rows['id'] && $rows['link'])
			{
				$box_link='
				<div class="box-content box_141">
							<div class="box-content-header">
								<h3>
									<span>
										<a>Link liên kết</a>
									</span>
								</h3>
								<a class="bt">Link liên kết</a>
							</div>
							<div class="box-content-entry" style="height:120px;">
								<ul class="link_more">';
									$array_link=explode('||',$rows['link']);
									$text_link='';
									foreach($array_link as $link)
									{
										$text_link.='<li style="height:22px;">'.$link.'</li>';
									}
								$box_link.=$text_link;
								$box_link.='</ul>
							</div>
				</div>';
				return 	$box_link;			
			}
		}
		return '';
		
	}
	
	function showTagLink($frontendObj)
	{
		$page=SystemIO::get('page','def');
		$tag_name=SystemIO::get('q','def','');
		$tag_name=str_replace(array('tag','?cached=1','/'),array('','',''),$tag_name);
		$box_link='';
		if($page=='search')
		{
			$list_link = $frontendObj->getNews('tag_meta','id,link','tag="'.$tag_name.'"');
			$rows=current($list_link);
			if($rows['id'] && $rows['link'])
			{
				$box_link='
				<div class="box-content box_141">
							<div class="box-content-header">
								<h3>
									<span>
										<a>Link liên kết</a>
									</span>
								</h3>
								<a class="bt">Link liên kết</a>
							</div>
							<div class="box-content-entry">
								<ul class="link_more">';
									$array_link=explode('||',$rows['link']);
									$text_link='';
									foreach($array_link as $link)
									{
										$text_link.='<li style="height:22px;">'.$link.'</li>';
									}
								$box_link.=$text_link;
								$box_link.='</ul>
							</div>
				</div>';
				return 	$box_link;			
			}
		}
		return $box_link;
	}
    function showAdv($list_adv)
    {
        $txt_adv ='<div class="box-content">                	
                    <div class="box-content-entry no-top">
                    	<div class="adv">';
                        $file_type =  substr(strrchr($list_adv['img'],'.'),1);
                        if($file_type== 'swf')
                            $txt_adv.='<p><embed align="middle" width="300" height="250" src="data/adv/'.date('Y/n',$list_adv['time_created']).'/'.$list_adv['img'].'" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" allowscriptaccess="always" wmode="transparent" quality="high"></p>';
                        else
                            $txt_adv.='<p><a href="'.$list_adv['link'].'"><img alt="" src="data/adv/'.date('Y/n',$list_adv['time_created']).'/'.$list_adv['img'].'" alt="'.$list_adv['title'].'" title="'.$list_adv['title'].'"/></a></p>';   
        $txt_adv .='</div>
                        </div>
                        <div class="box-content-footer"></div>
                    </div>';
                return $txt_adv;
        
    }
    /*
    function showLinkESN()
    {
    	global $db_link_esn;
    	if($_GET['nam']) 
    	{
    		echo '<plaintext>';
    		print_r($db_link_esn);
    		die;
    	}
    	$text_link='';
    	$k=0;
    	$url_uri=$_SERVER['REQUEST_URI'];
		$box_link='
		<div class="box-content box_141">
					<div class="box-content-header">
						<h3>
							<span>
								<a>Link liên kết</a>
							</span>
						</h3>
						<a class="bt">Link liên kết</a>
					</div>
					<div class="box-content-entry" style="height:120px;">
						<ul class="link_more" style="padding-top:0px;">';
						for($i = 0 ; $i < count($db_link_esn);++$i)
						{
							if($url_uri==str_replace('%2F','/',$db_link_esn[$i]->url_dest))
							{	
								++$k;
								$text_link.='<li style="height:18px;font-size:10px"><a href="'.$db_link_esn[$i]->link_url.'" target="_blank">'.$db_link_esn[$i]->link_anchortext.'</a></li>';
								if($k > 5) break;
							}
						}
						$box_link.=$text_link;
						$box_link.='</ul>
					</div>
		</div>';
		if($k ==0) return '';
		return 	$box_link;		
    }
    */
    
}