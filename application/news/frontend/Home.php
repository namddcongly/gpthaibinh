<?php
//ini_set('display_errors',1);
if (defined(IN_JOC)) {
    die("Direct access not allowed!");
}
require_once 'application/news/frontend/includes/define.php';

class Home
{
    function index()
    {
        joc()->set_file('Home_Center', Module::pathTemplate() . 'frontend/home_center.htm');
        Page::setHeader('Giáo phận Thái Bình', 'Giáo phận Thái Bình');
        /*Tin nổi bật trang chủ*/
        global $list_category;
        $frontendObj = new FrontendNews();
        $news_home_highlights = $frontendObj->getNewsHome('*',
            'time_public < ' . time() . ' AND  time_public > 0 AND property & 1 = 1', 15);

        $rowTop = current($news_home_highlights);
        joc()->set_var('href_0', Url::link_detail($rowTop, $list_category));
        joc()->set_var('title_0', $rowTop['title']);
        joc()->set_var('html_title_0', htmlspecialchars($rowTop['title']));
        joc()->set_var('description_0', SystemIO::strLeft(strip_tags($rowTop['description']), 300));
        joc()->set_var('img_0', IMG::showImgFrontend($rowTop));
        joc()->set_var('date_0', date('d-m-Y', $rowTop['time_public']));
        $k = 1;
        foreach ($news_home_highlights as $row) {
            if ($row['nw_id'] != $rowTop['nw_id']) {
                joc()->set_var('href_' . $k, Url::link_detail($row, $list_category));
                joc()->set_var('title_' . $k, $row['title']);
                joc()->set_var('html_title_' . $k, htmlspecialchars($row['title']));
                joc()->set_var('description_' . $k, SystemIO::strLeft(strip_tags($row['description']), 300));
                joc()->set_var('img_' . $k, IMG::thumb($row));
                joc()->set_var('date_' . $k, date('d-m-Y', $row['time_public']));
                ++$k;
            }
        }
        $listFocus1 = array_slice($news_home_highlights, 5, 10);

        joc()->set_block('Home_Center', 'Focus', 'Focus');
        $txt_focus = '';
        foreach ($listFocus1 as $row) {
            joc()->set_var('title', $row['title']);
            joc()->set_var('title_cut', SystemIO::strLeft($row['title'], 250));
            joc()->set_var('html_title', htmlspecialchars($row['title']));
            $href = Url::link_detail($row, $list_category);
            joc()->set_var('href', $href);
            $txt_focus .= joc()->output('Focus');

        }

        joc()->set_var('Focus', $txt_focus);
        joc()->set_block('Home_Center', 'FocusRight', 'FocusRight');
        $listFocus2 = array_slice($news_home_highlights, 15, 5);
        $txt_focus2 = '';
        foreach ($listFocus2 as $row) {
            joc()->set_var('title', $row['title']);
            joc()->set_var('title_cut', SystemIO::strLeft($row['title'], 250));
            joc()->set_var('html_title', htmlspecialchars($row['title']));
            joc()->set_var('img', IMG::thumb($row));
            $href = Url::link_detail($row, $list_category);
            joc()->set_var('href', $href);
            $txt_focus2 .= joc()->output('FocusRight');

        }

        joc()->set_var('FocusRight', $txt_focus2);

        joc()->set_var('cate_447', $this->showCate(447, $frontendObj));

        $html = joc()->output('Home_Center');
        joc()->reset_var();
        return $html;
    }

    public function showCate($cate_id, $frontendObj)
    {
        global $list_category;
        $newsCate = $this->getNewsCate($cate_id, $frontendObj);
        ksort($newsCate);
        $cateData = '<div class="home-category home-category-' . $cate_id . '" style="overflow: hidden">
            <div class="left">
                <div class="container-article-2">' . $this->showListCateChild($cate_id) . '
                    <div class="box-article">
                        <div class="box-main">
                            <div class="main-article">
                                <div class="img">
                                    <a href="' . Url::link_detail($newsCate['0'], $list_category) . '">
                                        <img src="' . IMG::showImgFrontend($newsCate['0']) . '"
                                             alt="' . htmlspecialchars($newsCate['0']['title']) . '"/></a>
                                </div>
                                <h3 class="article-title">
                                    <a href="' . Url::link_detail($newsCate['0'],
                $list_category) . '">' . $newsCate['0']['title'] . '</a>
                                </h3>
                                <p>' . $newsCate['0']['description'] . '</p>
                                <div class="info">
                            <span class="time">
                                <i class="fad fa-clock"></i>' . date($newsCate['0']['time_public'], 'd-m-Y H:i:s') . '
                            </span>
                                </div>
                            </div>
                        </div>
                        <div class="scroll-article">
                            <div class="article-list">
                                <div class="article-item">
                                    <div>
                                        <a href="' . Url::link_detail($newsCate['1'],
                $list_category) . '"><img src="' . IMG::showImgFrontend($newsCate['1']) . '" alt=""/></a>
                                        <a href="' . Url::link_detail($newsCate['1'], $list_category) . '" class="article-link"></a>
                                    </div>
                                     <div>
                                        <a href="' . Url::link_detail($newsCate['2'],
                $list_category) . '"><img src="' . IMG::showImgFrontend($newsCate['2']) . '" alt=""/></a>
                                        <a href="' . Url::link_detail($newsCate['2'],
                $list_category) . '" class="article-link">' . $newsCate['2']['title'] . '</a>
                                    </div>
                                </div>
                                <div class="clear"></div>
                                <ul>
                                    <li><a href="' . Url::link_detail($newsCate['3'], $list_category) . '" class="article-link"><i
                                            class="far fa-dot-circle"></i>' . $newsCate['3']['title'] . '</a></li>
                                    <li><a href="' . Url::link_detail($newsCate['4'], $list_category) . '" class="article-link"><i
                                            class="far fa-dot-circle"></i>' . $newsCate['4']['title'] . '</a></li>
                                    <li><a href="' . Url::link_detail($newsCate['5'], $list_category) . '" class="article-link"><i
                                            class="far fa-dot-circle"></i>' . $newsCate['5']['title'] . '</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="box-list-article">
                        <div class="item">
                            <div>
                                <div class="img">
                                    <a href="' . Url::link_detail($newsCate['6'], $list_category) . '">
                                        <img src="' . IMG::showImgFrontend($newsCate['6']) . '"alt=""/></a>
                                </div>
                            </div>
                            <div>
                                <h3 class="title">
                                    <a href="' . Url::link_detail($newsCate['6'],
                $list_category) . '">' . $newsCate['6']['title'] . '</a>
                                </h3>
                                <p>' . $newsCate['6']['description'] . '</p>
                            </div>
                        </div>
                        <div class="item">
                            <div>
                                <div class="img">
                                    <a href="' . Url::link_detail($newsCate['7'], $list_category) . '">
                                        <img src="' . IMG::showImgFrontend($newsCate['7']) . '"alt=""/></a>
                                </div>
                            </div>
                            <div>
                                <h3 class="title">
                                    <a href="' . Url::link_detail($newsCate['7'],
                $list_category) . '">' . $newsCate['7']['title'] . '</a>
                                </h3>
                                <p>' . $newsCate['7']['description'] . '</p>
                            </div>
                        </div>
                        <div class="item">
                            <div>
                                <div class="img">
                                    <a href="' . Url::link_detail($newsCate['8'], $list_category) . '">
                                        <img src="' . IMG::showImgFrontend($newsCate['8']) . '"alt=""/></a>
                                </div>
                            </div>
                            <div>
                                <h3 class="title">
                                    <a href="' . Url::link_detail($newsCate['8'],
                $list_category) . '">' . $newsCate['8']['title'] . '</a>
                                </h3>
                                <p>' . $newsCate['8']['description'] . '</p>
                            </div>
                        </div>
                        <div class="item">
                            <div>
                                <div class="img">
                                    <a href="' . Url::link_detail($newsCate['9'], $list_category) . '">
                                        <img src="' . IMG::showImgFrontend($newsCate['9']) . '" alt=""/></a>
                                </div>
                            </div>
                            <div>
                                <h3 class="title">
                                    <a href="' . Url::link_detail($newsCate['9'],
                $list_category) . '">' . $newsCate['9']['title'] . '</a>
                                </h3>
                                <p>' . $newsCate['9']['description'] . '</p>
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="right right358">
                <a href="">
                    <img class="img" src="https://giaophanthaibinh.org/upload/images/banner/SUY%20NI%E1%BB%86M.png" alt=""/></a>
                <div class="sticky sticky358">
                    <div class="container-new-hot">
                        <h4 class="title-c">
                            <i class="fas fa-fire"></i>Các tin khác
                        </h4>
                        <div class="article-list">
                            <ul>

                                <li>
                                    <a href="' . Url::link_detail($newsCate['10'], $list_category) . '"
                                       title="">
                                        <img src="' . IMG::showImgFrontend($newsCate['10']) . '"
                                             alt=""/>
                                    </a>
                                    <p>
                                        <a href="' . Url::link_detail($newsCate['10'], $list_category) . '"
                                           title="">' . $newsCate['10']['title'] . '</a>
                                    </p>
                                    <div class="clear"></div>
                                </li>
                                <li>
                                    <a href="' . Url::link_detail($newsCate['11'], $list_category) . '"
                                       title="">
                                        <img src="' . IMG::showImgFrontend($newsCate['11']) . '"
                                             alt=""/>
                                    </a>
                                    <p>
                                        <a href="' . Url::link_detail($newsCate['11'], $list_category) . '"
                                           title="">' . $newsCate['10']['title'] . '</a>
                                    </p>
                                    <div class="clear"></div>
                                </li>
                                <li>
                                    <a href="' . Url::link_detail($newsCate['12'], $list_category) . '"
                                       title="">
                                        <img src="' . IMG::showImgFrontend($newsCate['12']) . '"
                                             alt=""/>
                                    </a>
                                    <p>
                                        <a href="' . Url::link_detail($newsCate['12'], $list_category) . '"
                                           title="">' . $newsCate['12']['title'] . '</a>
                                    </p>
                                    <div class="clear"></div>
                                </li>
                                <li>
                                    <a href="' . Url::link_detail($newsCate['13'], $list_category) . '"
                                       title="">
                                        <img src="' . IMG::showImgFrontend($newsCate['13']) . '"
                                             alt=""/>
                                    </a>
                                    <p>
                                        <a href="' . Url::link_detail($newsCate['13'], $list_category) . '"
                                           title="">' . $newsCate['13']['title'] . '</a>
                                    </p>
                                    <div class="clear"></div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        return $cateData;
    }

    public function showListCateChild($cate_id)
    {
        global $list_category;
        $txt_list_cate_child = '';
        $array_cate_child = array();

        foreach ($list_category as $_tmp) {
            if ($_tmp['cate_id1'] == $cate_id && (($_tmp['property'] & 1) == 1)) {
                $array_cate_child[] = $_tmp;
            }
        }

        foreach ($array_cate_child as $_tmp) {
            $txt_list_cate_child .= '<li class="brand-item"><a class="brand-link" title="' . $_tmp['name'] . '" href="/' . $_tmp['alias'] . '/">' . $_tmp['name'] . '</a></li>';
        }

        $child = '';
        $child .= '<div class="container-title"><h2 class="title"><a href="' . $list_category[$cate_id]['alias'] . '">' . $list_category[$cate_id]['name'] . '</a></h2><ul class="brand-list">';
        $child .= $txt_list_cate_child;
        $child .= '</ul></div>';

        return $child;
    }

    public function getNewsCate($cate_id, $frontendObj)
    {
        $catNewsNew = $frontendObj->getNewsHome('*',
            '(cate_id = "' . $cate_id . '" OR cate_path LIKE "%' . $cate_id . '%")',
            8);

        return $catNewsNew;
    }

}