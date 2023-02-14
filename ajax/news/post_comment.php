<?php
require_once 'application/news/includes/comment_model.php';

$comm = new CommentModel();

$id         = SystemIO::get('id', 'int', 0);
$title      = SystemIO::get('title', 'str', '');

$full_name  = SystemIO::get('fullname', 'str', '');
$email      = SystemIO::get('mail', 'str', '');
$content    = SystemIO::get('comment', 'str', '');

$insert = array(
                "news_id"       => $id, 
                "full_name"     => $email,
                "email"         => $email,
                "content"       => $content,
                "time_post"     => time(),
                "nw_title"      => $title,
                "ip_address"    => $_SERVER['REMOTE_ADDR']);
if($id > 0 && $title != "" && $full_name != "" && $email!="" && $content != "")
{
	if($comm -> insertData($insert))
	json_encode(array("text" => "Thêm mới thành công"));
	else
	json_encode(array("text" => "Thêm mới không thành công"));
}
else
json_encode(array("text" => "Thông tin bạn nhập không hợp lệ"));

?>