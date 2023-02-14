<?php
global $NEWS_PROPERTY;
define('NEWS_FEATURED',0x00000001);
define('NEWS_NEW',0x00000002);
define('NEWS_FEATURED_FOCUS',0x00000004);
define('NEWS_FEATURED_CATE',0x00000008);
$NEWS_PROPERTY=array(
	NEWS_FEATURED =>'<font color="#990000">Tin tiêu điểm</font>',
	NEWS_FEATURED_FOCUS =>'Tin nổi bật',
	NEWS_NEW    =>'Tin mới nhất trang chủ',
	NEWS_FEATURED_CATE=>'Tin nổi bật mục'
);
?>
