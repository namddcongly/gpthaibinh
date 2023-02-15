<?php
require_once 'application/news/frontend/includes/frontend.news.php';
require_once 'application/news/includes/comment_model.php';
require_once 'application/user/includes/comment.php';

class Detail
{
    function __construct()
    {
    }

    function index()
    {
        joc()->set_file('Detail', Module::pathTemplate() . 'frontend' . DS . 'detail.htm');
        $newsObj = new FrontendNews();
        global $list_category;
        global $list_category_alias;
        $id = SystemIO::get('id', 'int', 0);
        joc()->set_var('id', $id);

        if ($id > 0) {
            $detail = $newsObj->newsOne($id);
            $detail_content = $newsObj->detail($id);
            $detail['content'] = $detail_content;
            $cate_id = $detail['cate_id'];
            if ($list_category[$cate_id]['cate_id1'] == 0) {
                joc()->set_var('cate_parent_name', $list_category[$cate_id]['name']);
                $cate_id_parent = $list_category[$cate_id]['cate_id1'];
            } else {
                $cate_id_parent = $list_category[$cate_id]['cate_id1'];
                joc()->set_var('cate_parent_name', $list_category[$cate_id]['name']);
            }

            joc()->set_block('Detail', 'SubCate', 'SubCate');


            $href_cate_parent = Url::link(array(
                'cate_id' => $cate_id_parent,
                'title' => $list_category_alias[$detail['cate_id']],
            ), 'news', 'cate');

            joc()->set_var('href_cate_parent', $href_cate_parent);
            $cate_text = '';
            foreach ($list_category as $cate) {
                if ($cate['cate_id1'] == $cate_id_parent) {

                    $cate_href = Url::link(array(
                        'cate_id' => $cate_id_parent,
                        'title' => $list_category_alias[$detail['cate_id']],
                    ), 'news', 'cate');

                    joc()->set_var('cate_name', $cate['name']);
                    joc()->set_var('cate_href', $cate_href);
                    $cate_text .= joc()->output('SubCate');
                }
            }
            joc()->set_var('SubCate', $cate_text);


            Page::setHeader($detail['title'], $detail['tag'], strip_tags($detail['description']));

            joc()->set_var('title', $detail['title']);
            joc()->set_var('time_public', date('d/n/Y H:i', $detail['time_public']) . ' UTC+7');
            joc()->set_var('description', strip_tags($detail['description']));
            joc()->set_var('img', IMG::showImgFrontend($detail));
            joc()->set_var('content', $newsObj->showContent($detail['content']));
            joc()->set_var('link_detail', ROOT_URL . Url::link_detail($detail, $list_category));

            joc()->set_var('author', $detail['author'] ? $detail['author'] : 'Admin');
            //Cac tin moi cap nhat cung danh muc
            $detail['cate_id'] = $detail['cate_id'] ?? 1;
            $sameCate = $newsObj->newsOther('cate_id=' . $detail['cate_id'] . ' AND id !=' . $id . ' AND time_public > 0',
                '0,10');

            joc()->set_block('Detail', 'SameCate', 'SameCate');
            $html_same = '';
            if (count($sameCate) > 0) {
                foreach ($sameCate as $n) {
                    joc()->set_var('href', Url::Link(array(
                        'id' => $n['nw_id'],
                        'title' => $n['title'],
                        'cate_id' => $n['cate_id'],
                        'cate_alias' => $list_category_alias[$n['cate_id']]
                    ), 'news', 'detail'));
                    joc()->set_var('title', $n['title']);
                    joc()->set_var('html_title', htmlspecialchars($n['title']));
                    joc()->set_var('date', date("d/n", $n['time_public']));
                    $html_same .= joc()->output('SameCate');
                }
            }

            joc()->set_var('SameCate', $html_same);
            $html = joc()->output('Detail');
            joc()->reset_var();
            return $html;
        }
    }
}

?>