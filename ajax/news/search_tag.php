<?php
require_once 'application/news/backend/includes/backend.news.php';
$newsObj = new BackendNews();
$tagName = SystemIO::post("tag", "str", "");
if ($tagName != '') {
    $tagName = str_replace(' ', '-', Convert::convertUtf8ToSMS($tagName));
    $tags = $newsObj->getNews('tag_meta', '*', 'tag LIKE "%' . $tagName . '%" OR name LIKE "%'.$tagName.'%"');
    foreach ($tags as $tag) {
        echo '<li style="margin-left:150px;" id="tag_' . $tag['id'] . '" class="no-choose">
        &nbsp;<input type="checkbox" value="' . $tag['name'] . '" name="tag[]" onclick="setTag(this,' . $tag['id'] . ');">' . $tag['name'] . '</li>';
    }
}
?>