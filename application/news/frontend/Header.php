<?php
if (defined(IN_JOC)) {
    die("Direct access not allowed!");
}
require_once 'application/news/frontend/includes/define.php';
require(APPLICATION_PATH . 'news' . DS . 'frontend' . DS . 'includes' . DS . 'frontend.news.php');

class Header
{
    function index()
    {
        joc()->set_file('Header', Module::pathTemplate('news') . 'frontend/header.htm');
        Page::registerFile('main.css',
            'webskins' . DS . 'skins' . DS . 'news' . DS . 'css' . DS . 'main.css', 'header', 'css');
        Page::registerFile('style.css',
            'webskins' . DS . 'skins' . DS . 'news' . DS . 'css' . DS . 'style.css', 'header', 'css');
        $frontendObj = new FrontendNews();
        $page = SystemIO::get('page', 'def', 'home');
        global $list_category;
        global $list_category_alias;
        global $info_news;
        $list_category = $frontendObj->getCategory();
        $list_category_alias = SystemIO::arrayToOption($list_category, 'id', 'alias');
        $id = SystemIO::get('id', 'int', 0);
        $cate_id = SystemIO::get('cate_id', 'int', 0);
        if ($id && $cate_id == 0) {
            $detail = $frontendObj->newsOne($id);
            $detail_content = $frontendObj->detail($id);
            $info_news = $detail;
            $info_news['content'] = $detail_content;
        }

        joc()->set_var('root_url', ROOT_URL);
        $html = joc()->output('Header');
        joc()->reset_var();
        return $html;
    }

}
