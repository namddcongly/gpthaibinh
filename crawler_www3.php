<header>
    <meta charset="utf-8">
</header>
<?php
ini_set('display_errors', 1);
session_cache_expire(3600);
session_start();
ini_set('session.gc-maxlifetime', 3600);
ini_set('memory_limit',-1);
date_default_timezone_set('Asia/Bangkok');
include 'define.php';
require_once 'application/news/frontend/includes/frontend.news.php';
$backendNews = new FrontendNews();
//header('Content-Type: text/html; charset=utf-8');
#$data = $backendNews->getNews('store1', '*', 'ChannelId = 5 AND  PublishedTime > "2020-10-01" AND PublishedTime < "2021-01-01"', 'PublishedTime ASC');
//$data = $backendNews->getNews('store1', '*', 'ChannelId = 5 AND  PublishedTime > "2021-01-01" AND PublishedTime < "2022-01-01"', 'PublishedTime ASC');
$data = $backendNews->getNews('tblArticle', '*', '1=1', 'CreatedDate ASC', '0,2000');
$arr = array();
$i = 0;
require_once 'application/news/backend/includes/backend.news.php';
$newsObj = new BackendNews();

foreach ($data as $tmp) {
    $gl = json_decode($tmp['Gallery'],true);
    $i = 0;
    $arrCateId = explode(',', trim($tmp['CategoryIDList'], ','));
    $cateId = end($arrCateId);
    $arr[$i]['title'] = $tmp['Name'];
    $arr[$i]['user_id'] = 1;
    $arr[$i]['author'] = 'Admin';
    $arr[$i]['tag'] = $tmp['TagNameList'];
    $arr[$i]['time_created'] = strtotime($tmp['CreatedDate']);
    $arr[$i]['cate_id'] = (int)$cateId;
    $arr[$i]['cate_path'] = $tmp['CategoryIDList'];
    $arr[$i]['img1'] = 'https://giaophanthaibinh.org/'.trim($gl['0']['Path'],'/');
    $arr[$i]['description'] = $tmp['Description'];
    $arr[$i]['time_public'] = strtotime($tmp['CreatedDate']);
    #$arr[$i]['content'] = $tmp['LongDescription'];
    $arr[$i]['url'] = $tmp['FriendlyUrl'];
    try {
        $id = $newsObj->insertData('store', $arr[$i]);
    } catch (Exception $exception) {
        echo $exception->getMessage();
    }

    if ($id) {
        $newsObj->insertData('url_rewrite', array('entity_id' => $id,'type' => 'news', 'request_path' => $tmp['FriendlyUrl']));
        $newsObj->insertData('store_content', array('nw_id' => $id, 'content' => $tmp['LongDescription']));
    }
}

echo $id;
?>
