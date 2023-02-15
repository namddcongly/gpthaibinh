<?php
require_once(APPLICATION_PATH . 'news' . DS . 'frontend' . DS . 'includes' . DS . 'frontend.news.php');
require_once 'application/news/frontend/includes/define.php';
//require_once(UTILS_PATH .'pagination_mobile.php');
require_once UTILS_PATH . 'paging.php';

class Cate
{
    function __construct()
    {
    }

    function index()
    {
        joc()->set_file('Browse', Module::pathTemplate('news') . 'frontend' . DS . 'cate.htm');
        $cate_id = SystemIO::get('cate_id', 'int', 1);

        $frontendObj = new FrontendNews();
        global $list_category;
        global $list_category_alias;
        $page_no = SystemIO::get('page_no', 'int', 0);
        $limit = 20 * $page_no . ',21';

        if ($page_no == 0) {
            $limit = '0,20';
        }
        if ($list_category[$cate_id]['cate_id1'] == 0) {
            joc()->set_var('cate_parent_name', $list_category[$cate_id]['name']);
            $cate_id_parent = $list_category[$cate_id]['cate_id1'];
        } else {
            $cate_id_parent = $list_category[$cate_id]['cate_id1'];
            joc()->set_var('cate_parent_name', $list_category[$cate_id]['name']);
        }

        joc()->set_block('Browse', 'SubCate', 'SubCate');
        $href_cate_parent = Url::link(array(
            'cate_id' => $cate_id_parent,
            'title' => $list_category_alias[$cate_id],
        ), 'news', 'cate');

        joc()->set_var('href_cate_parent', $href_cate_parent);
        $cate_text = '';
        foreach ($list_category as $cate) {
            if ($cate['cate_id1'] == $cate_id_parent) {

                $cate_href = Url::link(array(
                    'cate_id' => $cate_id_parent,
                    'title' => $list_category_alias[$cate_id],
                ), 'news', 'cate');

                joc()->set_var('cate_name', $cate['name']);
                joc()->set_var('cate_href', $cate_href);
                $cate_text .= joc()->output('SubCate');
            }
        }
        joc()->set_var('SubCate', $cate_text);

        if ($page_no > 1) {
            Page::setHeader(
                $list_category[$cate_id]['title'] . '- Trang ' . $page_no,
                $list_category[$cate_id]['keyword'], $list_category[$cate_id]['description'] . ' Trang ' . $page_no
            );
        } else {
            Page::setHeader(
                $list_category[$cate_id]['title'], $list_category[$cate_id]['keyword'],
                $list_category[$cate_id]['description']
            );
        }

        if ($list_category[$cate_id]['cate_id1'])// danh muc con
        {
            $list_news = $frontendObj->getNews('store', 'id,title,description,img1,time_public,time_created,cate_id',
                'cate_id=' . $cate_id, 'time_public DESC', $limit, 'id');
        } else {
            $list_news = $frontendObj->getNews('store', 'id,title,description,img1,time_public,time_created,cate_id',
                'cate_path LIKE "%,' . $cate_id . ',%"', 'time_public DESC', $limit, 'id');
        }

        $first = current($list_news);
        $this->setDataRow($first, $list_category, 'f');
        $row1 = next($list_news);
        $this->setDataRow($row1, $list_category, 'r1');
        $row2 = next($list_news);
        $this->setDataRow($row2, $list_category, 'r2');
        $row3 = next($list_news);
        $this->setDataRow($row3, $list_category, 'r3');
        $row4 = next($list_news);
        $this->setDataRow($row4, $list_category, 'r4');

        joc()->set_block('Browse', 'Row', 'Row');
        $txt_html = '';
        $k = 1;
        foreach ($list_news as $row) {
            if ($k < 20) {
                joc()->set_var('title', $row['title']);
                joc()->set_var('html_title', htmlspecialchars($row['title']));
                joc()->set_var('public', date('H:i d/n/Y', $row['time_public']));
                joc()->set_var('description', SystemIO::strLeft(strip_tags($row['description']), 250, ''));
                $href = Url::link_detail($row, $list_category);
                joc()->set_var('href', $href);
                $img = IMG::showImgFrontend($row);
                if ($row['img1'] == '') {
                    joc()->set_var('img_html', '');
                } else {
                    joc()->set_var('img_html', $img);
                }

                $txt_html .= joc()->output('Row');
            }
            ++$k;
        }

        joc()->set_var('Row', $txt_html);

        if (count($list_news) > 21) {
            array_pop($list_news);
            $view_more = Url::link(array(
                'cate_id' => $cate_id,
                'title' => $list_category_alias[$cate_id],
                'page_no' => ($page_no + 1)
            ), 'news', 'cate');

            joc()->set_var('view_more',
                '<a id="category_paging_article" href="' . $view_more . '" class="btn-see-more">Xem thêm<i class="fas fa-sort-down"></i></a>');

        } else {
            joc()->set_var('view_more', '');
        }
        $html = joc()->output('Browse');
        joc()->reset_var();
        return $html;
    }

    public function setDataRow($row, $list_category, $prefix = '')
    {
        joc()->set_var($prefix . '_title', $row['title']);
        joc()->set_var($prefix . '_img', IMG::showImgFrontend($row['time_created']));
        joc()->set_var($prefix . '_html_title', htmlspecialchars($row['title']));
        joc()->set_var($prefix . '_description', $row['description']);
        joc()->set_var($prefix . '_href', Url::link_detail($row, $list_category));
    }
}

?>