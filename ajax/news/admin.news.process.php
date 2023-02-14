<?php
//ini_set("display_errors", 1);
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/includes/category_model.php';
require_once UTILS_PATH . 'convert.php';
$action = SystemIO::post('action', 'def', 1);
if ($action == 1) return false;
$news_id = SystemIO::post('nw_id', 'int', 0);
$user_info = UserCurrent::$current->data;
$newsObj = new BackendNews();
$list_news_cache = array();
$news_id = SystemIO::post('nw_id', 'str', "");
$news_list_id = trim(SystemIO::post('list_news_id', 'str', ""), ",");
$news_review_id = SystemIO::post('review_id', 'str', "");

function pushTA($nw_id)
{
    $newsObj = new BackendNews();
    $row = $newsObj->getStoreOne($nw_id);
    //if($row['cate_id'] == 355 || $row['cate_id'] == 304 || $row['cate_id'] == 305 || $row['cate_id'] == 306 || $row['cate_id'] == 284 || $row['cate_id'] == 364 ||  $row['cate_id'] == 374)
    if ($row['cate_id'] == 355) {
        $res1 = @file_get_contents('http://toaanhanam.gov.vn/ajax.php?fnc=getnewscly&path=news&nw_id=' . $nw_id . '&cate_id=' . $row['cate_id']);
        $res2 = @file_get_contents('http://toaanlamdong.gov.vn/ajax.php?fnc=getnewscly&path=news&nw_id=' . $nw_id . '&cate_id=' . $row['cate_id']);
        $res3 = @file_get_contents('http://tandbacninh.gov.vn/ajax.php?fnc=getnewscly&path=news&nw_id=' . $nw_id . '&cate_id=' . $row['cate_id']);
    }

    if ($row['cate_id'] == 304 || $row['cate_id'] == 305 || $row['cate_id'] == 306 || $row['cate_id'] == 284 || $row['cate_id'] == 364 || $row['cate_id'] == 374) {
        $res1 = @file_get_contents('http://toaanhanam.gov.vn/ajax.php?fnc=getnewscly&path=news&nw_id=' . $nw_id . '&cate_id=' . $row['cate_id']);
    }
}

switch ($action) {

    case 'public_ta':

        $nw_id = SystemIO::post('nw_id');
        $res1 = @file_get_contents('http://toaanhanam.gov.vn/ajax.php?fnc=getnewscly&path=news&nw_id=' . $nw_id);
        $res2 = @file_get_contents('http://toaanlamdong.gov.vn/ajax.php?fnc=getnewscly&path=news&nw_id=' . $nw_id);
        $res3 = @file_get_contents('http://tandbacninh.gov.vn/ajax.php?fnc=getnewscly&path=news&nw_id=' . $nw_id);
        echo 1;
        break;
    case 'set-read-most':
        if ($news_id) {
            if ($newsObj->updateData('store', array('is_read_most' => 1), 'id=' . $news_id)) {
                echo 1;
            }
            else
                echo 0;
        }
        break;
    case 'news-refresh':
        if ($news_id) {
            if ($newsObj->updateData('store', array('time_public' => time()), 'id=' . $news_id)) {
                $newsObj->updateData('store_home', array('time_public' => time()), 'nw_id=' . $news_id);
                echo 1;
            } else echo 0;
        }
        break;
    case 'set-time_public':
        $date = SystemIO::post('date', 'def');
        $time_public = (int)strtotime(str_replace('/', '-', $date));
        if ($news_id && $time_public > 0) {
            if ($newsObj->updateData('store', array('time_public' => $time_public), 'id=' . $news_id)) {
                $newsObj->updateData('store_home', array('time_public' => $time_public), 'nw_id=' . $news_id);
                $newsObj->updateData('search', array('time_public' => $time_public), 'nw_id=' . $news_id);
                echo 1;
            } else echo 0;
        }
        break;
    case 'delete':
        if ($newsObj->deleteHome($news_id)) {
            echo 1;
        } else
            echo 0;
        break;
    case 'post_news':
        $post_id = SystemIO::post('nw_id', 'int', 0);
        if ($newsObj->updateReview($post_id, array('status' => '0','date_time_push_pendding'=> date('Y-m-d H:i:s')))) {
            echo 1;
        } else
            echo 0;
        break;
    case 'search_relate':
        $keyword = SystemIO::post('keyword', 'str');
        //$keyword=str_replace(array('%',"'"),array(' '," "),$keyword);
        $keyword = trim($keyword, ';');
        $arr = explode(";", $keyword);
        $sql_match = "";
        $leng = count($arr);
        if ($leng > 0) {
            $q = str_replace(array('<', '>', '/', '"', "'", '“', '”'), array('&lt', '&gt', '', '', '', '', ''), trim($arr[0]));
            $q = mysql_escape_string($q);

            $convert = new Convert();
            $q = $convert->convertUtf8ToTelex($q);

            #$value = str_replace(" ", " +", $q);
            $value = $q;
            #$sql_match .= "(MATCH(keyword) AGAINST('+$value' IN BOOLEAN MODE) AND keyword like '%$q%')";
            $sql_match .= "(MATCH(keyword) AGAINST('$value' IN BOOLEAN MODE) AND keyword LIKE '%$q%')";
            for ($i = 1; $i < $leng; $i++) {
                $q = str_replace(array('<', '>', '/', '"', "'", '“', '”'), array('&lt', '&gt', '', '', '', '', ''), trim($arr[$i]));
                $q = mysql_escape_string($q);

                $q = $convert->convertUtf8ToTelex($q);

                $value = str_replace(" ", " +", $q);
                $sql_match .= " AND (MATCH(keyword) AGAINST('+$value' IN BOOLEAN MODE) AND keyword like '%$q%')";
            }
        }

        if ($sql_match != "") {
            $list_ralate = $newsObj->searchFullTextNews($sql_match, "", "0,20");
            if (count($list_ralate)) {
                foreach ($list_ralate as $row) {
                    if ($row['time_public'] < time() && $row['time_public'] > 0)
                        echo '<li style="margin-left:150px;" id="' . $row['id'] . '" class="no-choose"><input type="checkbox" value="' . $row['id'] . '" name="relate[]" onclick="setRelate(this,' . $row['id'] . ');">' . $row['title'] . '</li>';
                }
            } else
                echo '<li style="margin-left:150px;" class="no-choose"> Không có tin liên quan tới từ khóa: <b>' . $keyword . '</b></li>';
        } else
            echo '<li style="margin-left:150px;" class="no-choose"> Chưa nhập từ khóa tìm kiếm</li>';
        break;
    case 'delete-old-home':
        $number = SystemIO::post('number', 'int', 0);
        $list_home_old = $newsObj->getListHome('', 'time_public ASC', $number);
        $list_news_ids = '';
        foreach ($list_home_old as $_temp) {
            $list_news_ids .= $_temp['nw_id'] . ',';
        }
        $list_news_ids = rtrim($list_news_ids, ',');
        if ($newsObj->deleteMultiHome('nw_id IN (' . $list_news_ids . ')', $number)) {
            echo 1;
            $newsObj->updateData('store', array('type' => 0), 'id IN (' . $list_news_ids . ')');
        } else
            echo 0;
        break;
    case 'set-property'    :
        $set_property = SystemIO::post('set_property', 'int');
        $unset_property = SystemIO::post('unset_property', 'int');
        $news_id = SystemIO::post('nw_id', 'int');
        if ($newsObj->setProperty('store_home', "nw_id={$news_id}", $set_property, $unset_property))
            echo 1;
        else
            echo 0;
        break;
    case 'set-multi-property':
        $set_property = SystemIO::post('set_property', 'int');
        $unset_property = SystemIO::post('unset_property', 'int');
        $news_ids = SystemIO::post('list_news_id', 'def');
        if (!$news_ids) {
            echo 0;
            exit();
        }
        if ($newsObj->setProperty('store_home', "nw_id IN ({$news_ids})", $set_property, $unset_property))
            echo 1;
        else
            echo 0;
        break;
    case 'public':
        $news_id = SystemIO::post('nw_id', 'int');
        if ($newsObj->updateData('store', array('time_public' => time(), 'censor_id' => $user_info['id']), 'id=' . $news_id)) {
            pushTA($news_id);
            echo 1;
        } else
            echo 0;
        break;
    case 'public-set-property':
        $set_property = SystemIO::post('set_property', 'int');
        $news_id = SystemIO::post('nw_id', 'int');
        $row = $newsObj->getStoreOne($news_id);
        $time_public = ($row['time_public'] > time()) ? $row['time_public'] : time(); // hen h tren trang chu
        //$time_public = $row['time_public'];
        //+1 de no luon luon thay doi d up date
        if ($newsObj->updateData('store', array('time_public' => $time_public + 1, 'censor_id' => $user_info['id']), 'id=' . $news_id)) {
            pushTA($news_id);
            if ($newsObj->insertHomeToStore($news_id, $user_info['id'], $set_property)) {
                echo 1;
            } else
                echo 0;
        } else {
            echo 0;
        }
        break;
    case 'set-property-from-store':
        $set_property = SystemIO::post('set_property', 'int');
        $news_id = SystemIO::post('nw_id', 'int');
        $row = $newsObj->getStoreOne($news_id);
        $time_public = ($row['time_public'] == 0) ? time() : $row['time_public'];
        //+1 de no luon luon thay doi d up date
        if ($newsObj->updateData('store', array('time_public' => $time_public + 1, 'censor_id' => $user_info['id']), 'id=' . $news_id)) {
            //insert tin tu bang store sang bang home
            if ($newsObj->insertHomeToStore($news_id, $user_info['id'], $set_property)) {
                echo 1;
            } else
                echo 0;
        } else {
            echo 0;
        }
        break;
    case 'public-set-multi-property':
        $set_property = SystemIO::post('set_property', 'int');
        $public = SystemIO::post('public', 'int');
        if ($set_property == 0 && $public == 0) {
            echo 0;
            exit();
        }
        $news_ids = SystemIO::post('list_news_id', 'def');
        if ($news_ids) {
            if ($newsObj->updateData('store', array('time_public' => time()), "id IN ({$news_ids})")) {
                if ($set_property) {
                    $j = 0;
                    $_arr_news_id = explode(',', $news_ids);
                    for ($i = 0; $i < count($_arr_news_id); ++$i) {
                        if ($newsObj->insertHomeToStore($_arr_news_id[$i], $user_info['id'], $set_property)) ++$j;
                    }
                    if ($j == count($news_ids)) echo 1;
                    else echo 0;
                } else
                    echo 1;

            } else
                echo 0;
        }
        break;
    /*Duyet tin*/
    case 'do-censor':
        $review_id = SystemIO::post('review_id', 'def', '');
        $public = SystemIO::post('public', 'int', 0);
        if ($public == 1) {
            $time = time();
        } else {
            $time = 0;
        }
        $result = $newsObj->insertReviewToStore($review_id, $user_info['id'], $time, 0);
        if ($result['store_id'] && $result['search_id']) {
            //pushTA($result['store_id']);
            echo 1;
        } else
            echo 0;
        break;
    /*Duyet tin va set thuoc tinh*/
    case 'censor-set-property':
        $set_property = SystemIO::post('set_property', 'int');
        $news_id = SystemIO::post('nw_id', 'int');
        if ($res = $newsObj->insertReviewToHome($news_id, $user_info['id'], time(), 1, $set_property)) {
            //pushTA($res['store_id']);
            echo 1;
        } else
            echo 0;

        break;
    /*Duyet tin va set nhieu thuoc tinh*/
    case 'censor-set-multi-property':
        $set_property = SystemIO::post('set_property', 'int', 0);
        $public = SystemIO::post('public', 'int');
        $censor = SystemIO::post('censor', 'int');
        if ($set_property == 0 && $public == 0 && $censor == 0) {
            echo 0;
            exit();
        }
        $news_ids = SystemIO::post('list_news_id', 'def');
        if ($news_ids) {
            if ($set_property) {
                $j = 0;
                $_arr_news_id = explode(',', $news_ids);
                for ($i = 0; $i < count($_arr_news_id); ++$i) {
                    if ($newsObj->insertReviewToHome($_arr_news_id[$i], $user_info['id'], time(), 1, $set_property)) ++$j;
                }
                if (count($_arr_news_id) == $j) echo 1;
                else echo 0;
            } elseif ($public && ($set_property == 0)) {
                $j = 0;
                $_arr_news_id = explode(',', $news_ids);
                for ($i = 0; $i < count($_arr_news_id); ++$i) {
                    $result = $newsObj->insertReviewToStore($_arr_news_id[$i], $user_info['id'], time(), 0);
                    if ($result['store_id'] && $result['view_id'] && $result['search_id'] && $result['hit_id'] && $result['content_id']) {
                        ++$j;
                    }
                }
                if ($j == count($_arr_news_id)) echo 1;
                else echo 0;
            } else {
                $j = 0;
                $_arr_news_id = explode(',', $news_ids);
                for ($i = 0; $i < count($_arr_news_id); ++$i) {
                    $result = $newsObj->insertReviewToStore($_arr_news_id[$i], $user_info['id'], 0, 0);
                    if ($result['store_id'] && $result['view_id'] && $result['search_id'] && $result['hit_id'] && $result['content_id']) {
                        ++$j;
                    }
                }
                if ($j == count($_arr_news_id)) echo 1;
                else echo 0;
            }

        }
        break;
    case 'news-return':
        $reason = SystemIO::post('reason', 'def', '');
        $news_id = SystemIO::post('nw_id', 'int');
        if ($reason) {
            if ($newsObj->updateData('review', array('reason_return' => $reason, 'status' => 1,'date_time_return'=>date('Y-m-d H:i:s')), 'id=' . $news_id)) {
                echo 1;
            } else echo 0;
        } else echo 0;
        break;
    case 'news-return-to-store':
        $reason = SystemIO::post('reason', 'def', '');
        $news_id = SystemIO::post('nw_id', 'int');
        if ($reason)
            if ($newsObj->convertStoreToReview($news_id, $reason))
                echo 1;
            else
                echo 0;
        else
            echo 0;
        break;
    case 'news-return-to-store-censor':
        $reason = SystemIO::post('reason', 'def', '');
        $news_id = SystemIO::post('nw_id', 'int');
        if ($reason) {
            if ($newsObj->convertStoreToReview($news_id, $reason, 0))
                echo 1;
            else
                echo 0;
        } else {
            echo 0;
        }
        break;
    case 'delete-review' :
        $news_id = SystemIO::post('nw_id', 'int');
        if ($newsObj->delReview($news_id))
            echo 1;
        else
            echo 0;
        break;
    case 'view-content-to-store':
        $news_id = SystemIO::post('nw_id', 'int', '');
        $row = $newsObj->getStoreOne($news_id);
        $row['content'] = $newsObj->getContentOne($news_id);
        echo '<div style="float:left; width:785px;">
		<a href="javascript;">' . $row['title'] . '</a>
		<table style="float:left;">
				<tr valign="bottom">
					<td>
						<img  src="' . IMG::show($newsObj->getPathNews($row['time_created']), $row['img1']) . '" width="100px" style="float:left"/>&nbsp;
					</td>
					<td>
						<img  src="' . IMG::show($newsObj->getPathNews($row['time_created']), $row['img2']) . '" width="100px" style="float:left"/>&nbsp;
					</td>
					<td>
						<img  src="' . IMG::show($newsObj->getPathNews($row['time_created']), $row['img3']) . '" width="100px" style="float:left"/>&nbsp;
					</td>

				</tr>
			</table>
			<p style="margin-left:5px;font-weight:bold;">' . $row['description'] . '</p>
		</div>
		<div style="clear:both; margin:5px 0px 0px 0px;">' . $row['content'] . '</div>
		<span style="float:right;"><i>Nguồn:' . $row['origin'] . '</i>&nbsp;</span>';
        break;
    case 'view-content-to-review':
        $news_id = SystemIO::post('nw_id', 'int', '');
        $row = $newsObj->getReviewOne($news_id);
        echo '<div style="float:left; width:785px;">
			<a href="javascript;">' . $row['title'] . '</a>
			<table style="float:left;">
				<tr valign="bottom">
					<td>
						<img  src="' . IMG::show(NEWS_IMG_URL, $row['img1']) . '" width="100px" style="float:left"/>&nbsp;
					</td>
					<td>
						<img  src="' . IMG::show(NEWS_IMG_URL, $row['img2']) . '" width="100px" style="float:left"/>&nbsp;
					</td>
					<td>
						<img  src="' . IMG::show(NEWS_IMG_URL, $row['img3']) . '" width="100px" style="float:left"/>&nbsp;
					</td>

				</tr>
			</table>
			<p style="margin-left:5px;font-weight:bold;">' . $row['description'] . '</p>
		</div>
		<div style="clear:both; margin:5px 0px 0px 0px;">' . $row['content'] . '</div>
		<span style="float:right;"><i>Nguồn:' . ($row['origin'] ? $row['origin'] : 'N/A') . '</i>&nbsp;</span>';
        break;
    case 'map-news-region':
        $list_news_id = SystemIO::post('list_news_id', 'def', '');
        $list_region_id = SystemIO::post('list_region_id', 'def', '');
        $arr_news = explode(',', $list_news_id);
        $arr_region = explode(',', $list_region_id);

        if ($newsObj->countRecord('region_store', "nw_id IN ({$list_news_id}) AND region_id IN ({$list_region_id})", 'nw_id')) {
            echo 0;
            exit();
        }
        if ($newsObj->mapNewsRegion($arr_news, $arr_region))
            echo 1;
        else
            echo 0;
        break;
    case 'del-map-news-region':
        $news_id = SystemIO::post('nw_id', 'int');
        $region_id = SystemIO::post('region_id', 'int');
        if ($newsObj->delData('region_store', "nw_id={$news_id} AND region_id={$region_id}"))
            echo 1;
        else
            echo 0;
        break;
    case 'save-arrange':
        $list_home_id = SystemIO::post('list_home_id', 'def');
        $list_arrange = SystemIO::post('list_arrange', 'def');
        $arr_home_id = explode(',', $list_home_id);
        $arr_arrange = explode(',', $list_arrange);
        for ($i = 0; $i < count($arr_home_id); ++$i) {
            $newsObj->updateData('store_home', array('arrange' => $arr_arrange[$i]), 'id=' . $arr_home_id[$i]);
        }
        echo 1;
        break;
    case 'page_news_region':
        require_once UTILS_PATH . 'pagination.php';
        $pageObj = new Pagination();
        $item_per_page = 20;
        $pageObj->per_page = $item_per_page;
        $page_no = SystemIO::get('page_no', 'int', 1);
        $q = SystemIO::get('q', 'def', '');
        $cate_id = SystemIO::get('cate_id', 'int', 0);
        $q = trim(str_replace(array('"', "'", '%'), array('', '', ''), $q), ' ');
        if ($page_no < 1) $page_no = 1;
        $stt = ($page_no - 1) * $item_per_page + 1;
        $limit = (($page_no - 1) * $item_per_page) . ',' . $item_per_page;
        $wh = '1=1';
        if ($q) $wh .= " AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
        if ($cate_id)
            $wh .= " AND cate_path LIKE '%,{$cate_id},%'";
        $list_news = $newsObj->getListStore($wh, 'time_public DESC', $limit);
        $news_ids = '';
        $cate_ids = '';
        foreach ($list_news as $_temp) {
            $cate_ids .= trim($_temp['cate_path'], ',') . ',';
        }
        $cate_ids = trim($cate_ids, ',');
        $list_path_news = $newsObj->getMultiPathNews($cate_ids);
        $str = '<tbody>
				<tr align="center" class="table-title">
				  <td width="3%" class="bdbottom bdleft">STT</td>
				  <td width="45%" class="bdbottom bdleft" align="left">&nbsp;Danh sách các bài viết</td>
				</tr>';
        foreach ($list_news as $row) {
            ++$stt;
            $str .= '<tr>
			  <td class="bdleft" align="center">' . $stt . '<br/><input  type="checkbox" id="check_news" name="check_news" value="' . $row['id'] . '"/></td>
			  <td class="bdleft">
				<p><strong><a href="?portal=news&page=detail&id=' . $row['id'] . '&title=' . $row['title'] . '" target="_blank">' . $row['title'] . '</a></strong></p>
				<p style="color:#993300">Trong mục: ' . $list_path_news[$row['cate_id']] . '</p>
				<p>' . $row['description'] . '</p>
				<p><i>Tag: ' . $row['tag'] . '</i></p>
			  </td>
			</tr>';
        }
        $str .= '</tbody>';
        global $TOTAL_ROWCOUNT;
        $pageObj->total = $TOTAL_ROWCOUNT;
        $pageObj->portal = "news";
        $pageObj->pagename = "admin.news.process";
        $pageObj->page = $page_no - 1;
        $ajax_page = '';
        if ($q) {
            $ajax_page .= "&q={$q}";
        }
        if ($cate_id)
            $ajax_page .= "&cate_id={$cate_id}";
        echo json_encode(array("text" => $str, "paging" => $pageObj->create1_ajax($ajax_page)));
        break;
    case 'delete-from-store':
        $id = SystemIO::post('nw_id', 'int', 0);
        if ($newsObj->deleteStore($id))
            echo 1;
        else
            echo 0;
        break;
    case 'set-home-from-store':
        $id = SystemIO::post('nw_id', 'int', 0);
        $property = SystemIO::post('property', 'int', 0);
        $check_home = $newsObj->countRecord('store_home', 'nw_id=' . $id);
        if ($check_home) {
            if ($newsObj->setProperty('store_home', "nw_id={$id}", $property, 0))
                echo 1;
            else
                echo 0;
        } else {
            if ($newsObj->setHomeFromStore($id, $property))
                echo 1;
            else
                echo 0;
        }
        break;
    case 'load-region':
        $cate_id = SystemIO::post('cate_id', 'int', 0);
        $list_category = $newsObj->getListCategory("cate_id1={$cate_id}", '', '0,200', 'id');
        $cate_ids = $cate_id;
        if (count($list_category)) {
            foreach ($list_category as $_temp) {
                $cate_ids .= ',' . $_temp['id'];
            }
        }


        $list_region = $newsObj->getListRegionCate('cate_id IN (' . $cate_ids . ')');
        $region_ids = '';
        if (count($list_region)) {
            foreach ($list_region as $_temp) {
                $region_ids .= $_temp['region_id'] . ',';

            }
        }
        $region_ids = rtrim($region_ids, ',');
        if ($region_ids) {
            $list_region = $newsObj->getListRegion("id IN ({$region_ids})");
            echo SystemIO::getOption(SystemIO::arrayToOption($list_region, 'id', 'name'), '');
        } else
            echo '<option>Không có vùng</option>';
        break;
    case 'load-region-map':
        $cate_id = SystemIO::post('cate_id', 'int', 0);

        $wh = "id={$cate_id} OR cate_id1={$cate_id}";
        $list_category = $newsObj->getListCategory($wh, '', '0,200', 'id');
        $category_ids = '';
        if (count($list_category)) {
            foreach ($list_category as $_temp) {
                $category_ids .= $_temp['id'] . ',';
            }
        }
        $category_ids = rtrim($category_ids, ',');
        if ($category_ids)
            $list_region = $newsObj->getListRegionCate("cate_id IN ($category_ids)");
        $region_ids = '';
        $arr_cate_region = array();
        if (count($list_region)) {
            foreach ($list_region as $_temp) {
                $region_ids .= $_temp['region_id'] . ',';
                $arr_cate_region[$_temp['region_id']] = $_temp['cate_id'];

            }
        }

        $region_ids = rtrim($region_ids, ',');
        if ($region_ids) {
            $list_region = $newsObj->getListRegion("id IN ({$region_ids})");
            echo ' <table cellspacing="0" cellpadding="0" border="0" >
			<tbody>
				<tr align="center" class="table-title">
				  <td width="3%" class="bdbottom bdleft">STT</td>
				  <td width="45%" class="bdbottom bdleft" align="left">&nbsp;Danh sách các vùng</td>
				</tr>';
            $i = 0;
            foreach ($list_region as $row) {
                ++$i;
                echo '<tr>
					<td class="bdleft" align="center">' . $i . '<br/><input  type="checkbox" id="check_region" name="check_region" value="' . $row['id'] . '"/></td>
					<td class="bdleft">
						<p><strong><a href="javascript:;">' . $row['name'] . '</a></strong></p>
						<p style="color:#993300">Trong mục: ' . $list_category[$arr_cate_region[$row['id']]]['name'] . '</p>
						<p>' . $row['desciption'] . '</p>
					</td>
				</tr>';
            }

            echo '
			</tbody>
	  </table>';
        } else
            echo '<option>Không có vùng</option>';
        break;
    case 'load-category-map':
        $cate_id = SystemIO::post('cate_id', 'int', 0);
        $list_category = $newsObj->getListCategory('cate_id1=' . $cate_id . ' AND cate_id2=0 AND property&1 =1', '', 100, 'id');
        echo '<option value="0">Chọn danh mục</option>' . SystemIO::getOption(SystemIO::arrayToOption($list_category, 'id', 'name'), $cate_id);
        break;
    case 'choose_topic':
        $topic_id = SystemIO::post('topic_id', 'int', 0);
        $news_id = SystemIO::post('nw_id', 'int', 0);
        if ($news_id)
            if ($newsObj->updateData('store', array('topic_id' => $topic_id), 'id=' . $news_id))
                echo 1;
            else
                echo 0;
        else
            echo 0;
        break;
}

