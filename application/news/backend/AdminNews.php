<?php

if (defined(IN_JOC)) {
    die("Direct access not allowed!");
}
ini_set("display_errors", 0);
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/news/includes/property_news.php';
require_once 'application/main/includes/user.php';
require_once UTILS_PATH . 'paging.php';
require_once UTILS_PATH . 'image.upload.php';

class AdminNews extends Form
{

    function __construct()
    {
        Form::__construct($this);
    }

    function addLogoIntoImage($logo_file, $image_file, $position = 2, $image_file_have_logo = null)
    {
        if ($image_file_have_logo === null) {
            $image_file_have_logo = $image_file;
        }
        $photo = imagecreatefromjpeg($image_file);
        $fotoW = imagesx($photo);
        $fotoH = imagesy($photo);
        $logoImage = imagecreatefrompng($logo_file);
        $logoW = imagesx($logoImage);
        $logoH = imagesy($logoImage);
        $photoFrame = imagecreatetruecolor($fotoW, $fotoH);
        $dest_x = $fotoW - $logoW;
        $dest_y = $fotoH - $logoH;
        imagecopyresampled($photoFrame, $photo, 0, 0, 0, 0, $fotoW, $fotoH, $fotoW, $fotoH);
        if ($position == 3) {
            imagecopy($photoFrame, $logoImage, $dest_x / 2, 2 * $dest_y / 3, 0, 0, $logoW, $logoH);
        } else {
            imagecopy($photoFrame, $logoImage, $dest_x / $position, $dest_y / $position, 0, 0, $logoW, $logoH);
        }
        imagejpeg($photoFrame, $image_file_have_logo, 95);
    }

    function on_submit()
    {
        if (!UserCurrent::isLogin()) {
            echo 'Bạn chưa Login!';
            die;
        }
        require_once UTILS_PATH . 'image.resize.php';
        $user_info = UserCurrent::$current->data;
        $newsObj = new BackendNews();
        $newsObj->delData('autosave', 'user_id=' . $user_info['id'], '1000');
        $news_id = SystemIO::post('news_id', 'int', 0);
        $memcache = new Memcache();
        $memcache->addServer('localhost', 11211);
        $key_memcache = 'active_reading';
        if ($news_id) {
            $memcache->delete($key_memcache . $news_id);
        }

        $IMG_MAX_SIZE = 4806400;
        $from = SystemIO::post('from', 'def', 'review');
        if (SystemIO::post('add_logo', 'int', 0)) {
            $action_add_logo = true;
        } else {
            $action_add_logo = false;
        }

        $img1 = '';
        $img3 = '';
        if ($news_id) {
            if ($from == 'review') {
                $row = $newsObj->getReviewOne($news_id);
            } elseif ($from == 'store') {
                $row = $newsObj->getStoreOne($news_id);
                $row['content'] = $newsObj->getContentOne($news_id);
            }
        }

        if (!$row['id']) {
            // tạo mới
            $path_img_upload = NEWS_IMG_UPLOAD . date('Y/n/j', time());
        } else {
            $path_img_upload = NEWS_IMG_UPLOAD . date('Y/n/j', $row['time_created']);
        }

        if ($news_id == 0) {
            $time_created = time();
        } else {
            $time_created = $row['time_created'];
        }

        if ($_FILES['img1']['name']) {
            $uploader = new Uploader();
            $uploader->setPath($path_img_upload);
            $uploader->setMaxSize(5000000);
            $uploader->setFileType('custom', array('jpg', 'jpeg', 'png', 'web', 'gif', 'bmp'));
            $result = $uploader->doUpload('img1');
            $arr_info_image = getimagesize($result['path'] . $result['name']);
            $img1 = (string)$result['name'];
        }
        if ($_FILES['img3']['name']) {
            $uploader = new Uploader();
            $uploader->setPath($path_img_upload);
            $uploader->setMaxSize(5000000);
            $uploader->setFileType('custom', array('jpg', 'jpeg', 'png', 'web', 'gif', 'bmp'));
            $result = $uploader->doUpload('img3');
            $img3 = (string)$result['name'];
            $time_created = time(); // thay doi tiem created
        }

        $arr_file = $_POST['file_old'];
        if ($_FILES['file1']['name']) {
            $path_upload_file = ROOT_PATH . 'data/file';
            $uploader = new Uploader();
            $uploader->setPath($path_upload_file);
            $uploader->setMaxSize(200000);
            $uploader->setFileType('custom', array('pdf', 'doc', 'docx', 'xlsx', 'xls', 'jpg', 'txt', 'gif'));
            $result = $uploader->doUpload('file1');
            $arr_file['1'] = (string)$result['name'];
        }

        if ($_FILES['file2']['name']) {
            $path_upload_file = ROOT_PATH . 'data/file';
            $uploader = new Uploader();
            $uploader->setPath($path_upload_file);
            $uploader->setMaxSize(200000);
            $uploader->setFileType('custom', array('pdf', 'doc', 'docx', 'xlsx', 'xls', 'jpg', 'txt', 'gif'));
            $result = $uploader->doUpload('file2');
            $arr_file['2'] = (string)$result['name'];
        }
        if ($_FILES['file3']['name']) {
            $path_upload_file = ROOT_PATH . 'data/file';
            $uploader = new Uploader();
            $uploader->setPath($path_upload_file);
            $uploader->setMaxSize(200000);
            $uploader->setFileType('custom', array('pdf', 'doc', 'docx', 'xlsx', 'xls', 'jpg', 'txt', 'gif'));
            $result = $uploader->doUpload('file3');
            $arr_info_image = getimagesize($result['path'] . $result['name']);
            if ($arr_info_image['0'] > $arr_info_image['1'] * 0.8) {
                $result['name'] = '';
            }

            $arr_file['3'] = (string)$result['name'];
        }
        if ($_FILES['file4']['name']) {
            $path_upload_file = ROOT_PATH . 'data/file';
            $uploader = new Uploader();
            $uploader->setPath($path_upload_file);
            $uploader->setMaxSize(200000);
            $uploader->setFileType('custom', array('pdf', 'doc', 'docx', 'xlsx', 'xls', 'jpg', 'txt', 'gif'));
            $result = $uploader->doUpload('file4');
            $arr_file['4'] = (string)$result['name'];
        }
        if ($_FILES['file5']['name']) {
            $path_upload_file = ROOT_PATH . 'data/file';
            $uploader = new Uploader();
            $uploader->setPath($path_upload_file);
            $uploader->setMaxSize(200000);
            $uploader->setFileType('custom', array('pdf', 'doc', 'docx', 'xlsx', 'xls', 'jpg', 'txt', 'gif'));
            $result = $uploader->doUpload('file5');
            $arr_file['5'] = (string)$result['name'];
        }

        $file = implode(',', $arr_file);

        $title = SystemIO::post('title', 'def');
        $description = SystemIO::post('description', 'def');
        $tag = implode(',', SystemIO::post('tag', 'def'));

        $arr_relate = @$_POST['relate'];
        $list_news_ids = '';
        for ($i = 0; $i < count($arr_relate); ++$i) {
            $list_news_ids .= $arr_relate[$i] . ',';
        }
        $list_news_ids = rtrim($list_news_ids, ',');
        $author = SystemIO::post('author', 'def');
        $origin = SystemIO::post('origin', 'def');
        $content = SystemIO::post('content', 'def', 'Content');
        $is_video = SystemIO::post('is_video', 'int', 0);
        $is_img = SystemIO::post('is_img', 'int', 0);
        $province_id = SystemIO::post('province_id', 'int');
        $topic_id = SystemIO::post('topic_id', 'int', 0);
        /* Dat lich public */
        $hour_public = SystemIO::post('hour_public', 'int', 0);
        $date_public = SystemIO::post('date_public', 'def', '');
        $minutes = SystemIO::post('minutes', 'int', '0');
        $time_public = 0;
        if ($date_public) {
            if ($minutes > 60 || $minutes < 0) {
                $minutes = '00';
            }
            $str_time = $hour_public . ':' . $minutes . ' ' . str_replace('/', '-', $date_public);
            $time_public = strtotime($str_time);
        }

        $user_id = $user_info['id'];
        $arr_cate_id = SystemIO::post('data', 'arr');


        $cate_path = ',';
        $cate_other = '';
        foreach ($arr_cate_id as $cate_ids) {
            if ((int)$cate_ids && is_numeric($cate_ids)) {
                $cate_path .= $cate_ids . ',';
            } elseif (is_array($cate_ids)) {
                $cate_path .= $cate_ids['0'] . ',';
                for ($n = 1; $n < count($cate_ids); ++$n) {
                    $cate_other .= $cate_ids[$n] . ',';
                }
            }
        }
        $cate_end_id = end($arr_cate_id);
        $cate_id = $cate_end_id['0'];

        if ($cate_other) {
            $cate_other = ',' . $cate_other;
        }
        $content = stripcslashes($content);
        $partern = '/src=\"([^\"]*)\"/';

        preg_match_all($partern, $content, $m);

        $images = $m[1];
        $leng = count($images);
        if ($leng > 0) {
            for ($i = 0; $i < $leng; $i++) {
                $info = pathinfo($images[$i]);
                if ($info['extension'] == 'mp4') {
                    continue;
                }
                if (strpos($images[$i], "data/news/") === false || strpos($images[$i], "data/news/") != 0) {
                    if (strpos($images[$i], "youtube")) {
                        continue;
                    }
                    $text = @file_get_contents(str_replace(" ", "%20", $images[$i]));
                    $file_size = (int)@filesize(str_replace('http://cms.congly.com.vn/', '', $images[$i]));
                    if ($file_size > $IMG_MAX_SIZE) {
                        if ($from != "store") {
                            $content = str_replace($images[$i], 'webskins/skins/news/images/logo_congly.png', $content);
                        }
                        continue;
                    }
                    if ($text != "") {

                        $arr = explode('/', $images[$i]);

                        $image_name = preg_replace('/[^a-zA-Z0-9\-]/', '', $arr[count($arr) - 1]);

                        //if(strpos($image_name, "jpg") === FALSE && strpos($image_name, "jpeg") === FALSE && strpos($image_name, "gif") === FALSE && strpos($image_name, "png") === FALSE)
                        if (strpos($image_name, "flv") === false) {
                            //$image_name = str_replace(array('jpg','JPG',' '),array('','','_'),$image_name);
                            if ($from == 'store') {
                                $image_name = Convert::convertLinkTitle($title) . "-hinh-anh" . $i . time() . rand($i,
                                        time()) . ".jpg";

                            } else {
                                $image_name = Convert::convertLinkTitle($title) . "-hinh-anh" . $i . rand($i,
                                        time()) . ".jpg";
                            }


                            $image_name = str_replace(array('/', '\\'), array('-', '-'), $image_name);
                        }

                        if (!is_dir(NEWS_IMG_UPLOAD . date('Y', time()) . '/' . date('n', time()) . '/' . date('j',
                                time()) . DS . $user_info['id'])) {
                            @mkdir(NEWS_IMG_UPLOAD . date('Y', time()) . '/' . date('n', time()) . '/' . date('j',
                                    time()) . DS . $user_info['id']);
                        }

                        @file_put_contents(NEWS_IMG_URL . $user_info['id'] . DS . $image_name, $text);
                        if ($action_add_logo) {
                            $this->addLogoIntoImage('webskins/skins/news/images/logo_congly.png',
                                NEWS_IMG_URL . $user_info['id'] . DS . $image_name, 1);
                        }
                        $content = str_replace($images[$i], NEWS_IMG_URL . $user_info['id'] . DS . $image_name,
                            $content);
                    }
                } else {
                    if ($action_add_logo) {
                        $arr = explode('/', $images[$i]);
                        $content_image = @file_get_contents(str_replace(" ", "%20", $images[$i]));
                        if ($content_image != '') {
                            $image_name = preg_replace('/[^a-zA-Z0-9\-]/', '', $arr[count($arr) - 1]);
                            if (strpos($image_name, 'flv') === false) {
                                $image_name = Convert::convertLinkTitle($title) . '-hinh-anh' . $i . '.jpg';
                                $image_name = str_replace(array('/', '\\'), array('-', '-'), $image_name);
                            }
                            $image_name_new = time() . $image_name;
                            @file_put_contents(NEWS_IMG_URL . $user_info['id'] . DS . $image_name, $content_image);
                            $this->addLogoIntoImage('webskins/skins/news/images/logo_congly.png',
                                NEWS_IMG_URL . $user_info['id'] . DS . $image_name, 1,
                                NEWS_IMG_URL . $user_info['id'] . DS . $image_name_new);
                            $content = str_replace($images[$i], NEWS_IMG_URL . $user_info['id'] . DS . $image_name_new,
                                $content);
                        }

                    }

                }
            }
        }

        $arrNewData = array(
            'title' => $title,
            'description' => $description,
            'is_video' => $is_video,
            'cate_id' => $cate_id,
            'cate_path' => $cate_path,
            'cate_other' => $cate_other,
            'tag' => $tag . '[]' . Convert::convertLinkTitle($tag),
            'topic_id' => $topic_id,
            'relate' => $list_news_ids,
            'author' => $author,
            'origin' => $origin,
            'content' => $content,
            'user_id' => $user_id,
            'time_created' => $time_created,
            'province_id' => (int)$province_id,
            'is_img' => $is_img,
            'time_public' => $time_public,
            'poll_id' => SystemIO::post('poll', 'int', 0),
            'type_post' => SystemIO::post('type_post', 'int', 0),
            'status' => SystemIO::post('status', 'int', 0)
        );

        if ($arrNewData['status'] != 2) {
            $arrNewData['date_time_push_pendding'] = date('Y-m-d H:i:s');
        } else {
            $arrNewData['date_time_push_pendding'] = date('Y-m-d H:i:s', $time_created);
        }

        if ($file) {
            $arrNewData['file'] = $file;
        }

        $arrNewSEO = array(
            'title_seo' => SystemIO::post('title_seo', 'def'),
            'description_seo' => SystemIO::post('description_seo', 'def'),
            'keyword_seo' => SystemIO::post('keyword_seo', 'def'),
        );
        if ($img1) {
            $arrNewData['img1'] = $img1;
            if ($action_add_logo) {
                $this->addLogoIntoImage('webskins/skins/news/images/logo_congly.png', $path_img_upload . '/' . $img1, 1,
                    null);
            }
        }
        if ($img3) {
            $arrNewData['img3'] = $img3;
            if ($action_add_logo) {
                $this->addLogoIntoImage('webskins/skins/news/images/logo_congly.png', $path_img_upload . '/' . $img1, 1,
                    null);
            }
        }

        if ($news_id) {
            if ($from == 'store') {
                $content = $arrNewData['content'];
                unset($arrNewData['content']);
                $arrNewData['user_id'] = $row['user_id'] ? $row['user_id'] : $user_id;
                if ($row['editor_id'] == $user_id) {
                    $arrNewData['editor_id'] = $user_id;
                } else {
                    $arrNewData['editor_id'] = $row['editor_id'] . ',' . $user_id;
                }

                //$arrNewData['censor_id'] = $user_id; nguoi sua khong phai la nguoi duyet

                if ($row['time_public']) {
                    if ($arrNewData['time_public'] < time()) {
                        //$arrNewData['time_public']=time();// doan nay cap nhat thoi gian khi sua bai
                        $arrNewData['time_public'] = $row['time_public']; // giua thoi gian public khi sua bai
                    } elseif ($arrNewData['time_public'] > time()) {
                        $arrNewData['time_public'] = $time_public;
                    }
                } else {
                    $arrNewData['time_public'] = $time_public;
                }

                $newsObj->updateData('store', $arrNewData, 'id=' . $news_id);
                $newsObj->updateData('store_content', array('content' => $content), 'nw_id=' . $news_id);
                $row_seo = $newsObj->readSeo('nw_id=' . $news_id);
                if (count($row_seo) > 2) {
                    $newsObj->updateData('seo', $arrNewSEO, 'nw_id=' . $news_id);
                } else {
                    $arrNewSEO['nw_id'] = $news_id;
                    $newsObj->insertSeo($arrNewSEO);
                }
                unset($arrNewData['author']);
                unset($arrNewData['origin']);
                unset($arrNewData['user_id']);
                unset($arrNewData['status']);
                unset($arrNewData['editor_id']);
                unset($arrNewData['censor_id']);
                unset($arrNewData['type_post']);
                unset($arrNewData['province_id']);
                unset($arrNewData['date_time_push_pendding']);

                if ($row['type'] == 1) {// tin nay dang ơ trang chu;
                    unset($arrNewData['file']);
                    $newsObj->updateData('store_home', $arrNewData, 'nw_id=' . $news_id);
                }

                if ($arrNewData['time_public']) {
                    if ($row['type']) {
                        Url::redirectUrl(array(), '?app=news&page=admin_news&cmd=home');
                    } else {
                        Url::redirectUrl(array(), '?app=news&page=admin_news&cmd=news_store');
                    }
                } else {
                    Url::redirectUrl(array(), '?app=news&page=admin_news&cmd=pending_public');
                }
            } else {
                $arrNewData['user_id'] = $row['user_id'] ? $row['user_id'] : $user_id;
                $arrNewData['editor_id'] = $user_id;
                if ($newsObj->updateReview($news_id, $arrNewData) >= 0) {

                    $row_seo = $newsObj->readSeo('id=' . $news_id);
                    if (count($row_seo) > 2) {
                        $newsObj->updateData('seo', $arrNewSEO, 'id=' . $news_id);
                    } else {
                        $arrNewSEO['id'] = $news_id;
                        $newsObj->insertSeo($arrNewSEO);
                    }

                    $status = SystemIO::post('status', 'int', 0);
                    if ($status == 2) {
                        Url::redirectUrl(array(), '?app=news&page=admin_news&cmd=news_private');
                    } else {
                        Url::redirectUrl(array(), '?app=news&page=admin_news&cmd=pending_censor');
                    }
                }
            }
        } else {
            $arrNewData['user_id'] = $row['user_id'] ? $row['user_id'] : $user_id;
            $arrNewData['editor_id'] = $user_id;
            print_r($arrNewData);
            try {


                if ($id_insert = $newsObj->insertReview($arrNewData)) {
                    $arrNewSEO['id'] = $id_insert;
                    $newsObj->insertSeo($arrNewSEO);
                    if (SystemIO::post('continue', 'int')) {
                        $_SESSION['news_continue'] = array(
                            'cate_id' => $cate_id,
                            'cate_path' => $cate_path,
                            'province_id' => $province_id,
                            'origin' => $origin,
                            'author' => $author,
                            'tag' => $tag,
                            'continue' => 1
                        );
                        Url::redirectUrl(array(), '?app=news&page=admin_news&cmd=news_create');
                    } else {
                        unset($_SESSION['news_continue']);
                        $status = SystemIO::post('status', 'int', 0);
                        if ($status == 2) {
                            Url::redirectUrl(array(), '?app=news&page=admin_news&cmd=news_private');
                        } else {
                            Url::redirectUrl(array(), '?app=news&page=admin_news&cmd=pending_censor');
                        }
                    }
                } else {
                    echo 'Đã có bài "' . $arrNewData['title'] . '" trong hệ thống!';
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    function index()
    {
        if (!UserCurrent::isLogin()) {
            @header('Location:?app=main&page=admin_login');
        }

        $cmd = SystemIO::get('cmd', 'str', 'intro');
        $news_id = SystemIO::get('news_id', 'int', 0);
        switch ($cmd) {
            case 'tag_meta':
                if (!UserCurrent::havePrivilege('TAG_META')) {
                    Url::urlDenied();
                }
                return $this->adminTagMeta();
            case 'statistic':
                return $this->newsStatistic();

            case 'home':
                if (!UserCurrent::havePrivilege('NEWS_HOME')) {
                    Url::urlDenied();
                }
                return $this->adminHome();

            case 'news_private':
                return $this->adminPrivate();
            case 'pending_public':

                $newsObj = new BackendNews();
                $total_home_record = $newsObj->countRecord('store_home');
                return $this->adminPendingPublic();
            case 'pending_censor':
                $newsObj = new BackendNews();
                $total_home_record = $newsObj->countRecord('store_home');
                return $this->adminPendingCensor();
            case 'news_return':
                return $this->adminNewsReturn();

            case 'news_store':
                $newsObj = new BackendNews();
                $total_home_record = $newsObj->countRecord('store_home');
                if (!UserCurrent::havePrivilege('NEWS_STORE')) {
                    Url::urlDenied();
                }
                return $this->adminNewsStore();

            case 'news_create':
                if (!UserCurrent::havePrivilege('NEWS_CREATE')) {
                    Url::urlDenied();
                }
                return $this->adminAddAndEdit($news_id);

            case 'news_write_seo':
                if (!UserCurrent::havePrivilege('WRITE_SEO')) {
                    Url::urlDenied();
                }
                return $this->adminWirteSEO();

            case 'comment':
                if (!UserCurrent::havePrivilege('NEWS_COMMENT')) {
                    Url::urlDenied();
                }
                return $this->adminNewsComment();

            case 'recycle_bin':
                return $this->adminRecycleBin();

            case 'nhuanbut':
                return $this->adminRoyalties();
            default:
                return $this->adminNewsIntro();
        }
    }

    function adminNewsIntro()
    {
        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_news_intro.htm");
        Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
        $newsObj = new BackendNews();
        $total_home_record = $newsObj->countRecord('store_home');
        $where = "time_public >= " . strtotime(date('d-m-Y', time()));
        $home_record_in_day = $newsObj->countRecord('store_home', $where);
        $where = "time_public < " . strtotime(date('d-m-Y', time()));
        $where .= " AND time_public > " . (strtotime(date('d-m-Y', time())) - 86400);
        $home_record_yesterday = $newsObj->countRecord('store_home', $where);
        joc()->set_var('total_home_record', $total_home_record);
        joc()->set_var('home_record_in_day', $home_record_in_day);
        joc()->set_var('home_record_yesterday', $home_record_yesterday);
        joc()->set_var('total_home_old', $total_home_record - ($home_record_in_day + $home_record_yesterday));

        $over_record = $total_home_record - 100;
        $over_limit = '';
        if (UserCurrent::havePrivilege('ADMIN_ALLOCATION')) {
            if ($over_record < 0) {
                $over_limit = 'Số tin chưa hiển thị đủ trên trang chủ';
            } elseif ($over_record < 10) {
                $over_limit = 'Bạn đã vượt quá <strong>' . $over_record . '</strong> tin cho phép hiển thị trên trang chủ';
            } else {
                $over_limit = 'Bạn đã vượt quá <strong>' . $over_record . '</strong> tin cho phép hiển thị trên trang chủ. Bạn phải xóa các tin cũ hoặc các tin không được hiển thị trên trang chủ. Click  <a href="javascript:;" onclick="deleteNewsHome(' . $over_record . ')">Vào đây</a> để xóa <b>' . $over_record . '</b> tin cũ nhất';
            }
        }
        joc()->set_var('over_limit', $over_limit);
        if (UserCurrent::havePrivilege('NEWS_VIEW_ALL')) {
            $total_pending_public = $newsObj->countRecord('store', 'time_public=0');
            joc()->set_var('total_pending_public', $total_pending_public);
            $total_pending_censor = $newsObj->countRecord('review', 'status=0');
            joc()->set_var('total_pending_censor', $total_pending_censor);
            $total_news_return = $newsObj->countRecord('review', 'status=1');
            joc()->set_var('total_news_return', $total_news_return);
        } else {
            joc()->set_var('total_pending_public', 'N/A');
            $total_pending_censor = $newsObj->countRecord('review',
                'status=0 AND user_id=' . UserCurrent::$current->data['id']);
            joc()->set_var('total_pending_censor', $total_pending_censor);
            $total_news_return = $newsObj->countRecord('review',
                'status=1 AND user_id=' . (int)UserCurrent::$current->data['id']);
            joc()->set_var('total_news_return', $total_news_return);
        }
        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

    function Royalties($type_post, $view)
    {
        $nb = 0;
        $vw = 1000;
        if ($type_post == 0) // copy
        {
            $nb = 1;
        } elseif ($type_post == 2) //dich
        {
            $nb = 2;
        } elseif ($type_post == 3) // bai tu viet
        {
            $nb = 3;
        } elseif ($type_post == 4) // tin tong hop
        {
            $nb = 1;
        } elseif ($type_post == 5) // tin tu viet
        {
            $nb = 2;
        } else {
            $nb = 1;
        }
        if ($view < 1000) {
            if ($type_post == 0 || $type_post == 4) {
                $vw = 0;
            } else {
                if ($type_post == 5) {
                    $vw = 15000;
                } elseif ($type_post == 3) {
                    $vw = 20000;
                } else {
                    $vw = 8000;
                }
            }
        } elseif ($view > 1000 && $view < 2000) {
            if ($type_post == 0) {
                $vw = 0;
            } else {
                $vw = 25000;
            }
        } elseif ($view > 2000 && $view < 5000) {
            if ($type_post == 0) {
                $vw = 10000;
            } else {
                $vw = 35000;
            }
        } elseif ($view > 5000 && $view < 10000) {
            if ($type_post == 0) {
                $vw = 15000;
            } else {
                $vw = 55000;
            }
        } elseif ($view > 10000) {
            $vw = 75000;
        }

        return $nb * $vw;
    }

    function adminRoyalties()
    {
        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_royalties.htm");
        Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
        $newsObj = new BackendNews();
        $arr_cate = array();
        $cate = $newsObj->getListCategory();
        foreach ($cate as $temp) {
            $arr_cate[$temp['id']] = $temp;
        }
        ini_set("display_errors", 0);
        $userObj = new User();
        $list_user = $userObj->userIdName(false);
        joc()->set_var('total_view', $newsObj->countRecord('store_view'));
        joc()->set_var('total_store', $newsObj->countRecord('store'));
        $array_post = array(
            '0' => 'Bài copy',
            '3' => 'Bài tự viết',
            '5' => 'Tin tự viết',
            '1' => 'Bài TTXVN',
            '2' => 'Dịch',
            '4' => 'Tin tổng hợp'
        );


        $date_s = SystemIO::get('date_s', 'def');
        $date_en = SystemIO::get('date_en', 'def');
        $type_post = SystemIO::get('type_post', 'def');
        $date_s = strtotime($date_s);
        $date_en = strtotime($date_en) + 86399;
        if ($_GET['user_id']) {
            $list_store = $newsObj->getListData('store',
                'id,editor_id,user_id,origin,author,type_post,censor_id,title,time_public,cate_id',
                'user_id =' . $_GET['user_id'] . ' AND time_public >' . $date_s . ' AND time_public < ' . $date_en,
                'user_id ASC', 3000, 'id');
        } else {
            $list_store = $newsObj->getListData('store',
                'id,editor_id,user_id,origin,author,type_post,censor_id,title,time_public,cate_id',
                'time_public >' . $date_s . ' AND time_public < ' . $date_en, 'user_id ASC', 3000, 'id');
        }
        $id = '';
        foreach ($list_store as $temp) {
            $id .= $temp['id'] . ',';
        }

        $id = trim($id, ',');
        $list_hit = $newsObj->getListData('store_hit', 'nw_id,hit', 'nw_id IN (' . $id . ')', 'nw_id ASC', 3000,
            'nw_id');
        $text = '<table cellspacing="0" cellpadding="0" border="0">'
            . '<tr>'
            . '<td>STT</td>'
            . '<td>Tiêu đề bài viết</td>'
            . '<td>Người làm/Duyệt</td>'
            . '<td>Lượt view</td>'
            . '<td>Loại bài/Nguồn/Tác giá</td>'
            . '<td>Ngày xuất bản</td>'
            . '<td>Nhuận bút tạm tính</td>'
            . '<tr>';
        $k = 1;
        foreach ($list_store as $row) {
            $href = 'http://congly.vn/' . trim($arr_cate[$row['cate_id']]['alias'],
                    '/') . '/' . Convert::convertLinkTitle($row['title']) . '-' . $row['id'] . '.html';
            if ($list_hit[$row['id']]['hit'] > 10) {
                $list_editor = explode(',', $row ['editor_id']);
                $list_editor = array_unique($list_editor);
                $user_name_editor = '';
                for ($i = 0; $i < count($list_editor); ++$i) {
                    $user_name_editor .= $list_user [$list_editor [$i]] ['user_name'] . ',';
                }
                $text .= '<tr>';
                $text .= '<td >' . $k . '</td><td><a href="' . $href . '">' . $row['title'] . '</a></td>';
                $text .= '<td >' . $list_user[$row['user_id']]['user_name'] . '/' . $list_user[$row['censor_id']]['user_name'] . '/' . $user_name_editor . '</td>';
                $text .= '<td >' . $list_hit[$row['id']]['hit'] . '</td>';
                $text .= '<td>' . $array_post[(int)$row['type_post']] . '/' . ($row['origin'] ? $row['origin'] : ' NA') . '/' . ($row['author'] ? $row['author'] : 'NA') . '</td>';
                $text .= '<td>' . date('d/m/Y H:i', $row['time_public']) . '</td>';
                $text .= '<td>' . $this->Royalties((int)$row['type_post'], $list_hit[$row['id']]['hit']) . '</td>';
                $text .= '</tr>';
                ++$k;
            }
        }
        $text .= '</table>';
        joc()->set_var('text', $text);
        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

    function adminPendingPublic()
    {
        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_pending_public.htm");
        Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
        Page::registerFile('admin Js', Module::pathSystemJS() . 'admin.js', 'header', 'js');
        Page::registerFile('date Js', Module::pathSystemJS() . 'date.js', 'header', 'js');
        Page::registerFile('jquery date Js', Module::pathSystemJS() . 'jquery.datepicker.min.js', 'header', 'js');
        Page::registerFile('date Css', Module::pathSystemCSS() . 'datepicker.css', 'header', 'css');
        Page::registerFile('popup-css', Module::pathSystemCSS() . 'popup.css', 'header', 'css');
        Page::registerFile('popup-js', JAVASCRIPT_PATH . 'popup.js', 'footer', 'js');
        Page::registerFile('jqDnR', JAVASCRIPT_PATH . 'jqDnR.js', 'footer', 'js');
        $newsObj = new BackendNews();
        $userObj = new User();
        /* Tìm kiếm */
        $list_user = $userObj->getList();
        $list_category = $newsObj->getListCategory('', '', 500, 'id');

        $item_per_page = 20;
        $page_no = SystemIO::get('page_no', 'int', 1);
        if ($page_no < 1) {
            $page_no = 1;
        }
        $stt = ($page_no - 1) * $item_per_page + 1;
        $limit = (($page_no - 1) * $item_per_page) . ',' . $item_per_page;
        $q = SystemIO::get('q', 'def', '');

        joc()->set_var('q', $q);
        $q = trim(str_replace(array('"', "'", '%'), array('', '', ''), $q), ' ');
        $cate_id = SystemIO::get('cate_id', 'int', 0);
        joc()->set_var('option_category',
            SystemIO::getOption(SystemIO::arrayToOption($list_category, 'id', 'name'), $cate_id));
        $wh = '(time_public IS NULL OR time_public = 0 OR time_public > ' . time() . ')';
        if ($q) {
            $wh .= " AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
        }
        if ($cate_id) {
            $wh .= " AND cate_path LIKE '%,{$cate_id},%'";
        }
        if (!UserCurrent::havePrivilege('NEWS_VIEW_ALL')) {
            $wh .= ' AND user_id=' . (int)UserCurrent::$current->data['id'];
        }

        $date_begin = SystemIO::get('date_begin', 'def');
        joc()->set_var('date_begin', $date_begin);
        if ($date_begin) {
            $date_begin = strtotime(str_replace('/', '-', $date_begin));
            $wh .= " AND time_created >= {$date_begin}";
        }

        $date_end = SystemIO::get('date_end', 'def');
        joc()->set_var('date_end', $date_end);
        if ($date_end) {
            $date_end = strtotime(str_replace('/', '-', $date_end));
            $wh .= " AND time_created <= {$date_end}";
        }
        $censor_name = SystemIO::get('censor_name', 'def');
        $list_user_id_search = '0';
        if ($censor_name) {
            $_user_id_search = $userObj->userNameToId($censor_name);
            if (count($_user_id_search)) {
                foreach ($_user_id_search as $_temp) {
                    $list_user_id_search .= ',' . $_temp['id'];
                }
            }

            $wh .= ' AND censor_id IN (' . $list_user_id_search . ') AND time_public < ' . time();
        }
        $list_news = $newsObj->getListStore($wh, 'time_created DESC', $limit);
        $cate_ids = '';
        $censor_id = '';
        foreach ($list_news as $_temp) {

            $cate_ids .= trim($_temp['cate_id'], ',') . ',';
            $censor_id .= $_temp['censor_id'] . ',';
        }
        $cate_ids = trim($cate_ids, ',');
        $censor_id = trim($censor_id, ',');

        $list_censor_user_name = $userObj->userIdToName($censor_id);

        $list_path_news = $newsObj->getMultiPathNews($cate_ids);
        joc()->set_block('AdminNews', 'ListRow', 'ListRow');
        $text_html = '';
        global $NEWS_PROPERTY;
        foreach ($list_news as $row) {
            joc()->set_var('title', $row['title']);
            joc()->set_var('nw_id', $row['id']);
            joc()->set_var('path', $list_path_news[$row['cate_id']]);
            joc()->set_var('time_created', date('H:i d-m-Y', $row['time_created']));
            joc()->set_var('description', strip_tags($row['description']));
            joc()->set_var('censor_user_name', $list_censor_user_name[$row['censor_id']]['user_name']);
            joc()->set_var('created_user_name', $list_user[$row['user_id']]['user_name']);
            $user_name_editor = '';
            $list_editor = explode(',', $row['editor_id']);
            $list_editor = array_unique($list_editor);

            for ($i = 0; $i < count($list_editor); ++$i) {
                $user_name_editor .= $list_user[$list_editor[$i]]['user_name'] . '<br/>,';
            }


            joc()->set_var('edit_user_name', trim($user_name_editor, ','));
            joc()->set_var('editor_info', '');
            joc()->set_var('created_info', '');
            joc()->set_var('stt', $stt);
            joc()->set_var('tag', $row['tag']);
            joc()->set_var('origin', $row['origin'] ? strip_tags($row['origin']) : 'N/A');
            $path_img = $newsObj->getPathNews($row['time_created']);
            if ($row['img1']) {
                $src = $row['img1'];
            } elseif ($row['img2']) {
                $src = $row['img2'];
            } elseif ($row['img3']) {
                $src = $row['img3'];
            } else {
                $src = '100x100.jpg';
                $path_img = 'news/icons/';
            }
            if ($src == '100x100.jpg') {
                joc()->set_var('src', 'data/news/icons/100x100.jpg');
            } else {
                joc()->set_var('src', IMG::show($path_img, $src));
            }
            $un_property = '';
            if (UserCurrent::havePrivilege('HOME_FOCUS')) {
                foreach ($NEWS_PROPERTY as $p => $desc) {
                    if ($row['property'] & $p) {
                        $property .= 'Bỏ thiết lập là: <a style="color:#990000" href="javascript:;" onclick="setProperty(' . $row['id'] . ',0,' . $p . ')">' . $desc . '</a><br/>';
                    } else {
                        $un_property .= 'Thiết lập là: <a href="javascript:;" onclick="setProperty(' . $row['id'] . ',' . $p . ',0)">' . $desc . '</a><br/>';
                    }
                }
            }
            joc()->set_var('property', $un_property . $property);
            ++$stt;
            $bg = "#FFF";
            if ($row['time_public'] > time()) {
                $bg = "#CCF2D9";
                joc()->set_var('time_public', 'Xuất bản lúc: ' . date('H:i d/m/y', $row['time_public']));
            } else {
                joc()->set_var('time_public', '');
            }

            joc()->set_var('bg', $bg);
            $act_del_edit = 'Hành động khác: <a href="?app=news&page=admin_news&cmd=news_create&news_id=' . $row['id'] . '&from=store">Sửa</a> | <a href="javascript:;" onclick="deleteData(' . $row['id'] . ')">Xóa</a> <br/>';
            if ((UserCurrent::havePrivilege('NEWS_ACTION_EDIT_DEL_RETURN_PENDING_PUBLIC') == true) or (UserCurrent::$current->data['id'] == $row['user_id'])) {
                joc()->set_var('act_del_edit', $act_del_edit);
            } else {
                joc()->set_var('act_del_edit', '');
            }

            //if(substr_count($row['cate_path'],',338,'))
            if (false) {
                joc()->set_var('xbta',
                    '<br/><a href="javascript:;" onclick="publicTA(' . $row['id'] . ')">Xuất bản các Site TA tỉnh</a><br/>');
            } else {
                joc()->set_var('xbta', '');
            }

            joc()->set_var('href',
                'http://congly.vn/' . (isset($list_category[$row['cate_id']]['alias']) ? $list_category[$row['cate_id']]['alias'] : 'no-cate') . '/review/' . Convert::convertLinkTitle($row['title']) . '-' . $row['id'] . '.html?from=store&ip=' . base64_encode($_SERVER['REMOTE_ADDR']));
            $text_html .= joc()->output('ListRow');
        }
        joc()->set_var('ListRow', $text_html);
        global $TOTAL_ROWCOUNT;
        $totalRecord = $TOTAL_ROWCOUNT;
        joc()->set_var('total_rowcount', $totalRecord);
        joc()->set_var('paging',
            '<li>Tổng số: ' . $totalRecord . '</li>' . Paging::paging($totalRecord, $item_per_page, 10));
        /* Setup Property */
        joc()->set_block('AdminNews', 'Property', 'Property');

        $text_property = '';
        $j = 0;
        foreach ($NEWS_PROPERTY as $p => $desc) {
            ++$j;
            joc()->set_var('property_setup', $j); // set hay huy
            joc()->set_var('property_cancel', ++$j); // set hay huy
            joc()->set_var('property_desc', $desc);
            joc()->set_var('property_value', $p);
            $text_property .= joc()->output('Property');
        }
        if (UserCurrent::havePrivilege('NEWS_HOME')) {
            joc()->set_var('Property', $text_property);
        } else {
            joc()->set_var('Property', '');
        }
        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

    function delete_files($path, $del_dir = false, $level = 0)
    {
        // Trim the trailing slash
        $path = preg_replace("|^(.+?)/*$|", "\\1", $path);
        if (!$current_dir = @opendir($path)) {
            return;
        }
        while (false !== ($filename = @readdir($current_dir))) {
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
        if ($del_dir == true and $level > 0) {
            @rmdir($path);
        }
    }

    function adminHome()
    {

        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_news.htm");
        Page::registerFile('admin Js', Module::pathSystemJS() . 'admin.js', 'header', 'js');
        Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
        $newsObj = new BackendNews();
        $list_category = $newsObj->getListCategory('cate_id2=0', '', "0,300", 'id');
        $this->autoDelNewsHome();
        global $NEWS_PROPERTY;
        $item_per_page = 20;
        $page_no = SystemIO::get('page_no', 'int', 1);
        if ($page_no < 1) {
            $page_no = 1;
        }
        $stt = ($page_no - 1) * $item_per_page + 1;
        $limit = (($page_no - 1) * $item_per_page) . ',' . $item_per_page;
        $q = SystemIO::get('q', 'def', '');
        $property = SystemIO::get('property', 'def', '');
        if ($property) {
            $arr_property = explode(',', $property);
        }

        joc()->set_var('q', $q);
        $q = trim(str_replace(array('"', "'", '%'), array('', '', ''), $q), ' ');
        $cate_id = SystemIO::get('cate_id', 'int', 0);
        joc()->set_var('option_category', SystemIO::selectBox($list_category, array($cate_id), "id", "id", "name", ""));

        $property_s = '';
        foreach ($NEWS_PROPERTY as $value => $desc) {

            if (@in_array($value, $arr_property)) {
                $property_s .= '<input type="checkbox" checked="checked" value="' . $value . '"  name="property_s"/>' . $desc . '&nbsp;&nbsp;';
            } else {
                $property_s .= '<input type="checkbox"  value="' . $value . '"  name="property_s"/>' . $desc . '&nbsp;&nbsp;';
            }
        }
        joc()->set_var('property_s', $property_s);

        $is_video = SystemIO::get('is_video', 'int', 0);
        if ($is_video) {
            joc()->set_var('video_check', 'checked="checked"');
        } else {
            joc()->set_var('video_check', '');
        }

        $wh = '1=1';

        if ($q) {
            $wh .= " AND title LIKE '%{$q}%' OR description LIKE '%{$q}%'";
        }
        if ($cate_id) {
            $wh .= " AND cate_path LIKE '%,{$cate_id},%'";
        }
        $property_value = 0;
        if (count($arr_property)) {
            $property_value = array_sum($arr_property);
        }

        if ($property_value) {
            $wh .= " AND property & {$property_value}=$property_value";
        }

        if ($is_video) {
            $wh .= " AND is_video = 1";
        }
        $list_news = $newsObj->getListHome($wh, 'time_public DESC,arrange ASC', $limit);
        /* Xoa tin trang chu khi nhieu */
        $news_ids = '';
        $censor_id = ',';
        foreach ($list_news as $_temp) {
            $news_ids .= $_temp['nw_id'] . ',';
            $cate_ids .= $_temp['cate_id'] . ',';
            if (!substr_count($censor_id, ',' . $_temp['censor_id'] . ',')) {
                $censor_id .= $_temp['censor_id'] . ',';
            }
        }
        $cate_ids = trim($cate_ids, ',');
        $news_ids = trim($news_ids, ',');
        $censor_id = trim($censor_id, ',');
        $userObj = new User();
        $list_censor_user_name = $userObj->userIdToName($censor_id);
        $list_news_hit = $newsObj->getListNewsHit($news_ids);
        $list_path_news = $newsObj->getMultiPathNews($cate_ids);
        joc()->set_block('AdminNews', 'ListRow', 'ListRow');
        $text_html = '';

        foreach ($list_news as $row) {
            $bg = "#FFF";
            if ($row['time_public'] > time()) {
                $bg = '#CCF2D9';
            }
            joc()->set_var('bg', $bg);
            joc()->set_var('title', $row['title']);
            joc()->set_var('nw_id', $row['nw_id']);
            joc()->set_var('path', $list_path_news[$row['cate_id']]);
            joc()->set_var('time_public', date('H:i d-m-Y', $row['time_public']));
            joc()->set_var('description', $row['description']);
            joc()->set_var('censer_user_name', $list_censor_user_name[$row['censor_id']]['user_name']);
            joc()->set_var('arrange', $row['arrange']);
            joc()->set_var('stt', $stt);
            joc()->set_var('id', $row['id']);
            joc()->set_var('tag', $row['tag']);
            $path_img = $newsObj->getPathNews($row['time_created']);
            if ($row['img1']) {
                $src = $row['img1'];
            } elseif ($row['img2']) {
                $src = $row['img2'];
            } elseif ($row['img3']) {
                $src = $row['img3'];
            } else {
                $src = '100x100.jpg';
                $path_img = 'data/news/icons/';
            }
            joc()->set_var('src', IMG::show($path_img, $src));
            $property = '';
            $un_property = '';
            foreach ($NEWS_PROPERTY as $p => $desc) {
                if ($p == 1) {
                    if (UserCurrent::havePrivilege('HOME_FOCUS')) {
                        if ($row['property'] & $p) {
                            $property .= 'Bỏ thiết lập là: <a style="color:#990000" href="javascript:;" onclick="setProperty(' . $row['nw_id'] . ',0,' . $p . ')">' . $desc . '</a><br/>';
                        } else {
                            $un_property .= 'Thiết lập là: <a href="javascript:;" onclick="setProperty(' . $row['nw_id'] . ',' . $p . ',0)">' . $desc . '</a><br/>';
                        }
                    } else {
                        if ($row['property'] & $p) {
                            $property .= 'Bỏ thiết lập là: <a style="color:#990000" href="javascript:;">' . $desc . '</a><br/>';
                        } else {
                            $un_property .= 'Thiết lập là: <a href="javascript:;" >' . $desc . '</a><br/>';
                        }
                    }
                } else {
                    if (UserCurrent::havePrivilege('CATE_FOCUS')) {
                        if ($row['property'] & $p) {
                            $property .= 'Bỏ thiết lập là: <a style="color:#990000" href="javascript:;" onclick="setProperty(' . $row['nw_id'] . ',0,' . $p . ')">' . $desc . '</a><br/>';
                        } else {
                            $un_property .= 'Thiết lập là: <a href="javascript:;" onclick="setProperty(' . $row['nw_id'] . ',' . $p . ',0)">' . $desc . '</a><br/>';
                        }
                    } else {
                        if ($row['property'] & $p) {
                            $property .= 'Bỏ thiết lập là: <a style="color:#990000" href="javascript:;" >' . $desc . '</a><br/>';
                        } else {
                            $un_property .= 'Thiết lập là: <a href="javascript:;" >' . $desc . '</a><br/>';
                        }
                    }
                }
            }
            joc()->set_var('hit', (int)$list_news_hit[$row['nw_id']]['hit']);
            joc()->set_var('property', $property . $un_property);
            ++$stt;
            $text_html .= joc()->output('ListRow');
        }
        joc()->set_var('ListRow', $text_html);
        global $TOTAL_ROWCOUNT;
        $totalRecord = $TOTAL_ROWCOUNT;
        joc()->set_var('paging',
            '<li>Tổng số: ' . $totalRecord . '</li>' . Paging::paging($totalRecord, $item_per_page, 10));
        /* Setup Property */
        joc()->set_block('AdminNews', 'Property', 'Property');

        $text_property = '';
        $j = 0;
        foreach ($NEWS_PROPERTY as $p => $desc) {
            ++$j;
            joc()->set_var('property_setup', $j); // set hay huy
            joc()->set_var('property_cancel', ++$j); // set hay huy
            joc()->set_var('property_desc', $desc);
            joc()->set_var('property_value', $p);
            $text_property .= joc()->output('Property');
        }
        joc()->set_var('Property', $text_property);
        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

    function adminPendingCensor()
    {
        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_pending_censor.htm");
        Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
        Page::registerFile('admin Js', Module::pathSystemJS() . 'admin.js', 'header', 'js');
        Page::registerFile('date Js', Module::pathSystemJS() . 'date.js', 'header', 'js');
        Page::registerFile('jquery date Js', Module::pathSystemJS() . 'jquery.datepicker.min.js', 'header', 'js');
        Page::registerFile('date Css', Module::pathSystemCSS() . 'datepicker.css', 'header', 'css');
        Page::registerFile('popup-css', Module::pathSystemCSS() . 'popup.css', 'header', 'css');
        Page::registerFile('popup-js', JAVASCRIPT_PATH . 'popup.js', 'footer', 'js');
        Page::registerFile('jqDnR', JAVASCRIPT_PATH . 'jqDnR.js', 'footer', 'js');
        $newsObj = new BackendNews();
        $userObj = new User();
        $list_user = $userObj->getList();
        /* Tìm kiếm */
        $list_category = $newsObj->getListCategory('', '', 500, 'id');

        $item_per_page = 20;
        $page_no = SystemIO::get('page_no', 'int', 1);
        if ($page_no < 1) {
            $page_no = 1;
        }
        $stt = ($page_no - 1) * $item_per_page + 1;
        $limit = (($page_no - 1) * $item_per_page) . ',' . $item_per_page;
        $q = SystemIO::get('q', 'def', '');

        joc()->set_var('q', $q);
        $q = trim(str_replace(array('"', "'", '%'), array('', '', ''), $q), ' ');
        $cate_id = SystemIO::get('cate_id', 'int', 0);
        joc()->set_var('option_category',
            SystemIO::getOption(SystemIO::arrayToOption($list_category, 'id', 'name'), $cate_id));
        $wh = '(status = 0 OR status = 3 OR status is NULL)';
        if (!UserCurrent::havePrivilege('NEWS_VIEW_ALL')) {
            $wh .= ' AND user_id=' . (int)UserCurrent::$current->data['id'];
        }
        if ($q) {
            $wh .= " AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
        }
        if ($cate_id) {
            $wh .= " AND cate_path LIKE '%,{$cate_id},%'";
        }

        $date_begin = SystemIO::get('date_begin', 'def');
        joc()->set_var('date_begin', $date_begin);
        if ($date_begin) {
            $date_begin = strtotime(str_replace('/', '-', $date_begin));
            $wh .= " AND time_created >= {$date_begin}";
        }
        $date_end = SystemIO::get('date_end', 'def');
        joc()->set_var('date_end', $date_end);
        if ($date_end) {
            $date_end = strtotime(str_replace('/', '-', $date_end));
            $wh .= " AND time_created <= {$date_end}";
        }
        $user_name = SystemIO::get('user_name', 'def');
        joc()->set_var('user_name', $user_name);
        $list_user_id_search = '0';
        if ($user_name) {
            $_user_id_search = $userObj->userNameToId($user_name);
            if (count($_user_id_search)) {
                foreach ($_user_id_search as $_temp) {
                    $list_user_id_search .= ',' . $_temp['id'];
                }
            }

            $wh .= ' AND user_id IN (' . $list_user_id_search . ')';
        }
        $list_news = $newsObj->getListReview($wh, 'id desc', $limit);
        $news_ids = '';
        $user_id = '';
        $cate_ids = '';
        $editor_id = '';
        foreach ($list_news as $_temp) {
            $cate_ids .= $_temp['cate_id'] . ',';
            $user_id .= $_temp['user_id'] . ',';
            if ((int)$_temp['editor_id']) {
                $editor_id .= $_temp['editor_id'] . ',';
            }
        }
        $cate_ids = trim($cate_ids, ',');
        $user_id = trim($user_id, ',');
        $editor_id = trim($editor_id, ',');
        if ($editor_id) {
            $user_id .= ',' . $editor_id;
        }
        $list_censor_user_name = $userObj->userIdToName($user_id);

        $list_path_news = $newsObj->getMultiPathNews($cate_ids);
        joc()->set_block('AdminNews', 'ListRow', 'ListRow');
        $text_html = '';
        global $NEWS_PROPERTY;
        foreach ($list_news as $row) {
            $bg = "#FFF";
            if ($row['time_public'] > time()) {
                $bg = '#CCF2D9';
            }
            joc()->set_var('title', $row['title']);
            joc()->set_var('nw_id', $row['id']);
            joc()->set_var('path', $list_path_news[$row['cate_id']]);
            joc()->set_var('reason_return',
                $row['reason_return'] ? '<p>Lý do trả: ' . $row['reason_return'] . '</p>' : '');
            joc()->set_var('time_created', date('H:i d-m-Y', $row['time_created']));
            joc()->set_var('description', $row['description']);
            joc()->set_var('user_name', $list_censor_user_name[$row['user_id']]['user_name']);
            joc()->set_var('edit_user_name',
                $list_censor_user_name[$row['editor_id']]['user_name'] ? $list_censor_user_name[$row['editor_id']]['user_name'] : 'N/A');
            joc()->set_var('editor_info',
                $list_user[$row['editor_id']]['full_name'] . ' - ' . $list_user[$row['editor_id']]['mobile_phone'] . ' - ' . $list_user[$row['editor_id']]['email']);
            joc()->set_var('created_info',
                $list_user[$row['user_id']]['full_name'] . ' - ' . $list_user[$row['user_id']]['mobile_phone'] . ' - ' . $list_user[$row['user_id']]['email']);
            joc()->set_var('stt', $stt);
            joc()->set_var('date_time_push_pendding', $row['date_time_push_pendding']);

            joc()->set_var('tag', $row['tag'] ? $row['tag'] : 'N/A');
            if ($row['time_public']) {
                joc()->set_var('time_public', 'Xuất bản lúc: ' . date('H:i d/n/y', $row['time_public']));
            } else {
                joc()->set_var('time_public', '');
            }

            $path_img = $newsObj->getPathNews($row['time_created']);
            if ($row['img1']) {
                $src = $row['img1'];
            } elseif ($row['img2']) {
                $src = $row['img2'];
            } elseif ($row['img3']) {
                $src = $row['img3'];
            } else {
                $src = '100x100.jpg';
                $path_img = 'data/news/icons/';
            }
            joc()->set_var('src', IMG::show($path_img, $src));
            joc()->set_var('origin', $row['origin'] ? $row['origin'] : 'N/A');
            $un_property = '';
            $property = '';
            if (UserCurrent::havePrivilege('HOME_FOCUS')) {
                foreach ($NEWS_PROPERTY as $p => $desc) {
                    if ($row['property'] & $p) {
                        $property .= 'Bỏ thiết lập là: <a style="color:#990000" href="javascript:;" onclick="setProperty(' . $row['id'] . ',0,' . $p . ')">' . $desc . '</a><br/>';
                    } else {
                        $un_property .= 'Thiết lập là: <a href="javascript:;" onclick="setProperty(' . $row['id'] . ',' . $p . ',0)">' . $desc . '</a><br/>';
                    }
                }
            }
            joc()->set_var('property', $un_property . $property);
            $function = '';
            if (UserCurrent::havePrivilege('NEWS_ACTION_RETURN')) {
                $function .= '<a href="javascript:;" class="show-list" rel="reason-return" onclick="getId(' . $row['id'] . ')">Trả về</a><br />';
            }
            if (UserCurrent::havePrivilege('NEWS_ACTION_CENSOR')) {
                $function .= '<a href="javascript:;" onclick="doCensor(' . $row['id'] . ',0,this)">Lên chờ xuất bản</a><br/>';
            }
            if (UserCurrent::havePrivilege('NEWS_ACTION_PUBLIC_CENSOR')) {
                $function .= '<a href="javascript:;" onclick="doCensor(' . $row['id'] . ',1)">Xuất bản ngay</a><br/>';
            }
            joc()->set_var('function', $function);
            ++$stt;

            $act_del_edit = 'Hành động khác: <a href="?app=news&page=admin_news&cmd=news_create&news_id=' . $row['id'] . '">Sửa</a> | <a href="javascript:;" onclick="delData(' . $row['id'] . ')">Xóa</a> <br/>';
            if ((UserCurrent::havePrivilege('NEWS_ACTION_EDIT_DEL_RETURN_PENDING_CENSOR') == true) or (UserCurrent::$current->data['id'] == $row['user_id'])) {
                joc()->set_var('act_del_edit', $act_del_edit);
            } else {
                joc()->set_var('act_del_edit', '');
            }

            joc()->set_var('bg', $bg);
            joc()->set_var('href',
                'http://congly.vn/' . (isset($list_category[$row['cate_id']]['alias']) ? $list_category[$row['cate_id']]['alias'] : 'no-cate') . '/review/' . Convert::convertLinkTitle($row['title']) . '-' . $row['id'] . '.html?ip=' . base64_encode($_SERVER['REMOTE_ADDR']));
            $text_html .= joc()->output('ListRow');
        }
        joc()->set_var('ListRow', $text_html);
        global $TOTAL_ROWCOUNT;
        $totalRecord = $TOTAL_ROWCOUNT;
        joc()->set_var('total_rowcount', $totalRecord);
        joc()->set_var('paging',
            '<li>Tổng số: ' . $totalRecord . '</li>' . Paging::paging($totalRecord, $item_per_page, 10));
        /* Setup Property */
        joc()->set_block('AdminNews', 'Property', 'Property');

        $text_property = '';
        $j = 0;
        foreach ($NEWS_PROPERTY as $p => $desc) {
            ++$j;
            joc()->set_var('property_setup', $j); // set hay huy
            joc()->set_var('property_cancel', ++$j); // set hay huy
            joc()->set_var('property_desc', $desc);
            joc()->set_var('property_value', $p);
            $text_property .= joc()->output('Property');
        }
        if (UserCurrent::havePrivilege('NEWS_HOME')) {
            joc()->set_var('Property', $text_property);
        } else {
            joc()->set_var('Property', '');
        }
        $f_censor_all = '';
        if (!UserCurrent::havePrivilege('NEWS_ACTION_CENSOR')) {
            $f_censor_all = 'disabled="disabled"';
        }
        joc()->set_var('f_censor_all', $f_censor_all);

        $f_censor_public_all = '';
        if (!UserCurrent::havePrivilege('NEWS_ACTION_PUBLIC_CENSOR')) {
            $f_censor_public_all = 'disabled="disabled"';
        }
        joc()->set_var('f_censor_public_all', $f_censor_public_all);

        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

    function adminNewsReturn()
    {
        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_news_return.htm");
        Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
        Page::registerFile('admin Js', Module::pathSystemJS() . 'admin.js', 'header', 'js');
        Page::registerFile('date Js', Module::pathSystemJS() . 'date.js', 'header', 'js');
        Page::registerFile('jquery date Js', Module::pathSystemJS() . 'jquery.datepicker.min.js', 'header', 'js');
        Page::registerFile('date Css', Module::pathSystemCSS() . 'datepicker.css', 'header', 'css');
        Page::registerFile('popup-css', Module::pathSystemCSS() . 'popup.css', 'header', 'css');
        Page::registerFile('popup-js', JAVASCRIPT_PATH . 'popup.js', 'footer', 'js');
        Page::registerFile('jqDnR', JAVASCRIPT_PATH . 'jqDnR.js', 'footer', 'js');
        $newsObj = new BackendNews();
        $userObj = new User();
        $list_category = $newsObj->getListCategory('', '', 500, 'id');

        $item_per_page = 50;
        $page_no = SystemIO::get('page_no', 'int', 1);
        if ($page_no < 1) {
            $page_no = 1;
        }
        $stt = ($page_no - 1) * $item_per_page + 1;
        $limit = (($page_no - 1) * $item_per_page) . ',' . $item_per_page;
        $q = SystemIO::get('q', 'def', '');

        joc()->set_var('q', $q);
        $q = trim(str_replace(array('"', "'", '%'), array('', '', ''), $q), ' ');
        $cate_id = SystemIO::get('cate_id', 'int', 0);
        joc()->set_var('option_category',
            SystemIO::getOption(SystemIO::arrayToOption($list_category, 'id', 'name'), $cate_id));
        $wh = 'status = 1';
        if (!UserCurrent::havePrivilege('NEWS_VIEW_ALL')) {
            $wh .= ' AND user_id=' . (int)UserCurrent::$current->data['id'];
        }
        if ($q) {
            $wh .= " AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
        }
        if ($cate_id) {
            $wh .= " AND cate_path LIKE '%,{$cate_id},%'";
        }

        $date_begin = SystemIO::get('date_begin', 'def');
        joc()->set_var('date_begin', $date_begin);
        if ($date_begin) {
            $date_begin = strtotime(str_replace('/', '-', $date_begin));
            $wh .= " AND time_created >= {$date_begin}";
        }

        $date_end = SystemIO::get('date_end', 'def');
        joc()->set_var('date_end', $date_end);
        if ($date_end) {
            $date_end = strtotime(str_replace('/', '-', $date_end));
            $wh .= " AND time_created <= {$date_end}";
        }
        $list_news = $newsObj->getListReview($wh, 'id DESC', $limit);
        $censor_id = ',';
        $user_ids = '';
        $cate_ids = '';
        foreach ($list_news as $_temp) {
            $cate_ids .= $_temp['cate_id'] . ',';
            if (!substr_count($censor_id, ',' . $_temp['censor_id'] . ',')) {
                $censor_id .= (int)$_temp['censor_id'] . ',';
            }
            if (!substr_count($user_ids, ',' . $_temp['user_id'] . ',')) {
                $user_ids .= (int)$_temp['user_id'] . ',';
            }
        }
        $cate_ids = trim($cate_ids, ',');
        $censor_id = trim($censor_id, ',');
        $user_ids = trim($user_ids, ',');
        if (strlen($user_ids)) {
            $list_censor_user_name = $userObj->userIdToName($censor_id . ',' . $user_ids);
        }
        $list_path_news = $newsObj->getMultiPathNews($cate_ids);
        joc()->set_block('AdminNews', 'ListRow', 'ListRow');
        $text_html = '';
        global $NEWS_PROPERTY;
        foreach ($list_news as $row) {
            joc()->set_var('title', $row['title']);
            joc()->set_var('nw_id', $row['id']);
            joc()->set_var('path', $list_path_news[$row['cate_id']]);
            joc()->set_var('time_created', date('H:i d-n-Y', $row['time_created']));
            joc()->set_var('description', $row['description']);
            joc()->set_var('reason', nl2br($row['reason_return']));
            joc()->set_var('creator', $list_censor_user_name[$row['user_id']]['user_name']);
            joc()->set_var('censor',
                $list_censor_user_name[$row['censor_id']]['user_name'] ? $list_censor_user_name[$row['censor_id']]['user_name'] : 'N/A');
            joc()->set_var('stt', $stt);
            joc()->set_var('date_time_return', $row['date_time_return']);

            joc()->set_var('tag', $row['tag'] ? $row['tag'] : 'N/A');
            $path_img = $newsObj->getPathNews($row['time_created']);
            if ($row['img1']) {
                $src = $row['img1'];
            } elseif ($row['img2']) {
                $src = $row['img2'];
            } elseif ($row['img3']) {
                $src = $row['img3'];
            } else {
                $src = '100x100.jpg';
                $path_img = 'data/news/icons/';
            }
            joc()->set_var('src', IMG::show($path_img, $src));
            ++$stt;
            joc()->set_var('href',
                'http://congly.vn/' . (isset($list_category[$row['cate_id']]['alias']) ? $list_category[$row['cate_id']]['alias'] : 'no-cate') . '/review/' . Convert::convertLinkTitle($row['title']) . '-' . $row['id'] . '.html?ip=' . base64_encode($_SERVER['REMOTE_ADDR']));
            $text_html .= joc()->output('ListRow');
        }
        joc()->set_var('ListRow', $text_html);
        global $TOTAL_ROWCOUNT;
        $totalRecord = $TOTAL_ROWCOUNT;
        joc()->set_var('total_rowcount', $totalRecord);
        joc()->set_var('paging',
            '<li>Tổng số: ' . $totalRecord . '</li>' . Paging::paging($totalRecord, $item_per_page, 10));
        /* Setup Property */

        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

    function adminNewsStore()
    {
        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_news_store.htm");
        Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
        Page::registerFile('admin Js', Module::pathSystemJS() . 'admin.js', 'header', 'js');
        Page::registerFile('date Js', Module::pathSystemJS() . 'date.js', 'header', 'js');
        Page::registerFile('jquery date Js', Module::pathSystemJS() . 'jquery.datepicker.min.js', 'header', 'js');
        Page::registerFile('date Css', Module::pathSystemCSS() . 'datepicker.css', 'header', 'css');
        Page::registerFile('popup-css', Module::pathSystemCSS() . 'popup.css', 'header', 'css');
        Page::registerFile('popup-js', JAVASCRIPT_PATH . 'popup.js', 'footer', 'js');
        Page::registerFile('jqDnR', JAVASCRIPT_PATH . 'jqDnR.js', 'footer', 'js');
        $newsObj = new BackendNews();
        $userObj = new User();
        $memcache = new Memcache();
        $memcache->addServer('localhost', 11211);

        // Đóng bài viết xóa key memcache
        if (SystemIO::get('nw_id')) {
            $memcache->delete('active_reading' . SystemIO::get('nw_id'));
        }

        /* Tìm kiếm */
        $list_category_all = $newsObj->getListCategory('', '', 500, 'id');
        $list_topic = $newsObj->getListData('topic', 'id,name,property,time_created', 'property >0', '', '0,500', 'id',
            false);
        $item_per_page = 20;
        $page_no = SystemIO::get('page_no', 'int', 1);
        $filter_id = SystemIO::get('filter_id', 'int', 0);
        $type_post = SystemIO::get('type_post', 'int', -1);
        if ($page_no < 1) {
            $page_no = 1;
        }
        $stt = ($page_no - 1) * $item_per_page + 1;
        $limit = (($page_no - 1) * $item_per_page) . ',' . $item_per_page;
        $q = SystemIO::get('q', 'def', '');

        joc()->set_var('q', $q);
        $label_s = substr($q, 0, 7);
        $q = trim(str_replace(array('"', "'", '%'), array('', '', ''), $q), ' ');
        $cate_id = SystemIO::get('cate_id', 'int', 0);
        $cate_id_2 = SystemIO::get('cate_id_2', 'int', 0);

        joc()->set_var('option_category',
            SystemIO::getOption(SystemIO::arrayToOption($list_category_all, 'id', 'name'), $cate_id));
        joc()->set_var('option_category1',
            SystemIO::getOption(SystemIO::arrayToOption($list_category_all, 'id', 'name'), $cate_id_2));
        $wh = 'time_public > 0 AND time_public < ' . time();
        if ($q) {
            if ($label_s == 'origin:' || $label_s == 'Origin:' || $label_s == 'ORIGIN:') {
                $wh .= " AND origin LIKE '%" . substr($q, 7, strlen($q)) . "%'";
            } else {
                $wh .= " AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
            }
        }
        if ($cate_id_2) {
            $wh .= " AND cate_path LIKE '%,{$cate_id_2},%'";
        }
        if ($cate_id) {
            $wh .= " AND cate_path LIKE '%,{$cate_id},%'";
        }

        if ($filter_id) {
            $wh .= ' AND cate_id=' . $filter_id;
        }
        if ($type_post > 0) {
            $wh .= ' AND type_post=' . $type_post;
        }
        $a_type_post = array(
            '0' => 'Bài dẫn nguồn',
            '1' => 'Bài thông tấn xã',
            '2' => 'Bài dịch',
            '3' => 'Bài tự viết',
            '4' => 'Bài tổng hợp',
            '5' => 'Tin tự viết',
            '6' => 'Infographic'
        );
        $type_post_t = '';
        foreach ($a_type_post as $k => $v) {
            if ($k == (int)$type_post) {
                $type_post_t .= '<input type="radio" name="type_post" value="' . $k . '" checked="checked"> ' . $v . '&nbsp;&nbsp;';
            } else {
                $type_post_t .= '<input type="radio" name="type_post" value="' . $k . '"> ' . $v . '&nbsp;&nbsp;';
            }
        }
        joc()->set_var('type_post', $type_post_t);
        $date_begin = SystemIO::get('date_begin', 'def');
        joc()->set_var('date_begin', $date_begin);
        if ($date_begin) {
            $date_begin = strtotime(str_replace('/', '-', $date_begin));
            $wh .= " AND time_public >= {$date_begin}";
        }

        $date_end = SystemIO::get('date_end', 'def');
        joc()->set_var('date_end', $date_end);
        if ($date_end) {
            $date_end = strtotime(str_replace('/', '-', $date_end));
            $date_end += 86399;
            $wh .= " AND time_public <= {$date_end}";
        }
        $censor_name = SystemIO::get('censor_name', 'def');
        joc()->set_var('censor_name', $censor_name);
        $list_user_id_search = '0';
        if ($censor_name) {
            $_user_id_search = $userObj->userNameToId($censor_name);
            if (count($_user_id_search)) {
                foreach ($_user_id_search as $_temp) {
                    $list_user_id_search .= ',' . $_temp['id'];
                }
            }
            $wh .= ' AND censor_id IN (' . $list_user_id_search . ')';
        }
        $btv_name = SystemIO::get('btv_name', 'def');
        joc()->set_var('btv_name', $btv_name);
        if ($btv_name) {
            $_user_id_search = $userObj->userNameToId($btv_name);
            $list_user_id_search = '0';
            if (count($_user_id_search)) {
                foreach ($_user_id_search as $_temp) {
                    $list_user_id_search .= ',' . $_temp['id'];
                }
            }
            $wh .= ' AND user_id IN (' . $list_user_id_search . ')';
        }
        /* Lay thông tin news */
        $list_news = $newsObj->getListStore($wh, 'time_public desc', $limit);
        $news_ids = '';
        $cate_ids = '';
        $user_ids = ',';
        $censor_ids = ',';
        $editor_id = ',';
        $news_ids_home = '';
        foreach ($list_news as $_temp) {
            $cate_ids .= $_temp['cate_id'] . ',';
            $news_ids .= $_temp['id'] . ',';
            if (!substr_count($censor_ids, ',' . $_temp['censor_id'] . ',')) {
                $censor_ids .= $_temp['censor_id'] . ',';
            }
            if (!substr_count($user_ids, ',' . $_temp['user_id'] . ',')) {
                $user_ids .= $_temp['user_id'] . ',';
            }
            if (!substr_count($editor_id, ',' . $_temp['editor_id'] . ',')) {
                $editor_id .= (int)$_temp['editor_id'] . ',';
            }
            if ($_temp['type'] == 1) {
                $news_ids_home .= $_temp['id'] . ',';
            }
        }
        $news_ids_home = rtrim($news_ids_home, ',');
        $cate_ids = trim($cate_ids, ',');
        $censor_ids = trim($censor_ids, ',');
        $user_ids = trim($user_ids, ',');
        $editor_id = trim($editor_id, ',');
        $news_ids = trim($news_ids, ',');
        $list_news_hit = $newsObj->getListNewsHit($news_ids);
        if ($editor_id) {
            $user_ids .= ',' . $editor_id;
        }

        $list_censor_user_name_and_name_btv = $userObj->userIdToNameAll();
        if ($news_ids_home) {
            $list_home = $newsObj->getListData('store_home', 'nw_id,property', 'nw_id IN(' . $news_ids_home . ')', null,
                '100', 'nw_id', false);
        }

        $list_path_news = $newsObj->getMultiPathNews($cate_ids);
        joc()->set_block('AdminNews', 'ListRow', 'ListRow');
        $text_html = '';
        global $NEWS_PROPERTY;
        foreach ($list_news as $row) {
            joc()->set_var('title', strip_tags($row['title']));
            joc()->set_var('nw_id', $row['id']);
            joc()->set_var('path', $list_path_news[$row['cate_id']]);
            joc()->set_var('time_created', date('H:i d-m-Y', $row['time_created']));
            joc()->set_var('description', strip_tags($row['description']));
            joc()->set_var('censer_user_name', $list_censor_user_name_and_name_btv[$row['censor_id']]['user_name']);
            joc()->set_var('name_btv', $list_censor_user_name_and_name_btv[$row['user_id']]['user_name']);

            $user_name_editor = '';
            $list_editor = explode(',', $row['editor_id']);
            #$list_editor = array_unique($list_editor);
            for ($i = 0; $i < count($list_editor); ++$i) {
                $user_name_editor .= $list_censor_user_name_and_name_btv[$list_editor[$i]]['user_name'] . '<br/>,';
            }
            joc()->set_var('name_edit', trim($user_name_editor, ','));
            joc()->set_var('stt', $stt);
            joc()->set_var('option_topic',
                SystemIO::getOption(SystemIO::arrayToOption($list_topic, 'id', 'name'), $row['topic_id']));
            joc()->set_var('tag', $row['tag'] ? $row['tag'] : 'N/A');
            joc()->set_var('time_public', date('H:i d-m-Y', $row['time_public']));
            joc()->set_var('href',
                'https://congly.vn/' . $list_category_all[$row['cate_id']]['alias'] . '/' . Convert::convertLinkTitle($row['title']) . '-' . $row['id'] . '.html');
            joc()->set_var('hit', (int)$list_news_hit[$row['id']]['hit']);
            $path_img = $newsObj->getPathNews($row['time_created']);
            if ($row['img1']) {
                $src = $row['img1'];
            } elseif ($row['img2']) {
                $src = $row['img2'];
            } elseif ($row['img3']) {
                $src = $row['img3'];
            } else {
                $src = '100x100.jpg';
                $path_img = 'data/news/icons/';
            }
            joc()->set_var('src', IMG::show($path_img, $src));

            joc()->set_var('origin', SystemIO::strLeft($row['origin'], 20));
            $property = '';
            $un_property = '';
            if (UserCurrent::havePrivilege('HOME_FOCUS')) {
                foreach ($NEWS_PROPERTY as $p => $desc) {
                    if ($row['property'] & $p) {
                        $property .= 'Bỏ thiết lập là: <a style="color:#990000" href="javascript:;" onclick="setProperty(' . $row['id'] . ',0,' . $p . ')">' . $desc . '</a><br/>';
                    } else {
                        $un_property .= 'Thiết lập là: <a href="javascript:;" onclick="setProperty(' . $row['id'] . ',' . $p . ',0)">' . $desc . '</a><br/>';
                    }
                }
            }
            joc()->set_var('property', $un_property . $property);
            $title_pos = '';
            if ($row['type'] == 1) {
                $title_pos = 'Tin được xuất bản là tin hiển thị trang chủ';
                $bg = "#ebebeb";
                if ($list_home[$row['id']]['property'] & NEWS_FEATURED) {
                    $title_pos = " Tin được xuất bản là tin TIÊU ĐIỂM TRANG CHỦ";
                    $bg = "#FFFFCC";
                }
                if ($list_home[$row['id']]['property'] & NEWS_FEATURED_CATE) {
                    $title_pos .= ' Tin được xuất bản là tin NỔI BẬT MỤC TRANG CHỦ';
                    $bg = "#0faffa";
                }
            } else {
                $bg = "#FFF";
                $title_pos = "Tin được xuất bản là tin thông thường";
            }

            $key_memcache_reading = 'active_reading' . $row['id'];
            if (UserCurrent::havePrivilege('NEWS_ACTION_EDIT_DEL_RETURN')) {
                if ($memcache->get($key_memcache_reading)) {
                    joc()->set_var('action_store',
                        '<font color="red">Bài đang mở</font><br>');
                } else {
                    joc()->set_var('action_store',
                        '<a href="javascript:;" onclick="getId(' . $row['id'] . ')" rel="reason-return" class="show-list">Về tin chờ duyệt</a> | <a href="?app=news&page=admin_news&cmd=news_create&news_id=' . $row['id'] . '&from=store">Sửa</a>| <a href="javascript:;" onclick="deleteData(' . $row['id'] . ')">Xóa bài</a><br/>');
                }
            } else {
                if (UserCurrent::havePrivilege('NEWS_ACTION_EDIT_STORE')) {

                    if ($memcache->get($key_memcache_reading)) {
                        joc()->set_var('action_store',
                            '<font color="red">Bài đang mở</font><br>');
                    } else {
                        joc()->set_var('action_store',
                            '<a href="?app=news&page=admin_news&cmd=news_create&news_id=' . $row['id'] . '&from=store">Sửa</a>');
                    }

                } else {
                    joc()->set_var('action_store', '');
                }
            }

            if (UserCurrent::havePrivilege('NEWS_ACTION_REFRESH')) {
                //if(true)
                $action_refresh = '<a href="javascript:;" onclick="newsRefresh(\'' . $row['id'] . '\')">Làm mới tin</a><br/><a href="javascript:;" onclick="newsSetTimePublic(\'' . $row['id'] . '\')">Làm cũ tin<a>';
            } else {
                $action_refresh = '';
            }
            joc()->set_var('action_refresh', $action_refresh);
            joc()->set_var('title_pos', $title_pos);
            joc()->set_var('bg', $bg);

            ++$stt;
            $text_html .= joc()->output('ListRow');
        }
        joc()->set_var('ListRow', $text_html);
        global $TOTAL_ROWCOUNT;
        $totalRecord = $TOTAL_ROWCOUNT;
        joc()->set_var('total_rowcount', $totalRecord);
        joc()->set_var('paging',
            '<li>Tổng số: ' . $totalRecord . '</li>' . Paging::paging($totalRecord, $item_per_page, 10));
        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

    function adminAddAndEdit($id = 0)
    {
        //ini_set("display_errors", 1);
        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_add_edit.htm");
        Page::registerFile('admin Js', Module::pathSystemJS() . 'admin.js', 'header', 'js');
        Page::registerFile('ckeditor.js', 'webskins/richtext/ckeditor/ckeditor.js', 'footer', 'js');
        Page::registerFile('date Js', Module::pathSystemJS() . 'date.js', 'header', 'js');
        Page::registerFile('jquery date Js', Module::pathSystemJS() . 'jquery.datepicker.min.js', 'header', 'js');
        Page::registerFile('thickbox.js', Module::pathSystemJS() . 'thickbox.js', 'header', 'js');
        Page::registerFile('date Css', Module::pathSystemCSS() . 'datepicker.css', 'header', 'css');
        Page::registerFile('thickbox css', Module::pathSystemCSS() . 'thickbox.css', 'header', 'css');

        Page::registerFile('date Js', Module::pathSystemJS() . 'date.js', 'header', 'js');

        Page::registerFile('jquery date Js', Module::pathSystemJS() . 'jquery.datepicker.min.js', 'header', 'js');
        Page::registerFile('date Css', Module::pathSystemCSS() . 'datepicker.css', 'header', 'css');
        Page::registerFile('date Js', Module::pathSystemJS() . 'jquery.base64.js', 'header', 'js');
        Page::registerFile('jquery.adapter.js', 'webskins/richtext/ckeditor/adapters/jquery.adapter.js', 'footer',
            'js');
        Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
        //Page::registerFile('admin_news.js', Module::pathJS().'admin_news.js' , 'footer', 'js');
        $user_info = UserCurrent::$current->data;
        $memcache = new Memcache();
        $memcache->addServer('localhost', 11211);
        $key_memcache = 'active_reading';


        $from = SystemIO::get('from', 'def', 'review');
        $row = $_SESSION['news_continue'];
        joc()->set_var('begin_form',
            Form::begin(false, "POST", ' enctype="multipart/form-data" onsubmit="return checkForm()"'));
        joc()->set_var('end_form', Form::end());

        if ($user_info['is_mobile']) {
            joc()->set_var('class_content', '');
        } else {
            joc()->set_var('class_content', 'ckeditor');
        }

        $newsObj = new BackendNews();
        $lisTempData = $newsObj->getListData('autosave', '*', 'is_used = 0 AND user_id=' . $user_info['id'],
            'time_updated DESC', '0,1');
        if ($from == 'auto') {
            $row = current($lisTempData);
        }

        if ($from == 'recycle') {
            $recycle_id = SystemIO::get('recycle_id', 'int', '0');
            $list_recycle = $newsObj->getListData('log_delete_from_store', '*', 'id = ' . $recycle_id, 'id desc', 1);
            $row = current($list_recycle);
            $row = json_decode($row['title'], true);
        }
        if (count($lisTempData)) {
            joc()->set_var('get_auto',
                '<a href="?app=news&page=admin_news&cmd=news_create&from=auto" style="text-align:center;color:red">CLICK ĐỂ LẤY LẠI DỮ LIỆU ĐÃ MẤT TỪ PHIÊN LÀM VIỆC TRƯỚC</a>');
        } else {
            joc()->set_var('get_auto', '');
        }

        joc()->set_var('from', $from);

        if ($id) {
            if ($from == 'review') {
                $row = $newsObj->getReviewOne($id);
                $row_seo = $newsObj->getSEOOne('id=' . $id);
            } elseif ($from == 'store') {
                $row = $newsObj->getStoreOne($id);
                $row['content'] = $newsObj->getContentOne($id);
                $row_seo = $newsObj->getSEOOne('nw_id=' . $id);
            }

            if ($row['user_id'] != $user_info['id']) {
                if (UserCurrent::havePrivilege('NEWS_ACTION_EDIT_DEL_RETURN_PENDING_CENSOR') == false && UserCurrent::havePrivilege('NEWS_ACTION_EDIT_DEL_RETURN ') == false) {
                    echo 'Bạn không có quyền sửa bài viết này!';
                    die;
                }
            }
        }
        if ($id && $from == 'store') {
            $key_memcache .= $id;
            if ($memcache->get($key_memcache) && $memcache->get('user_active_read_' . $user_info['id']) != $row['id']) {
                echo '<center>Bài viết đang được người khác sửa!</center>';
                die;
            } else {
                $memcache->set($key_memcache, $id, MEMCACHE_COMPRESSED, 900);
                $memcache->set('user_active_read_' . $user_info['id'], $row['id'], MEMCACHE_COMPRESSED, 900);
            }
        }

        $list_category = $newsObj->getListCategory('', 'id ASC');
        $arr_cate1 = array();
        $arr_cate2 = array();
        $arr_cate3 = array();
        $arr_cate4 = array();
        $arr_cate5 = array();
        foreach ($list_category as $_temp) {
            if ($_temp['cate_id4']) {
                $arr_cate5[$_temp['id']] = $_temp['name'];
            } elseif ($_temp['cate_id3']) {
                $arr_cate4[$_temp['id']] = $_temp['name'];
            } elseif ($_temp['cate_id2']) {
                $arr_cate3[$_temp['id']] = $_temp['name'];
            } elseif ($_temp['cate_id1']) {
                $arr_cate2[$_temp['id']] = $_temp['name'];
            } else {
                $arr_cate1[$_temp['id']] = $_temp;
            }
        }

        $arr_cate_id = explode(',', trim($row['cate_path'], ','));

        for ($i = 0; $i <= count($arr_cate_id); ++$i) {
            $row['cate_id' . ($i + 1)] = $arr_cate_id[$i];
        }

        $str_cate_selected = trim($row['cate_path'], ',');
        if ($row['cate_other']) {
            $str_cate_selected .= ',' . trim($row['cate_other'], ',');
        }

        $arraySelected = explode(',', $str_cate_selected);
        joc()->set_var('option_cate1', SystemIO::selectBox($arr_cate1, $arraySelected, "id", "id", "name"));
        if (count($arr_cate_id) > 1) {
            for ($k = 2; $k <= count($arr_cate_id); ++$k) {
                $arr_cate = "arr_cate" . $k;
                if ($row['cate_id' . $k]) {
                    joc()->set_var('option_cate' . ($k),
                        '<select  id="cate' . ($k) . '" name="data[cate_id' . ($k) . '][]" multiple="multiple" style="height:100px;><option style="font-weight: bold">Chọn danh mục cấp ' . ($k) . '</option>' . SystemIO::getMutileOption($$arr_cate,
                            $arraySelected) . '</select>'); // 2 day $$ la dung
                } else {
                    joc()->set_var('option_cate' . $k, '');
                }
            }
        }

        for ($l = count($arr_cate_id) + 1; $l < 5; ++$l) {
            joc()->set_var('option_cate' . $l, '');
        }


        joc()->set_var('option_cate2',
            '<select  id="cate2" name="data[cate_id2][]" multiple="multiple" style="height:100px;"><option style="font-weight: bold">Chọn danh mục cấp 2</option>' . SystemIO::getOption($arr_cate2,
                (int)$row['cate_id2']) . '</select>');
        /* load cate_other co xac dinh chinh phu ro rang */
        if ($row['cate_other']) {
            $arr_cate_other = explode(',', trim($row['cate_other'], ','));
        }
        joc()->set_var('option_cate11',
            SystemIO::getOption(SystemIO::arrayToOption($arr_cate1, 'id', 'name'), (int)$arr_cate_other['0']));
        joc()->set_var('option_cate12', SystemIO::getOption($arr_cate2, (int)$arr_cate_other['1']));

        joc()->set_var('news_id', $id);
        if ($row['relate']) {
            $list_relate = $newsObj->getListStore('id IN (' . trim($row['relate'], ',') . ')');
        }
        $text_relate = '';
        if (count($list_relate)) {
            foreach ($list_relate as $relate) {
                $text_relate .= '<li style="margin-left:150px;"><input type="checkbox" value="' . $relate['id'] . '" name="relate[]" checked="checked"/>' . $relate['title'] . '</li>';
            }
        }
        joc()->set_var('text_relate', $text_relate);
        joc()->set_var('title_seo', $row_seo['title_seo'] ? htmlspecialchars($row_seo['title_seo']) : '');
        joc()->set_var('description_seo',
            htmlspecialchars($row_seo['description_seo']) ? $row_seo['description_seo'] : '');
        joc()->set_var('keyword_seo', htmlspecialchars($row_seo['keyword_seo']) ? $row_seo['keyword_seo'] : '');

        joc()->set_var('title', $row['title'] ? htmlspecialchars($row['title']) : '');
        joc()->set_var('description', $row['description'] ? $row['description'] : '');
        joc()->set_var('content', $row['content'] ? $row['content'] : '');
        joc()->set_var('author', $row['author'] ? $row['author'] : '');
        $textTag = '';
        $tag = explode('[]', $row['tag']);
        if ($tag['0']) {
            $tags = explode(',', $tag['0']);
            for ($i = 0; $i < count($tags); ++$i) {
                $textTag .= '<li style="margin-left:150px;">&nbsp;<input type="checkbox" value="' . $tags[$i] . '" name="tag[]" checked="checked"/>' . $tags[$i] . '</li>';
            }
        }

        joc()->set_var('text_relate_tag', $textTag);

        joc()->set_var('origin', $row['origin'] ? $row['origin'] : '');
        if ($row['file']) {
            $arr_file = explode(',', $row['file']);
            $arr_file = array_pad($arr_file, 5, '');

            for ($i = 0; $i < count($arr_file); ++$i) {
                joc()->set_var('file' . ($i + 1), $arr_file[$i]);
                joc()->set_var('file_uploaded_' . ($i + 1),
                    'File đã tải:<a href="/data/file/' . $arr_file[$i] . '">' . $arr_file[$i] . '</a> <a onclick="deleteFile(\'' . ($i + 1) . '\',this)" style="color:red;">Xóa file</a>');
            }
        } else {
            for ($i = 1; $i < 6; ++$i) {
                joc()->set_var('file_uploaded_' . $i, '');
                joc()->set_var('file' . $i, '');
            }
        }
        joc()->set_var('poll', $row['poll_id']);
        joc()->set_var('topic', isset($row['topic_id']) ? $row['topic_id'] : '');
        joc()->set_var('is_video', $row['is_video'] ? 'checked="checked"' : "");
        if ($row['img1']) {
            joc()->set_var('img1',
                $row['img1'] ? '<img id="img1" src="' . IMG::show($newsObj->getPathNews($row['time_created']),
                        $row['img1']) . '" width="280px;" title="Click để thay đổi ảnh" style="border: 2px solid #ebebeb; min-height: 140px; cursor: pointer" /><br/>' : '');
        } else {
            joc()->set_var('img1',
                '<img id="img1" title="Click để thay đổi ảnh" src="webskins/skins/news/images/upload_img1.png" width="280px;" style="border: 2px solid #ebebeb; min-height: 140px; cursor: pointer" /><br/>');
        }

        joc()->set_var('img2',
            $row['img2'] ? '<img id="img2" src="' . IMG::show($newsObj->getPathNews($row['time_created']),
                    $row['img2']) . '" width="120px;" />' : '');
        joc()->set_var('img3',
            $row['img3'] ? '<img id="img2" src="' . IMG::show($newsObj->getPathNews($row['time_created']),
                    $row['img3']) . '" width="100px;" />' : '');
        joc()->set_var('img4', $row['img4'] ? '<img src="' . IMG::show($newsObj->getPathNews($row['time_created']),
                $row['img4']) . '" width="100px;" />' : '');
        joc()->set_var('img5', $row['img5'] ? '<img src="' . IMG::show($newsObj->getPathNews($row['time_created']),
                $row['img5']) . '" width="100px;" />' : '');

        if ($row['continue'] && (!$row['id'])) {
            joc()->set_var('check-disabled', 'checked="checked"');
        } elseif ($row['id']) {
            joc()->set_var('check-disabled', 'disabled="disabled"');
        } else {
            joc()->set_var('check-disabled', '');
        }
        $date_public = '';
        $hour_public = '25';
        $minutes = '00';
        if ($row['time_public'] > time()) {
            $hour_public = (int)date('H', $row['time_public']);
            $date_public = date('d/m/Y', $row['time_public']);
            $minutes = date('i', $row['time_public']);
        }
        $arr_hour = array(
            '0' => 0,
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            '10' => 10,
            '11' => 11,
            '12' => 12,
            '13' => 13,
            '14' => 14,
            '15' => 15,
            '16' => 16,
            '17' => 17,
            '18' => 18,
            '19' => 19,
            '20' => 20,
            '21' => 21,
            '22' => 22,
            '23' => 23
        );

        joc()->set_var('option_hour', SystemIO::getOption($arr_hour, $hour_public));
        joc()->set_var('date_public', $date_public);
        joc()->set_var('minutes', $minutes);

        $a_type_post = array(
            '0' => 'Bài dẫn nguồn',
            '1' => 'Bài thông tấn xã',
            '2' => 'Bài dịch',
            '3' => 'Bài tự viết',
            '4' => 'Bài tổng hợp',
            '5' => 'Tin tự viết',
            '6' => 'Infographic'
        );
        $type_post = '';
        foreach ($a_type_post as $k => $v) {
            if ($k == (int)$row['type_post']) {
                $type_post .= '<input type="radio" name="type_post" value="' . $k . '" checked="checked"> ' . $v . '&nbsp;&nbsp;&nbsp;&nbsp;';
            } else {
                $type_post .= '<input type="radio" name="type_post" value="' . $k . '"> ' . $v . '&nbsp;&nbsp;&nbsp;&nbsp;';
            }
        }
        joc()->set_var('type_post', $type_post);

        if ($row['is_img']) {
            joc()->set_var('is_img', 'checked="checked"');
        } else {
            joc()->set_var('is_img', '');
        }

        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

    private function adminPrivate()
    {
        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_private.htm");
        Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
        Page::registerFile('admin Js', Module::pathSystemJS() . 'admin.js', 'header', 'js');
        Page::registerFile('date Js', Module::pathSystemJS() . 'date.js', 'header', 'js');
        Page::registerFile('jquery date Js', Module::pathSystemJS() . 'jquery.datepicker.min.js', 'header', 'js');
        Page::registerFile('date Css', Module::pathSystemCSS() . 'datepicker.css', 'header', 'css');
        Page::registerFile('popup-css', Module::pathSystemCSS() . 'popup.css', 'header', 'css');
        Page::registerFile('popup-js', JAVASCRIPT_PATH . 'popup.js', 'footer', 'js');
        Page::registerFile('jqDnR', JAVASCRIPT_PATH . 'jqDnR.js', 'footer', 'js');
        $newsObj = new BackendNews();
        $userObj = new User();
        /* Tìm kiếm */
        $list_category = $newsObj->getListCategory('', '', 500, 'id');

        $item_per_page = 20;
        $page_no = SystemIO::get('page_no', 'int', 1);
        if ($page_no < 1) {
            $page_no = 1;
        }
        $stt = ($page_no - 1) * $item_per_page + 1;
        $limit = (($page_no - 1) * $item_per_page) . ',' . $item_per_page;
        $q = SystemIO::get('q', 'def', '');

        joc()->set_var('q', $q);
        $q = trim(str_replace(array('"', "'", '%'), array('', '', ''), $q), ' ');
        $cate_id = SystemIO::get('cate_id', 'int', 0);
        joc()->set_var('option_category',
            SystemIO::getOption(SystemIO::arrayToOption($list_category, 'id', 'name'), $cate_id));
        $wh = 'status=2';
        if (UserCurrent::$current->data['user_name'] != 'namdd') {
            $wh .= ' AND user_id=' . (int)UserCurrent::$current->data['id'];
        }
        if ($q) {
            $wh .= " AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
        }
        if ($cate_id) {
            $wh .= " AND cate_path LIKE '%,{$cate_id},%'";
        }

        $date_begin = SystemIO::get('date_begin', 'def');
        joc()->set_var('date_begin', $date_begin);
        if ($date_begin) {
            $date_begin = strtotime(str_replace('/', '-', $date_begin));
            $wh .= " AND time_created >= {$date_begin}";
        }
        $date_end = SystemIO::get('date_end', 'def');
        joc()->set_var('date_end', $date_end);
        if ($date_end) {
            $date_end = strtotime(str_replace('/', '-', $date_end));
            $wh .= " AND time_created <= {$date_end}";
        }
        $list_news = $newsObj->getListReview($wh, 'id desc', $limit);
        $news_ids = '';
        $user_id = '';
        $cate_ids = '';
        $editor_id = '';
        foreach ($list_news as $_temp) {
            $cate_ids .= $_temp['cate_id'] . ',';
            $user_id .= $_temp['user_id'] . ',';
            if ((int)$_temp['editor_id']) {
                $editor_id .= $_temp['editor_id'] . ',';
            }
        }
        $cate_ids = trim($cate_ids, ',');
        $user_id = trim($user_id, ',');
        $editor_id = trim($editor_id, ',');
        if ($editor_id) {
            $user_id .= ',' . $editor_id;
        }
        $list_censor_user_name = $userObj->userIdToName($user_id);

        $list_path_news = $newsObj->getMultiPathNews($cate_ids);
        joc()->set_block('AdminNews', 'ListRow', 'ListRow');
        $text_html = '';
        global $NEWS_PROPERTY;
        foreach ($list_news as $row) {
            joc()->set_var('title', $row['title']);
            joc()->set_var('nw_id', $row['id']);
            joc()->set_var('path', $list_path_news[$row['cate_id']]);
            joc()->set_var('time_created', date('H:i d-m-Y', $row['time_created']));
            joc()->set_var('description', $row['description']);
            joc()->set_var('censer_user_name', $list_censor_user_name[$row['user_id']]['user_name']);
            joc()->set_var('edit_user_name',
                $list_censor_user_name[$row['editor_id']]['user_name'] ? $list_censor_user_name[$row['editor_id']]['user_name'] : 'N/A');
            joc()->set_var('stt', $stt);
            joc()->set_var('tag', $row['tag'] ? $row['tag'] : 'N/A');
            if ($row['time_public']) {
                joc()->set_var('time_public', 'Xuất bản lúc: ' . date('H:i d/n/y', $row['time_public']));
            } else {
                joc()->set_var('time_public', '');
            }

            $path_img = $newsObj->getPathNews($row['time_created']);
            if ($row['img1']) {
                $src = $row['img1'];
            } elseif ($row['img2']) {
                $src = $row['img2'];
            } elseif ($row['img3']) {
                $src = $row['img3'];
            } else {
                $src = '100x100.jpg';
                $path_img = 'data/news/icons/';
            }
            joc()->set_var('src', IMG::show($path_img, $src));
            joc()->set_var('origin', $row['origin'] ? SystemIO::strLeft($row['origin'], 50) : 'N/A');
            ++$stt;
            joc()->set_var('href',
                'http://congly.vn/' . (isset($list_category[$row['cate_id']]['alias']) ? $list_category[$row['cate_id']]['alias'] : 'no-cate') . '/review/' . Convert::convertLinkTitle($row['title']) . '-' . $row['id'] . '.html?ip=' . base64_encode($_SERVER['REMOTE_ADDR']));
            $text_html .= joc()->output('ListRow');
        }
        joc()->set_var('ListRow', $text_html);
        global $TOTAL_ROWCOUNT;
        $totalRecord = $TOTAL_ROWCOUNT;
        joc()->set_var('total_rowcount', $totalRecord);
        joc()->set_var('paging',
            '<li>Tổng số: ' . $totalRecord . '</li>' . Paging::paging($totalRecord, $item_per_page, 10));
        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

    private function autoDelNewsHome()
    {
        return;
        $newsObj = new BackendNews();
        $list_news = $newsObj->getListData('store_home', 'nw_id,cate_id,time_public,property',
            'time_public < ' . time() - 100 * 86400, 'time_public ASC', '0,100', '', false);
        $list_id = '';
        $time = time() - 100 * 86400;
        foreach ($list_news as $row) {
            if ($row['time_public'] < $time) {
                $list_id .= $row['nw_id'] . ',';
            }
        }
        $list_id = trim($list_id, ',');
        if (strlen($list_id)) {
            $newsObj->deleteMultiHome('nw_id IN(' . $list_id . ')');
            $sql = "UPDATE store SET type =0 WHERE id IN (" . $list_id . ")";
            $newsObj->querySql($sql);
        }
    }

    private function adminNewsComment()
    {
        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_news_comment.htm");
        Page::setHeader("Quản trị tin bài", "Quản trị bình luận", "Quản trị bình luận");
        Page::registerFile('admin Js', Module::pathSystemJS() . 'admin.js', 'header', 'js');
        Page::registerFile('date Js', Module::pathSystemJS() . 'date.js', 'header', 'js');
        Page::registerFile('jquery date Js', Module::pathSystemJS() . 'jquery.datepicker.min.js', 'header', 'js');
        Page::registerFile('date Css', Module::pathSystemCSS() . 'datepicker.css', 'header', 'css');
        Page::registerFile('popup-css', Module::pathSystemCSS() . 'popup.css', 'footer', 'css');
        Page::registerFile('popup-js', JAVASCRIPT_PATH . 'popup.js', 'footer', 'js');
        Page::registerFile('jqDnR', JAVASCRIPT_PATH . 'jqDnR.js', 'footer', 'js');
        $item_per_page = 20;
        $wh = '';
        $commentObj = new Comment();
        $newsObj = new BackendNews();
        $userObj = new User();
        $list_comment = $commentObj->getList($wh, 'time_created desc', $limit);

        //Liệt kê danh sách người duyệt bình luận - begin
        $censor_ids = ',';
        foreach ($list_comment as $_temp) {
            if (!substr_count($censor_ids, ',' . $_temp['censor_id'] . ',')) {
                $censor_ids .= $_temp['censor_id'] . ',';
            }
        }
        $censor_ids = trim($censor_ids, ',');
        if ($censor_ids) {
            $list_censor = $userObj->userIdToName($censor_ids);
        }
        //Liệt kê danh sách người duyệt bình luận - end

        joc()->set_block('AdminNews', 'ListRow', 'ListRow');
        $text_html = '';
        $stt = 0;
        joc()->set_var('total_rowcount', count($list_comment));
        foreach ($list_comment as $row) {

            $news = $newsObj->getStoreOne($row['nw_id']);
//			$news = $newsObj->getStoreOne(22950);
//			$row['nw_id'] = 22950;
//			$commentObj->updateData($row,$row[id]);
            $stt++;
            joc()->set_var('stt', $stt);
            joc()->set_var('id', $row['id']);
            joc()->set_var('title', $row['title']);
            joc()->set_var('content', $row['content']);
            joc()->set_var('email', $row['email']);
            joc()->set_var('censor_id', $list_censor[$row['censor_id']]['user_name']);
//			joc()->set_var('censor_id',$row['censor_id']);
            joc()->set_var('time_created', date('H:i d-m-Y', $row['time_created']));
            joc()->set_var('nw_title', $news['title']);
            joc()->set_var('href', 'http://ngoisao.vn/?app=news&page=detail&id=' . $news['id']);

            $text_html .= joc()->output('ListRow');
        }

        joc()->set_var('ListRow', $text_html);


        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

    private function adminTagMeta()
    {
        ini_set('display_errors', 1);
        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_tag_meta.htm");
        joc()->set_block('AdminNews', 'ListRow', 'ListRow');
        $newsObj = new BackendNews();
        $list_meta_tag = $newsObj->getNews('tag_meta', 'id,tag,meta,link,name', '1=1', 'id DESC', '0,500');
        $txt_html = '';
        foreach ($list_meta_tag as $row) {
            joc()->set_var('id', $row['id']);
            joc()->set_var('link', $row['link']);
            joc()->set_var('tag', $row['tag']);
            joc()->set_var('name', $row['name']);
            joc()->set_var('meta', $row['meta']);
            $txt_html .= joc()->output('ListRow');
        }
        joc()->set_var('ListRow', $txt_html);
        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

    private function adminRecycleBin()
    {
        //ini_set("display_errors", 1);
        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_recycle_bin.htm");
        $newsObj = new BackendNews();
        //ini_set('display_errors',1);
        Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
        Page::registerFile('admin Js', Module::pathSystemJS() . 'admin.js', 'header', 'js');
        Page::registerFile('date Js', Module::pathSystemJS() . 'date.js', 'header', 'js');
        Page::registerFile('jquery date Js', Module::pathSystemJS() . 'jquery.datepicker.min.js', 'header', 'js');
        Page::registerFile('date Css', Module::pathSystemCSS() . 'datepicker.css', 'header', 'css');
        Page::registerFile('popup-css', Module::pathSystemCSS() . 'popup.css', 'header', 'css');
        Page::registerFile('popup-js', JAVASCRIPT_PATH . 'popup.js', 'footer', 'js');
        Page::registerFile('jqDnR', JAVASCRIPT_PATH . 'jqDnR.js', 'footer', 'js');
        $newsObj = new BackendNews();
        $userObj = new User();
        /* Tìm kiếm */
        $list_category = $newsObj->getListCategory('cate_id1=0', '', 100, 'id');

        $item_per_page = 20;
        $page_no = SystemIO::get('page_no', 'int', 1);
        if ($page_no < 1) {
            $page_no = 1;
        }
        $stt = ($page_no - 1) * $item_per_page + 1;
        $limit = (($page_no - 1) * $item_per_page) . ',' . $item_per_page;
        $q = SystemIO::get('q', 'def', '');
        $list_news = $newsObj->getListData('log_delete_from_store', '*', '1=1', 'time_delete DESC ', $limit);
        $news_ids = '';
        $user_id = '';
        $cate_ids = '';
        $editor_id = '';
        foreach ($list_news as $_temp) {
            $cate_ids .= $_temp['cate_id'] . ',';
            $user_id .= $_temp['user_id'] . ',';
            if ((int)$_temp['editor_id']) {
                $editor_id .= $_temp['editor_id'] . ',';
            }
        }
        $cate_ids = trim($cate_ids, ',');
        $user_id = trim($user_id, ',');
        $editor_id = trim($editor_id, ',');
        if ($editor_id) {
            $user_id .= ',' . $editor_id;
        }
        $list_censor_user_name = $userObj->userIdToName($user_id);

        $list_path_news = $newsObj->getMultiPathNews($cate_ids);
        joc()->set_block('AdminNews', 'ListRow', 'ListRow');
        $text_html = '';
        global $NEWS_PROPERTY;
        foreach ($list_news as $row1) {
            $row = json_decode($row1['title'], true);

            joc()->set_var('title', $row['title']);
            joc()->set_var('nw_id', $row['id']);
            joc()->set_var('recycle_id', $row1['id']);
            joc()->set_var('time_delete', $row1['time_delete']);
            joc()->set_var('path', $list_path_news[$row['cate_id']]);
            joc()->set_var('time_created', date('H:i d-m-Y', $row['time_created']));
            joc()->set_var('description', $row['description']);
            joc()->set_var('censer_user_name', $list_censor_user_name[$row['user_id']]['user_name']);
            joc()->set_var('edit_user_name',
                $list_censor_user_name[$row['editor_id']]['user_name'] ? $list_censor_user_name[$row['editor_id']]['user_name'] : 'N/A');
            joc()->set_var('stt', $stt);
            joc()->set_var('tag', $row['tag'] ? $row['tag'] : 'N/A');
            $path_img = $newsObj->getPathNews($row['time_created']);
            if ($row['img1']) {
                $src = $row['img1'];
            } elseif ($row['img2']) {
                $src = $row['img2'];
            } elseif ($row['img3']) {
                $src = $row['img3'];
            } else {
                $src = '100x100.jpg';
                $path_img = 'data/news/icons/';
            }
            joc()->set_var('src', IMG::show($path_img, $src));
            joc()->set_var('origin', $row['origin'] ? SystemIO::strLeft($row['origin'], 50) : 'N/A');
            ++$stt;
            $text_html .= joc()->output('ListRow');
        }
        joc()->set_var('ListRow', $text_html);
        global $TOTAL_ROWCOUNT;
        $totalRecord = $TOTAL_ROWCOUNT;
        joc()->set_var('total_rowcount', $totalRecord);
        joc()->set_var('paging',
            '<li>Tổng số: ' . $totalRecord . '</li>' . Paging::paging($totalRecord, $item_per_page, 20));
        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

    private function newsStatistic()
    {
        //ini_set('display_errors',1);
        joc()->set_file('AdminNews', Module::pathTemplate() . "backend/admin_news_statistic1.htm");
        Page::setHeader("Quản trị tin bài", "Quản trị tin bài", "Quản trị tin bài");
        Page::registerFile('admin Js', Module::pathSystemJS() . 'admin.js', 'header', 'js');
        Page::registerFile('date Js', Module::pathSystemJS() . 'date.js', 'header', 'js');
        Page::registerFile('jquery date Js', Module::pathSystemJS() . 'jquery.datepicker.min.js', 'header', 'js');
        Page::registerFile('date Css', Module::pathSystemCSS() . 'datepicker.css', 'header', 'css');
        $newsObj = new BackendNews();
        $userObj = new User();
        /* Tìm kiếm */
        $list_category = $newsObj->getListCategory('cate_id1=0', '', 100, 'id');
        $list_category1 = $newsObj->getListCategory('cate_id1 > 0 AND cate_id2=0', '', 100, 'id');
        $item_per_page = 1000;
        $page_no = SystemIO::get('page_no', 'int', 1);
        $filter_id = SystemIO::get('filter_id', 'int', 0);
        if ($page_no < 1) {
            $page_no = 1;
        }

        $stt = ($page_no - 1) * $item_per_page + 1;
        $limit = (($page_no - 1) * $item_per_page) . ',' . $item_per_page;
        $q = SystemIO::get('q', 'def', '');
        joc()->set_var('q', $q);
        $label_s = substr($q, 0, 7);
        $q = trim(str_replace(array('"', "'", '%'), array('', '', ''), $q), ' ');
        $cate_id = SystemIO::get('cate_id', 'int', 0);
        $cate_id_2 = SystemIO::get('cate_id_2', 'int', 0);
        $wh = 'time_public > 0 AND time_public < ' . time();
        if ($q) {
            if ($label_s == 'origin:' || $label_s == 'Origin:' || $label_s == 'ORIGIN:') {
                $wh .= " AND origin LIKE '%" . substr($q, 7, strlen($q)) . "%'";
            } else //$wh.=" AND (title LIKE '%{$q}%' OR description LIKE '%{$q}%')";
            {
                $wh .= " AND (title LIKE '%{$q}%')";
            }
        }
        if ($cate_id_2) {
            $wh .= " AND cate_path LIKE '%,{$cate_id_2},%'";
        }
        if ($cate_id) {
            $wh .= " AND cate_path LIKE '%,{$cate_id},%'";
        }

        if ($filter_id) {
            $wh .= ' AND cate_id=' . $filter_id;
        }

        $date_begin = SystemIO::get('date_begin', 'def');
        joc()->set_var('date_begin', $date_begin);
        if ($date_begin) {
            $date_begin = strtotime(str_replace('/', '-', $date_begin));
            $wh .= " AND time_public >= {$date_begin}";
        }
        $date_end = SystemIO::get('date_end', 'def');
        joc()->set_var('date_end', $date_end);
        if ($date_end) {
            $date_end = strtotime(str_replace('/', '-', $date_end));
            $date_end += 86399;
            $wh .= " AND time_public <= {$date_end}";
        }
        $censor_name = SystemIO::get('censor_name', 'def');
        joc()->set_var('censor_name', $censor_name);
        $list_user_id_search = '0';
        if ($censor_name) {
            $_user_id_search = $userObj->userNameToId($censor_name);
            if (count($_user_id_search)) {
                foreach ($_user_id_search as $_temp) {
                    $list_user_id_search .= ',' . $_temp['id'];
                }
            }
            $wh .= ' AND censor_id IN (' . $list_user_id_search . ')';
        }
        $btv_name = SystemIO::get('btv_name', 'def');
        joc()->set_var('btv_name', $btv_name);
        if ($btv_name) {
            $_user_id_search = $userObj->userNameToId($btv_name);
            $list_user_id_search = '0';
            if (count($_user_id_search)) {
                foreach ($_user_id_search as $_temp) {
                    $list_user_id_search .= ',' . $_temp['id'];
                }
            }
            $wh .= ' AND user_id IN (' . $list_user_id_search . ')';
        }
        $array_type_post = array(
            '0' => 'Copy',
            '1' => 'Thông tấn xã',
            '2' => 'Dịch',
            '3' => 'Tự viết',
            '4' => 'Tổng hợp'
        );
        if (!$date_begin && !$date_end) {
            $wh .= ' AND time_public > ' . (time() - 7 * 86399);
        }
//echo $wh;
        /* Lay thông tin news */
        $aray_date = array();
        $list_news = $newsObj->getListStore($wh, 'time_public desc', $limit);
        $list_user_id = '';
        $aray_date_total = array();
        $array_total_user = array();
        $array_total_user_date = array();
        foreach ($list_news as $row) {
            if ((int)$row['type_post'] == 0) {
                $array['0'] = @$array['0'] + 1;
            }
            if ((int)$row['type_post'] == 1) {
                $array['1'] = @$array['1'] + 1;
            }
            if ((int)$row['type_post'] == 2) {
                $array['2'] = @$array['2'] + 1;
            }
            if ((int)$row['type_post'] == 3) {
                $array['3'] = @$array['3'] + 1;
            }
            if ((int)$row['type_post'] == 4) {
                $array['4'] = @$array['4'] + 1;
            }
            $list_user_id .= $row['user_id'] . ',';
            $aray_date[date('d/m/Y', $row['time_public'])][$row['user_id']][] = $row['title'];
            $array_total_user[$row['user_id']] = (int)@$array_total_user[$row['user_id']] + 1;
            $array_total_user_date[$row['user_id']][date('d/m/Y',
                $row['time_public'])] = (int)@$array_total_user_date[$row['user_id']][date('d/m/Y',
                    $row['time_public'])] + 1;
            $aray_date_total[date('d/m/Y', $row['time_public'])] = (int)@$aray_date_total[date('d/m/Y',
                    $row['time_public'])] + 1;
        }
        $array_date_thu = array(2 => 'Hai', 3 => 'Ba', 4 => 'Tư', 5 => 'Năm', 6 => 'Sáu', 7 => 'Bảy');

        $list_user_id = trim($list_user_id, ',');
        $list_user_name = $userObj->userIdToName($list_user_id);
        $st = 'Tổng bài đã làm được trong tuần là: <b>' . count($list_news) . '</b>';
        $st .= '<br/>Bài copy: <b>' . $array['0'] . '</b><br/>Bài dịch: <b>' . $array['2'] . '</b><br/>Tự viết: <b>' . $array['3'] . '</b><br/>Bài Thông tấn xã: ' . $array['1'] . '<br/>Tổng hợp: ' . (int)$array['4'];
        foreach ($array_total_user as $user_id => $t) {
            $st .= '<br/>Biên tập viên <b>' . $list_user_name[$user_id]['user_name'] . '</b> làm được <font color="red" style="font-weight:bold;">' . $t . '</font> bài';
            $tem = $array_total_user_date[$user_id];
            foreach ($tem as $date => $tt) {
                $date_day = date('w', strtotime(str_replace('/', '-', $date)));
                if ($date_day == '0') {
                    $text_date = 'Chủ nhật';
                } else {
                    $text_date = 'Thứ ' . ($array_date_thu[$date_day + 1]);
                }

                $st .= '      <br/>- Ngày ' . $date . '( ' . $text_date . ') làm được ' . $tt . ' bài ';
            }
        }
        joc()->set_var('statistic', $st);
        $text_html = '';

        foreach ($aray_date as $date => $array_date_user) {
            $s .= '<div style="margin:0px 0px 0px 5px"><strong>Ngày ' . $date . ' làm được ' . $aray_date_total[$date] . ' Bài</strong></div>';
            foreach ($array_date_user as $user_ids => $a) {
                $s .= '<div style="margin:0px 0px 0px 20px;clear:bold">Biên tập viên:<b>' . $list_user_name[$user_ids]['user_name'] . '</b><br/></div>';
                $s .= '<div style="margin:0px 0px 0px 35px">';
                for ($j = 0; $j < count($a); ++$j) {
                    $s .= '  ' . ($j + 1) . '. ' . $a[$j] . '<br/>';
                }
                $s .= '</div>';
            }
        }
        joc()->set_var('s', $s);
        /* List news */
//ini_set("display_errors", 1);
        $list_hit = $newsObj->getListHit('store_hit', 'nw_id,hit', 'time_created > ' . (time() - 86400), 'hit DESC',
            '10', 'nw_id');
        $hit_news_id = '';
        foreach ($list_hit as $temp) {
            $hit_news_id .= $temp['nw_id'] . ',';
        }
        $hit_news_id .= trim($hit_news_id, ',');
        $list_news_hit = $newsObj->getListStore('id IN (' . $hit_news_id . ')', 'time_public desc', '10', 'id');
        $txt_hit = '';
        $i = 1;
        foreach ($list_hit as $row) {
            $news = $list_news_hit[$row['nw_id']];
            $txt_hit .= $i . '. ' . $news['title'] . ' Lượt view ' . $row['hit'] . ' - <strong>' . $list_user_name[$news['user_id']]['user_name'] . '</strong> Ngày ' . date('d/n/Y H:i',
                    $news['time_public']) . '<br/>';
            ++$i;
        }
        joc()->set_var('txt_hit', $txt_hit);
        $html = joc()->output("AdminNews");
        joc()->reset_var();
        return $html;
    }

}
