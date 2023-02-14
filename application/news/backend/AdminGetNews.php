<?php
if (defined(IN_JOC)) die("Direct access not allowed!");
ini_set('display_errors', 0);
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/main/includes/user.php';
require_once 'application/includes/vnexpress.php';
require_once 'application/includes/vnreview.php';
require_once 'application/includes/dantri.php';
require_once 'application/includes/tuoitre.php';
require_once 'application/news/backend/includes/define.config.database.php';
require_once UTILS_PATH . 'paging.php';
require_once UTILS_PATH . 'image.upload.php';

class AdminGetNews
{
    public $list_data;
    public $path;
    public $object;
    public $origin;
    function __construct()
    {
        $user_info = UserCurrent::$current->data;
        if (!$user_info['id']) {
            Url::urlDenied();
        }
    }

    function delete_files($path, $del_dir = FALSE, $level = 0)
    {
        // Trim the trailing slash
        $path = preg_replace("|^(.+?)/*$|", "\\1", $path);
        if (!$current_dir = @opendir($path)) return;
        while (FALSE !== ($filename = @readdir($current_dir))) {
            if ($filename != "." and $filename != "..") {
                if (is_dir($path . '/' . $filename)) {
                    // Ignore empty folders
                    if (substr($filename, 0, 1) != '.') {
                        delete_files($path . '/' . $filename, $del_dir, $level + 1);
                    }
                } else {
                    unlink($path . '/' . $filename);
                }
            }
        }
        @closedir($current_dir);
        if ($del_dir == TRUE AND $level > 0) {
            @rmdir($path);
        }
    }

    function index()
    {
        $src = SystemIO::get('src', 'str', '');
        switch ($src) {
            default:
                return $this->getNews();
                break;
        }
    }

    function get_data($src){
        if ($src == 'thethao') {
            $path = 'Thể thao';
            $link = 'http://vnexpress.net/rss/the-thao.rss';
            $this->object = new vnexpress($link);
            $list_news = $this->object->data;
            $this->origin = 'vnexpress';
        } elseif($src == 'congnghe') {
            $path = 'Công nghệ';
            $link = 'http://vnexpress.net/rss/so-hoa.rss';
            $this->object = new vnexpress($link);
            $this->origin = 'vnexpress';
            $list_news = $this->object->data;
            
        }
        elseif($src == 'thegioi-dantri'){
            $path = 'Thế giới';
            $link = 'http://dantri.com.vn/the-gioi.rss';
            $this->object = new dantri($link);
            $this->origin = 'dantri';
            $list_news = $this->object->data;
        }
        elseif($src == 'thegioi-tuoitre'){
            $path = 'Thế giới';
            $link = 'http://tuoitre.vn/rss/tt-the-gioi.rss';
            $this->object = new tuoitre($link);
            $this->origin = 'tuoitre';
            $list_news = $this->object->data;
        }
        elseif($src == 'congnghe-tuoitre'){
            $path = 'Công nghệ';
            $link = 'http://tuoitre.vn/rss/tt-nhip-song-so.rss';
            $this->object = new tuoitre($link);
            $this->origin = 'tuoitre';
            $list_news = $this->object->data;
        }
        else{
            $path = 'Công nghệ - Vnreview';
            $this->object = new vnreview();
            $list_news = $this->object->data;
            $this->origin = 'vnreview';
        }
        $this->path = $path;
        $this->list_data = $list_news;
    }
    function getNews()
    {
        joc()->set_file('GetNews', Module::pathTemplate() . "backend/get_news_xahoi.htm");
        Page::registerFile('admin Js', Module::pathSystemJS() . 'admin.js', 'header', 'js');
        Page::setHeader("Lấy tin tự động", "Lấy tin tự động", "Lấy tin tự động");
        $src = SystemIO::get('src','def', 'thethao');
        $this->get_data($src);
        joc()->set_var('src_site',$src);
        joc()->set_block('GetNews', 'ListRow', 'ListRow');
        $stt = 1;
        $text_html = '';
        foreach ($this->list_data as $id => $value) {
            $row = $this->object->get_data_basic($id);
            ++$stt;
            joc()->set_var('title', $row['title']);
            joc()->set_var('href', $row['link']);
            joc()->set_var('path', $this->path);
            joc()->set_var('nw_id', $id);
            joc()->set_var('time_created', date('H:i d-m-Y', $row['time_public']));
            joc()->set_var('description', $row['description']);
            joc()->set_var('stt', $stt);
            joc()->set_var('origin', $this->origin);
            joc()->set_var('src_site', $src);
            joc()->set_var('src', $row['images']);
            $text_html .= joc()->output('ListRow');

        }
        joc()->set_var('ListRow', $text_html);
        $html = joc()->output("GetNews");
        joc()->reset_var();
        return $html;
    }
}