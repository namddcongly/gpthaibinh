<?php

/**
 * Created by PhpStorm.
 * User: namdd
 * Date: 3/8/2017
 * Time: 11:17 AM
 */
class vnexpress
{
    public $data;

    function __construct($link)
    {
        $xmlstring = file_get_contents($link);
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
        $data['description'] = explode('||', $data['description']);
        $news['title'] = $data['title'];
        $news['description'] = $data['description']['3'];
        $news['time_public'] = strtotime($data['pubDate']);
        $news['images'] = $data['description']['2'];
        $news['link'] = $data['link'];
        return $news;
    }

    public function get_all_content($id)
    {
        $news = $this->get_data_basic($id);
        $contents = file_get_contents($news['link']);
        $news['tag'] = $this->get_tag($contents);
        $contents = explode('article class="content_detail fck_detail width_common block_ads_connect">', $contents);
        $contents = $contents['1'];
        $contents = explode('</strong>', $contents);
        $contents = $contents['0'] . '</strong></p>';
        $news['content'] = $contents;
        return $news;
    }

    public function get_tag($contents)
    {
        $tag = '';
        $patter = '/<h4>(.*)<\/h4>/';
        preg_match_all($patter, $contents, $m);
        if (isset($m['1']['3'])) {
            $tag = str_replace('</a>', '</a>,', $m['1']['3']);
            $tag = strip_tags($tag);
        } else {
            $tag = $this->get_tag_congnghe($contents);
        }

        return $tag;
    }

    public function get_tag_congnghe($contents)
    {
        $patter = '/<a(.*)\s+class=\"tag_item\">(.*)<\/a>/';
        preg_match_all($patter, $contents, $m);
        $tag = '';
        $arr_tag = $m['0'];
        for ($i = 0; $i < count($arr_tag); ++$i) {
            $tag .= $arr_tag[$i] . ',';
        }
        $tag = strip_tags($tag);
        return $tag;
    }

}