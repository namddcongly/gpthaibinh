<?php
if(defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/user/includes/comment.php';
require_once 'application/news/frontend/includes/frontend.news.php';
require_once UTILS_PATH.'paging.php';
//if(!UserCurrent::havePrivilege('ADMIN_NEWS'))
//{
//	Url::urlDenied();
//}
class AdminComment extends Form
{
	
	function __construct()
	{
		Form::__construct($this);
	}
	function index(){
	   
	   joc()->set_file('AdminComment', Module::pathTemplate()."backend/admin_comment.htm");
	   Page::setHeader("Quản trị bình luận", "Quản trị bình luận", "Quản trị bình luận");
   	   Page::registerFile('date Js'		 , Module::pathSystemJS().'date.js' , 'header', 'js');
	   Page::registerFile('jquery date Js'	, Module::pathSystemJS().'jquery.datepicker.min.js' , 'header', 'js');
	   Page::registerFile('date Css'		 , Module::pathSystemCSS().'datepicker.css' , 'header', 'css');
       $new = new FrontendNews();
       
       $q = SystemIO::get('q','def','');
       $type_search = SystemIO::get('type_search','int','1');
       $date_begin = SystemIO::get('date_begin','def','');
       $date_end = SystemIO::get('date_end','def','');
       joc()->set_var('q',$q);
	   $q=trim(str_replace(array('"',"'",'%'),array('','',''),$q),' ');
       $wh = '1=1';
       if($q)
       {
          joc()->set_var('q',$q);
          if($type_search == 1)
          {
                $wh .= " and content like '%$q%'";
                joc()->set_var('selected_1','selected="selected"');
                joc()->set_var('selected_0','');
          }
                
          else
          {
                joc()->set_var('selected_0','selected="selected"');
                joc()->set_var('selected_1','');
               $sql = "select id from store where title like '%$q%'";
               news()->query($sql);
               $new_id = news()->fetchAll();
               $array_new_id = '';
               foreach($new_id as $n)
               {
                    $array_new_id[] = $n['id'];
               }
               if($array_new_id)
               {
                    $ar = implode(',',$array_new_id);
                    $wh .=" and nw_id in ($ar)";
               } 
               else
                   $wh .=" and nw_id in (0)";
          }    
       }
	   if($date_begin)
	   {
		    joc()->set_var('date_begin',$date_begin);
			$date_begin=strtotime(str_replace('/','-',$date_begin));
			$wh.= " AND time_post >= {$date_begin}";
       }
       else
           joc()->set_var('date_begin','');
	   if($date_end)
	   {
		    joc()->set_var('date_end',$date_end);  
			$date_end=strtotime(str_replace('/','-',$date_end));
			$wh .= " AND time_post <= {$date_end}";
       }
       else
            joc()->set_var('date_end','');	
            
       joc()->set_block('AdminComment','comment');
       $comments = new Comment();
       $item_per_page=20;
	   $page_no=SystemIO::get('page_no','int',1);
	   if ($page_no<1) $page_no=1;
	   $stt=($page_no-1)*$item_per_page+1;
	   $limit=(($page_no-1)*$item_per_page).','.$item_per_page;
       $comment = $comments->getList($wh,'id desc',$limit,'');
       $html_comment = '';
       foreach($comment as $c)
       {
            $n = $new->newsOne($c['nw_id']);
            joc()->set_var('title_new',$n['title']); 
            joc()->set_var('id',$c['id']); 
            joc()->set_var('int_status',$c['status']); 
            joc()->set_var('stt',$stt++);
            joc()->set_var('email',$c['email']);
            joc()->set_var('content',$c['content']);
            joc()->set_var('time_post',date('H:i, d/m/Y',$c['time_post']));
            joc()->set_var('f_href',Url::Link(array('id'=>$c['nw_id'],'title'=>$n['title'],'cate_alias'=>$n['cate_id']),'news','gioitre_detail'));
            if($c['status'] == 0)
                joc()->set_var('status','Duyệt');
            elseif($c['status']==1)
                joc()->set_var('status','Không duyệt');
            if($c['user_name'] == '')
                joc()->set_var('user_name','');
            else
                joc()->set_var('user_name',$c['user_name']);
                             
            $html_comment .= joc()->output('comment');
       }
       global $TOTAL_ROWCOUNT;
       joc()->set_var('total_rowcount',$TOTAL_ROWCOUNT);
	   joc()->set_var('paging','<li>Tổng số: '.$TOTAL_ROWCOUNT.'</li>'.Paging::paging ($TOTAL_ROWCOUNT,$item_per_page,10));
       joc()->set_var('comment',$html_comment);
       $html= joc()->output("AdminComment");
	   joc()->reset_var();
	   return $html;
	} 
}