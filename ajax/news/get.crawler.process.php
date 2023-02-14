<?php
ini_set('display_errors', 1);
require_once 'application/includes/vnexpress.php';
require_once 'application/includes/vnreview.php';
require_once 'application/includes/dantri.php';
require_once 'application/includes/tuoitre.php';
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/backend/includes/get.news.php';
require(APPLICATION_PATH . 'news' . DS . 'frontend' . DS . 'includes' . DS . 'frontend.news.php');
require_once 'application/news/backend/includes/define.config.database.php';
require_once UTILS_PATH . 'convert.php';
$action = SystemIO::post('action', 'def', '');
$news_id = SystemIO::post('nw_id', 'int', 0);
$src = SystemIO::post('src', 'def', '');
$user_info = UserCurrent::$current->data;
$newsObj = new BackendNews();
if ($src == 'thethao'){
    $cate_id = 314;
    $link = 'http://vnexpress.net/rss/the-thao.rss';
    $vnexpress = new vnexpress($link);
    $row = $vnexpress->get_all_content($news_id);
    $origin = 'vnexpress';
}
elseif($src =='congnghe'){
    $link = 'http://vnexpress.net/rss/so-hoa.rss';
    $vnexpress = new vnexpress($link);
    $row = $vnexpress->get_all_content($news_id);
    $origin = 'vnexpress';
    $cate_id = 307;
}
elseif($src == 'thegioi-dantri'){
    $link = 'http://dantri.com.vn/the-gioi.rss';
    $vnexpress = new dantri($link);
    $row = $vnexpress->get_all_content($news_id);
    $origin = 'dantri';
    $cate_id = 303;
}
elseif($src == 'thegioi-tuoitre') {
    $link = 'http://tuoitre.vn/rss/tt-the-gioi.rss';
    $vnexpress = new tuoitre($link);
    $row = $vnexpress->get_all_content($news_id);
    $origin = 'tuoitre';
    $cate_id = 303;
}
elseif($src == 'congnghe-tuoitre') {
    $link = 'http://tuoitre.vn/rss/tt-nhip-song-so.rss';
    $vnexpress = new tuoitre($link);
    $row = $vnexpress->get_all_content_congnghe($news_id);
    $origin = 'tuoitre';
    $cate_id = 307;
}
else
{
    $vnreview = new vnreview();
    $row = $vnreview->get_all_content($news_id);
    $cate_id = 307;
    $origin = 'vnreview';
}


switch ($action) {
    case 'get_news':
        $arrNewData = array(
            'user_id' => $user_info['id'],
            'cate_id' => $cate_id,
            'cate_path' => ',' . $cate_id . ',',
            'title' => strip_tags($row['title']),
            'description' => strip_tags($row['description']),
            'img1' => Convert::convertLinkTitle($row['title']).'.jpg',
            'origin' => $origin,
            'type_post' => 0,
            'time_public' => 0,
            'time_created' => time(),
            'tag' => $row['tag'].'[]'.Convert::convertLinkTitle($row['tag']),
        );


        $countRecord = $newsObj->countRecord('store', 'title LIKE "%' . str_replace('"', '&quot;', $row['title']) . '%"');
        if ($countRecord) {
            echo 1;
            die;
        }
        $id = $newsObj->insertData('store', $arrNewData);
        if ($id) {
            $newsObj->insertData('store_content', array('content' => $row['content'], 'nw_id' => $id));
            $newsObj->insertData('store_hit', array('nw_id' => $id, 'hit' => 1, 'time_created' => $arrNewData['time_created'], 'cate_path' => $arrNewData['cate_path']));
            $arrNewSEO = array(
                'nw_id' => $id,
                'title_seo' => $arrNewData['title'],
                'description_seo' => $arrNewData['description'],
                'keyword_seo' => $arrNewData['tag'],

            );
            $arrNewsSearch = array(
                'nw_id' => $id,
                'cate_id' => $arrNewData['cate_id'],
                'cate_path' => $arrNewData['cate_path'],
                'keyword' => Convert::convertUtf8ToSMS($arrNewData['title'] . ' ' . $arrNewData['description'] . ' ' . $arrNewData['tag']) . ' ' . Convert::convertUtf8ToTelex($arrNewData['title'] . ' ' . $arrNewData['description'] . ' ' . $arrNewData['tag'])

            );
            $newsObj->insertSeo($arrNewSEO);
            $newsObj->insertData('search',$arrNewsSearch);
            $img = file_get_contents($row['images']);
            $path_img_save = NEWS_IMG_UPLOAD . date('Y/n/j', time());
            file_put_contents($path_img_save . '/' . Convert::convertLinkTitle($row['title']).'.jpg', $img);
            echo 1;
        } else {
            echo 0;
        }


}