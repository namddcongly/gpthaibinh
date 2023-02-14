<?php

/**
 * Created by PhpStorm.
 * User: namdd
 * Date: 3/6/2017
 * Time: 1:19 PM
 */
class dantri
{
    public $data;

    function __construct($url = 'http://dantri.com.vn/the-gioi.rss')
    {

        $opts = array('http'=>array('header' => "User-Agent:Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.75 Safari/537.1\r\n", 'timeout' => "360"));
        $context = stream_context_create($opts);
        $xmlstring = file_get_contents($url, false, $context);
        $xmlstring = str_replace('<![CDATA[', '', $xmlstring);
        $xmlstring = str_replace(']]>', '', $xmlstring);
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
        $news['description'] = '';
        $news['tag'] = '';
        $news['images'] = $data['description']['a']['img']['@attributes']['src'];
        $news['images'] = str_replace('zoom/80_50/','',$news['images']);
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
        $contents = explode('<h2 class="fon33 mt1 sapo">', $contents);
        $contents = $contents['1'];
        $contents = explode('</h2>',$contents);
        $description = $contents['0'];
        $description = explode('<br />',$description);
        $news['description'] = $description['0'];
        $contents =  explode('<div class="clearfix mgt20 bottom-sharing">',$contents['1']);
        $contents = explode('<div id="divNewsContent" class="fon34 mt3 mr2 fon43 detail-content">',$contents['0']);
        $contents = explode('<style>',$contents['1']);
        $contents = explode('<div class="news-tag">',$contents['0']);
        $tag = $contents['1'];
        $contents = trim($contents['0']);
        $news['content'] = $contents;
        $news['tag'] = strip_tags(str_replace('Tag :','',$tag));
        $news['tag'] = trim($news['tag']);
        return $news;
    }

    public function get_tag()
    {
        return '';
    }

}

?>