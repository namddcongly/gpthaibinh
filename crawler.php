<html
<header>
</header>
<body>
<?php
ini_set('display_errors', 1);
session_cache_expire(3600);
session_start();
ini_set('session.gc-maxlifetime', 3600);
date_default_timezone_set('Asia/Bangkok');
include 'define.php';
require_once 'application/news/backend/includes/backend.news.php';
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_URL => 'http://gpthaibinh.etstech.com.vn/crawler_www3.php',
    CURLOPT_SSL_VERIFYPEER => false
));
$data = curl_exec($curl);
$newsObj = new BackendNews();

$datas = json_decode($data, true);
var_dump($datas);
die;
foreach ($datas as $t) {
    $content = $t['content'];
    $t['user_id'] = 1;
    unset($t['content']);
    $id = $newsObj->insertData('store', $t);
    if ($id) {
        $newsObj->insertData('store_content', array('nw_id' => $id, 'content' => $content));
    }
}
echo $id;
echo 'ngon';
die;
?>
</body>
