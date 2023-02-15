<?php
require_once(APPLICATION_PATH . 'news' . DS . 'frontend' . DS . 'includes' . DS . 'frontend.news.php');

class Infographic
{
    function __construct()
    {
    }

    function index()
    {
        joc()->set_file('Detail', Module::pathTemplate() . 'frontend' . DS . 'detail.htm');
        $frontendObj = new FrontendNews();
        global $LIST_CATEGORY;
        global $LIST_CATEGORY_ALIAS;
        $id = SystemIO::get('id', 'int', 0);
        if ($id > 0) {
            $detail = $frontendObj->newsOne($id);
            $detail['content'] = $frontendObj->detail($id);
            $cate_id = $detail['cate_id'];
            if ($LIST_CATEGORY[$cate_id]['cate_id1'] == 0) {
                joc()->set_var('navigation',
                    '<a href="' . Url::Link(array('cate_id' => $cate_id, 'title' => $LIST_CATEGORY_ALIAS[$cate_id]),
                        'news',
                        'm_cate') . '" title="' . $LIST_CATEGORY[$cate_id]['name'] . '">' . $LIST_CATEGORY[$cate_id]['name'] . '</a> &raquo;');
            } else {
                $cate_id_parent = $LIST_CATEGORY[$cate_id]['cate_id1'];
                joc()->set_var('navigation', '<a href="' . Url::Link(array(
                        'cate_id' => $cate_id_parent,
                        'title' => $LIST_CATEGORY_ALIAS[$cate_id_parent]
                    ), 'news',
                        'm_cate') . '" title="' . $LIST_CATEGORY[$cate_id_parent]['name'] . '">' . $LIST_CATEGORY[$cate_id_parent]['name'] . '</a> &raquo; <a href="' . Url::Link(array(
                        'cate_id' => $cate_id,
                        'title' => $LIST_CATEGORY_ALIAS[$cate_id]
                    ), 'news',
                        'm_cate') . '" title="' . $LIST_CATEGORY[$cate_id]['name'] . '">' . $LIST_CATEGORY[$cate_id]['name'] . '</a>');
            }

            Page::setHeader($detail['title'], $detail['tag'], $detail['description']);
            joc()->set_var('title', $detail['title']);
            joc()->set_var('time_public_detail', date('d/n/Y H:i', $detail['time_public']));
            joc()->set_var('description', $detail['description']);
            joc()->set_var('detail', $frontendObj->showContent($detail['content']));
            joc()->set_var('author',
                ($detail['author'] != '' ? $detail['author'] : '') . ' ' . ($detail['origin'] != '' ? $detail['origin'] : ''));
        }
        $html = joc()->output('Detail');
        joc()->reset_var();
        return $html;
    }
}

?>