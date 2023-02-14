<?php

/**
 * Created by PhpStorm.
 * User: namdd
 * Date: 3/6/2017
 * Time: 1:19 PM
 */
class Tuoitre
{
    public $data;

    function __construct($url = 'http://tuoitre.vn/rss/tt-the-gioi.rss')
    {

        $opts = array('http' => array('header' => "User-Agent:Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.75 Safari/537.1\r\n", 'timeout' => "360"));
        $context = stream_context_create($opts);
        $xmlstring = file_get_contents($url, false, $context);
        $xmlstring = str_replace('<![CDATA[', '', $xmlstring);
        $xmlstring = str_replace(']]>', '', $xmlstring);
        $xmlstring = str_replace('<a href="', '||', $xmlstring);
        $xmlstring = str_replace('<img', '', $xmlstring);
        $xmlstring = str_replace('></a></br>', '||', $xmlstring);
        $xmlstring = str_replace(array('src="', '.jpg"'), array('||', '.jpg||'), $xmlstring);
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
        $arr_description = explode('||', $data['description']);
        $news['images'] = str_replace('s146', 's490', $arr_description['2']);
        $news['description'] = $arr_description['4'];
        $news['time_public'] = strtotime($data['pubDate']);
        $news['link'] = $data['link'];
        return $news;
    }

    public function get_all_content($id)
    {
        $news = $this->get_data_basic($id);
        $opts = array('http' => array('header' => "User-Agent:Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.75 Safari/537.1\r\n", 'timeout' => "360"));
        $context = stream_context_create($opts);
        $contents = file_get_contents($news['link'], false, $context);
        $contents = explode('<div class="fck ">', $contents);
        $contents = $contents['1'];
        $contents = explode('<ul class="block-key">', $contents);
        $tag = $contents['1'];
        $tag = explode('</ul>', $tag);
        $tag = $tag['0'];
        $news['tag'] = $this->get_tag($tag);
        $contents = $contents['0'];
        $contents = explode('<div class="wrapper-qt">', $contents);
        $contents1 = $contents['0'];
        $news['author'] = trim(strip_tags($contents['1']));
        $news['content'] = $contents1;
        return $news;
    }

    public function get_all_content_congnghe($id){
        $news = $this->get_data_basic($id);
        $opts = array('http'=>array('header' => "User-Agent:Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.75 Safari/537.1\r\n", 'timeout' => "360"));
        $context = stream_context_create($opts);
        $contents = file_get_contents($news['link'], false, $context);
        $contents = explode('<div class="fck">', $contents);
        $contents = $contents['1'];
        $contents = explode('<p class="txt-head">',$contents);
        $contents =  trim(str_replace($news['description'],'',$contents[1]),'</p>');
        $contents = explode('<div class="wrapper-qt">',$contents);
        $contents1 = $contents['0'];
        $contents = explode('<ul class="block-key">',$contents['1']);
        $tag = $contents['1'];
        $tag = explode('</ul>',$tag);
        $tag = $tag['0'];
        $news['tag'] = $this->get_tag($tag);
        $news['content'] = trim($contents1);
        return $news;
    }
    public function get_tag($contents)
    {
        $tag = explode('<li>', $contents);
        foreach ($tag as $k => &$v) {
            $v = trim(strip_tags($v));
        }
        array_splice($tag, 0, 2);
        $str_tag = '';
        foreach ($tag as $k => $value) {
            $str_tag .= $value . ',';
        }

        return trim($str_tag, ',');
    }

}

?>