<?php
require_once 'application/user/includes/comment.php';
require_once 'application/news/backend/includes/backend.news.php';
require_once UTILS_PATH.'captchar.php';
ini_set('display_errors',1);
$action=SystemIO::post('action','def','');
switch($action)
{
    case 'send_comment':
        send_comment();
        break;
    case 'show_captchar':
        show_captchar(); 
        break;
    case 'approve_comment':
        approve_comment();
        break;
    case 'delete_comment':
        delete_comment();     
        break; 
    case 'approve_adv':
        approve_adv();     
        break; 
    case 'edit':
    	 edit_comment();
    	 break;	     
    case 'delete_adv':
        delete_adv();     
        break;    
}
function edit_comment()
{
	
	 $id = SystemIO::post('id','int',0);
	 $content= SystemIO::post('content','def','');
	 $newsObj=new BackendNews();
	 if($content){
		$data=array('content'=>$content); 
	 	if($newsObj->updateData('comment',$data,'id='.$id))
		 	echo 1;
		 else
		 	echo 0;	
	 }
	 else
	 	echo 0;
	  
	 
}
function send_comment(){
     
    $captcha = new Captcha(4);
    $full_name = SystemIO::post('name','def','');
    $email = SystemIO::post('email','def','');
    $capcha = SystemIO::post('capcha','def','');
    $comment = SystemIO::post('comment','def','');
    $ip_client = SystemIO::post('ip_client','def','');
    $new_id = SystemIO::post('new_id','int','');
    $title = SystemIO::post('title','def','');
    $data = array('full_name' =>$full_name,'email' =>$email,'content' =>$comment,'time_post' =>time(),'nw_id' =>$new_id,'ip_address' =>$ip_client,'status' =>0);
    //$check_captcha = $captcha->checkCaptcha($capcha);
    //if($check_captcha == false)
//    {
//        echo 3;die;
//    }    
    $cm = new Comment();
    $insert = $cm->insertData($data);
    if($insert)
        echo 1;
    else
        echo 0;
 }       
 function show_captchar()
 {
    $captcha= new Captcha(4);
    $src=$captcha->getCaptcha(false,true);
    echo $src;
 }  
 function approve_comment()
 {
    $id = SystemIO::post('id','int','');
    $status = SystemIO::post('status','int','');
    $user_name = UserCurrent::$current->data['user_name'];#var_dump($user_name);
    $update = '';
    if($status == 0)
        $update = news()->update('comment',array('status'=>'1','user_name' =>$user_name),"id = ". $id);  
    else
        $update = news()->update('comment',array('status'=>0,'user_name' =>$user_name),"id = ".$id);
    if($update)
       echo 1;
    else
       echo 0;    
 } 
 function delete_comment(){
    $id = SystemIO::post('id','int','');
    if($id)
        $dete = news()->delete('comment',"id = ".$id);
    else
        echo 0;
    if($dete) echo 1;
    else echo 0;        
 }
 function approve_adv(){
    $id = SystemIO::post('id','int','');
    $status = SystemIO::post('status','int','');
    $user_name = UserCurrent::$current->data['user_name'];#var_dump($user_name);
    $update = '';
    if($status == 0)
        $update = news()->update('banner',array('status'=>'1','user_name' =>$user_name),"id = ". $id);  
    else
        $update = news()->update('banner',array('status'=>0,'user_name' =>$user_name),"id = ".$id);
    if($update)
       echo 1;
    else
       echo 0;    
 }
 function delete_adv(){
    $id = SystemIO::post('id','int','');
    if($id)
        $dete = news()->delete('banner',"id = ".$id);
    else
        echo 0;
    if($dete) echo 1;
    else echo 0;        
 }
?>