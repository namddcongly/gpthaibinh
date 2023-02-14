<?php
if (defined(IN_JOC)) {
    die("Direct access not allowed!");
}
require_once 'application/news/frontend/includes/define.php';
require(APPLICATION_PATH . 'news' . DS . 'frontend' . DS . 'includes' . DS . 'frontend.news.php');

class Congly_Header
{
    function index()
    {
        joc()->set_file('Header', Module::pathTemplate('news') . "frontend/header.htm");
        Page::registerFile('main.css',
            'webskins' . DS . 'skins' . DS . 'news' . DS . 'css' . DS . 'main.css', 'header', 'css');
        Page::registerFile('style.css',
            'webskins' . DS . 'skins' . DS . 'news' . DS . 'css' . DS . 'style.css', 'header', 'css');
        joc()->set_var('root_url', ROOT_URL);
        $html = joc()->output("Header");
        joc()->reset_var();
        return $html;
    }

}
