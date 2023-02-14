<?php

/**
 * Created by PhpStorm.
 * User: namdd
 * Date: 3/6/2017
 * Time: 1:19 PM
 */
class vnreview
{
    public $data;

    function __construct()
    {

        $opts = array('http'=>array('header' => "User-Agent:Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.75 Safari/537.1\r\n", 'timeout' => "360"));
        $context = stream_context_create($opts);
        $url = 'http://vnreview.vn/feed/-/rss/home';
        $xmlstring = file_get_contents($url, false, $context);
        $xmlstring = str_replace('<![CDATA[', '', $xmlstring);
        $xmlstring = str_replace(']]>', '', $xmlstring);
        $xmlstring = str_replace('<a href="', '||', $xmlstring);
        $xmlstring = str_replace('<img', '', $xmlstring);
        $xmlstring = str_replace('></a></br>', '||', $xmlstring);
        $xmlstring = str_replace('"> width=130 height=100 src="', '||', $xmlstring);
        $xmlstring = str_replace('_180x108.jpg" ', '.jpg', $xmlstring);
        $xml = simplexml_load_string($xmlstring);
        $xml = $xml->channel;
        $json = json_encode($xml);
        $array = json_decode($json, true);
        $this->data = $array['item'];
    }

    public function get_data_basic($id)
    {
        $news = array();
        $data = $this->data[$id];
        $news['title'] = $data['title'];
        $news['description'] = $data['description'];
        $news['images'] = $data['guid'];
        $news['time_public'] = strtotime($data['pubDate']);
        $news['link'] = $data['link'];
        return $news;
    }

    public function get_all_content($id)
    {
        $news = $this->get_data_basic($id);
        $opts = array('http'=>array('header' => "User-Agent:Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.75 Safari/537.1\r\n", 'timeout' => "360"));
        $context = stream_context_create($opts);
        $contents = file_get_contents($news['link'], false, $context);
        $news['tag'] = $this->get_tag($contents);
        $contents = explode('<div class="journal-content-article">', $contents);
        $contents = $contents['1'];
        $contents = explode('<div class="social_share_item">', $contents);
        $contents = $contents['0'];
        $contents = explode('<div class="social_share">',$contents);
        $contents = $contents['0'];
        $contents = str_replace($news['description'],'',$contents);
        $news['content'] = $contents;
        return $news;
    }

    public function get_tag($contents)
    {
        //$patter = '/<span class=\"taglib-asset-tags-summary\">(.*)<\/span>/';
        $tag = explode('<span class="taglib-asset-tags-summary">', $contents);
        $tag = $tag['1'];
        $tag = explode('</span>', $tag);
        $tag = $tag['0'];
        $tag = strip_tags($tag);
        $tag = preg_replace('/(\s+)/',' ',$tag);
        return $tag;
    }

}
?>