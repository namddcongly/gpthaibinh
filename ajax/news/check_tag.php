<?php
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/backend/includes/backend.news.php';

$newsObj = new BackendNews();

$tag = SystemIO::post("tag", "str", "");
if ($tag != "") {
    $arrTagUtf8 = explode(',', $tag);
    $tag = Convert::convertUtf8ToSMS($tag);
    $arrTag = explode(',', $tag);
    $arrCountTag = array();
    for ($i = 0; $i < count($arrTag); ++$i) {
        $tagValue = str_replace(' ', '-', trim($arrTag[$i]));
        $arrCountTag[$i] = $newsObj->countRecord('tag_meta', "tag LIKE '%$tagValue%'");
    }
    $tagOk = '';
    foreach ($arrCountTag as $index => $count) {
        if ($count) {
            $tagOk .= $arrTagUtf8[$index] . ',';
        }
    }

    $tagOk = trim($tagOk, ',');
    echo json_encode(array('tag' => $tagOk));
}
?>