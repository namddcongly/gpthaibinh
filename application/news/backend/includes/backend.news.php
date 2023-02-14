<?php
/**
 *  Lop ket noi co so du lieu trong backend cua the thong tin tuc , thuc hien cac nghiep vu
 *    Class lam viec voi database su dung MySQLi
 *

 */
if (defined(IN_JOC)) {
    die("Direct access not allowed!");
}
require_once ROOT_PATH . 'system/kernel/includes/single.database.php';
require_once UTILS_PATH . 'cache.file.php';
require_once UTILS_PATH . 'convert.php';

class BackendNews
{
    private static $dbNews;
    public static $review_id;

    function __construct()
    {
        $config = array(
            'username' => 'etstech_dev',
            'password' => 'sX5X$qtawp@kYx&^',
            'host' => 'localhost',
            'host_reserve' => 'localhost',
            'dbname' => 'news'
        );
        #$config = array('username' => 'root', 'password' => 'ePxVnTfq9uTU2nUY', 'host' => 'localhost', 'host_reserve' => 'localhost', 'dbname' => 'congly_mssql_fe');
        self::$dbNews = new SingleDatabase($config);
    }

    /*
     * Cong tac vien insert vao bang review
     * */
    public function insertReview($arrNewData)
    {
        return self::$dbNews->insert('review', $arrNewData);
    }

    public function insertData($table, $data)
    {
        return self::$dbNews->insert($table, $data);
    }

    /*
     * Lấy chi tiết một tin từ bang review
     */
    public function getReviewOne($news_id)
    {
        settype($news_id, 'int');
        if (!$news_id) {
            return array();
        }
        return self::$dbNews->selectOne('review',
            'id,poll_id,is_video,relate,user_id,cate_id,cate_path,cate_other,is_img,title,description,content,img1,img2,img3,img4,province_id,district_id,status,type,origin,author,tag,file,arrange,censor_id,reason_return,time_public,time_created,editor_id,type_post,topic_id',
            'id=' . $news_id);
    }

    public function getSEOOne($wh)
    {
        return self::$dbNews->selectOne('seo', 'id,nw_id,title_seo,keyword_seo,description_seo', $wh);
    }

    /**
     * Update lại review
     * @param unknown_type $news_id
     * @param unknown_type $arrNewData
     */

    public function updateReview($news_id, $arrNewData)
    {
        settype($news_id, 'int');
        $this->log('Sửa bài từ bảng review', $news_id);
        return self::$dbNews->update('review', $arrNewData, 'id=' . $news_id);
    }

    /**
     * Xóa một review
     * @param $news_id
     */
    public function delReview($news_id)
    {
        settype($news_id, 'int');
        $user_info = UserCurrent::$current->data;
        $review = $this->getReviewOne($news_id);
        $this->log('Xóa bài từ bảng review', $news_id, $user_info['id']);
        $data = array(
            'id' => $news_id,
            'user_id' => $user_info['id'],
            'title' => json_encode($review),
            'time_delete' => date('Y-m-d H:i:s')
        );
        $this->insertData('log_delete_from_store', $data);
        return self::$dbNews->delete('review', 'id=' . $news_id, 1);
    }

    /**
     * Đọc chi tiết một bản tin
     * @param $news_id
     */
    public function getStoreOne($news_id)
    {
        settype($news_id, 'int');
        if (!$news_id) {
            return array();
        }
        return self::$dbNews->selectOne('store',
            'id,user_id,cate_id,cate_path,cate_other,title,description,img1,img2,img3,img4,province_id,district_id,status,type,origin,author,tag,file,arrange,censor_id,time_public,time_created,is_video,is_img,relate,editor_id,type_post,topic_id,poll_id',
            'id=' . $news_id);
    }

    /**
     * Lấy nôi dung chi tiết của một bản tin
     * @param $news_id
     */
    public function getContentOne($news_id)
    {
        settype($news_id, 'int');
        $row = self::$dbNews->selectOne('store_content', 'nw_id,content', 'nw_id=' . $news_id);
        return $row['content'];
    }

    public function getListContent($news_ids)
    {
        if (!$news_ids) {
            return array();
        }
        return self::$dbNews->select('store_content', 'nw_id,content', 'nw_id IN("' . $news_ids . '")', null, null,
            'nw_id');
    }

    /**
     * insert bang tin vao trang chu dong thoi thuc hien insert vao cac bang view,province,hit,content,search
     * @param $news_id
     * @param $type_img_show
     * @param $property
     * @param $time_public
     * Can update lai cache
     */
    public function insertReviewToHome($news_id, $censor_id, $time_public, $type_img_show = 1, $property = 0)
    {
        $this->log('Set bài vào trang chủ từ bảng review', $news_id, $censor_id);
        $row = $this->getReviewOne($news_id);
        if (!count($row)) {
            return false;
        }
        $arrNewsStore = $row;
        unset($arrNewsStore['id']);
        unset($arrNewsStore['content']);
        unset($arrNewsStore['reason_return']);
        $arrNewsStore['type'] = 1;// la tin trang chủ
        $arrNewsStore['censor_id'] = $censor_id;
        $arrNewsStore['time_public'] = $time_public;
        $id_news_store = self::$dbNews->insert('store', $arrNewsStore); // insert vao kho
        if (!$id_news_store) {
            return false;
        }
        $this->updateData('seo', array('nw_id' => $id_news_store), 'id=' . $news_id);
        $arrNewsHome = array(
            'nw_id' => $id_news_store,
            'censor_id' => $censor_id,
            'cate_id' => $row['cate_id'],
            'cate_path' => $row['cate_path'],
            'cate_other' => $row['cate_other'],
            'title' => $row['title'],
            'description' => $row['description'],
            'tag' => $row['tag'],
            'img1' => $row['img1'],
            'img2' => $row['img2'],
            'img3' => $row['img3'],
            'img4' => $row['img4'],
            'relate' => $row['relate'],
            'type_post' => $row['type_post'],
            'type_img_show' => $type_img_show,
            'property' => $property | NEWS_NEW,
            'is_video' => $row['is_video'],
            'time_created' => $row['time_created'],
            'time_public' => $time_public
        );

        $id_news_home = self::$dbNews->insert('store_home', $arrNewsHome); //insert vao home
        /*insert vao search*/
        $arrNewsSearch = array(
            'nw_id' => $id_news_store,
            'cate_id' => $row['cate_id'],
            'cate_path' => $row['cate_path'],
            'time_public' => $time_public,
            'keyword' => Convert::convertUtf8ToSMS($row['title'] . ' ' . $row['description'] . ' ' . $row['tag']) . ' ' . Convert::convertUtf8ToTelex($row['title'] . ' ' . $row['description'] . ' ' . $row['tag'])

        );
        $id_news_search = self::$dbNews->insert('search', $arrNewsSearch);
        $id_news_hit = self::$dbNews->insert('store_hit', array(
            'nw_id' => $id_news_store,
            'hit' => 0,
            'user_id' => $row['user_id'],
            'time_created' => $row['time_created'],
            'cate_path' => $row['cate_path'],
            'type_post' => $row['type_post']
        ));
        $id_news_content = self::$dbNews->insert('store_content',
            array('nw_id' => $id_news_store, 'content' => $row['content']));
        //if($id_news_content)
        self::$dbNews->delete('review', 'id=' . $news_id, 1);
        self::$review_id = $id_news_store;
        return array(
            'store_id' => $id_news_store,
            'home_id' => $id_news_home,
            'view_id' => 1,
            'search_id' => $id_news_search,
            'hit_id' => $id_news_hit,
            'content_id' => $id_news_content
        );
    }

    /**
     * Duyet mot tin vao kho tu bang review
     * @param $news_id
     * @param $censor_id
     * @param $time_public
     * Can update lai cache
     */
    public function insertReviewToStore($news_id, $censor_id, $time_public, $property = 0)
    {

        $this->log('Duyệt tin vào kho ', $news_id);
        $row = $this->getReviewOne($news_id);
        if (!$row['title']) {
            return false;
        }
        $arrNewsStore = $row;
        unset($arrNewsStore['id']);
        unset($arrNewsStore['content']);
        unset($arrNewsStore['reason_return']);
        $arrNewsStore['censor_id'] = $censor_id;

        if ($row['time_public'] > time()) {
            $arrNewsStore['time_public'] = $row['time_public'];
        } else {
            $arrNewsStore['time_public'] = $time_public;
        }

        /*Thuc hien tim kiem cac tin lien quan va them*/


        $id_news_store = self::$dbNews->insert('store', $arrNewsStore); // insert vao kho
        if (!$id_news_store) {
            return false;
        }
        $this->updateData('seo', array('nw_id' => $id_news_store), 'id=' . $news_id);
        $arrNewsSearch = array(
            'nw_id' => $id_news_store,
            'cate_id' => $row['cate_id'],
            'cate_path' => $row['cate_path'],
            'keyword' => Convert::convertUtf8ToSMS($row['title'] . ' ' . $row['description'] . ' ' . $row['tag']) . ' ' . Convert::convertUtf8ToTelex($row['title'] . ' ' . $row['description'] . ' ' . $row['tag'])
        );
        $arrNewsSearch['keyword'] = str_replace(array('"', '”', "'", '“'), array('', '', '', ''),
            $arrNewsSearch['keyword']);
        $id_news_search = self::$dbNews->insert('search', $arrNewsSearch);
        $id_news_hit = self::$dbNews->insert('store_hit', array(
            'nw_id' => $id_news_store,
            'hit' => 1,
            'time_created' => $row['time_created'],
            'cate_path' => $row['cate_path']
        ));
        $id_news_content = self::$dbNews->insert('store_content',
            array('nw_id' => $id_news_store, 'content' => $row['content']));
        //if($id_news_content)
        self::$dbNews->delete('review', 'id=' . $news_id, 1);
        return array(
            'store_id' => $id_news_store,
            'search_id' => $id_news_search,
            'hit_id' => $id_news_hit,
            'content_id' => $id_news_content
        );
    }

    /**
     * Insert vào bảng home tu kho dư liệu
     * @param $news_id
     * @param $censor_id
     * @param $property
     * Can update lai cache
     */
    function insertHomeToStore($news_id, $censor_id, $property)
    {
        $this->log('Set bài vào trang chủ', $news_id);
        $row = $this->getStoreOne($news_id);
        $arrNewsHome = array(
            'nw_id' => $news_id,
            'censor_id' => $censor_id,
            'cate_id' => $row['cate_id'],
            'cate_path' => $row['cate_path'],
            'cate_other' => $row['cate_other'],
            'title' => $row['title'],
            'description' => $row['description'],
            'relate' => $row['relate'],
            'tag' => $row['tag'],
            'img1' => $row['img1'],
            'img2' => $row['img2'],
            'img3' => $row['img3'],
            'img4' => $row['img4'],
            'type_post' => $row['type_post'],
            'type_img_show' => 1,
            'property' => $property | NEWS_NEW,
            'is_video' => $row['is_video'],
            'is_img' => $row['is_img'],
            'time_created' => $row['time_created'],
            'time_public' => $row['time_public'],
            //'time_public'	=>($row['time_public'] > time()) ? $row['time_public'] : time()
        );

        $check_exit = $this->countRecord('store_home', 'nw_id = ' . $news_id);
        if ($check_exit) {
            $this->updateData('store_home', array('property' => $property | NEWS_NEW), 'nw_id=' . $news_id);
            return true;
        } else {
            if (self::$dbNews->insert('store_home', $arrNewsHome)) {
                $this->updateData('store', array('type' => 1), 'id=' . $news_id);
                return true;
            } //insert vao home
        }

        return false;

    }

    /**
     * Thực hiện chuyển một bản tin từ kho về bảng review để thực hiện sửa bài
     * @param $news_id
     * @param $reason : Ly do tra bai
     * Can update lai cache
     */
    public function convertStoreToReview($news_id, $reason = '', $status = 1)
    {
        
        $this->log('Trả bài từ kho, với lý do ' . $reason, $news_id);
        $row = $this->getStoreOne($news_id);
        unset($row['id']);
        if (!count($row)) {
            return false;
        }
        $row['content'] = $this->getContentOne($news_id);
        $row['status'] = $status;
        $row['reason_return'] = $reason;
        $row['date_time_return'] = date('Y-m-d H:i:s');
        if ($id_insert = $this->insertReview($row)) {
            $this->updateData('seo', array('id' => $id_insert, 'nw_id' => '0'), 'nw_id=' . $news_id);
            self::$dbNews->delete('store_home', 'nw_id=' . $news_id, 1);
            self::$dbNews->delete('store_content', 'nw_id=' . $news_id, 1);
            self::$dbNews->delete('store', 'id=' . $news_id, 1);
            self::$dbNews->delete('store_hit', 'nw_id=' . $news_id, 1);
            self::$dbNews->delete('region_store', 'nw_id=' . $news_id, 1);
            self::$dbNews->delete('search', 'nw_id=' . $news_id, 1);
            return true;
        }

        return false;
    }

    /**
     * Lấy danh sách các bản tin trong store
     * @param $wh
     * @param $order
     * @param $limit
     * @param $key
     * @param $pagging
     */
    public function getListStore($wh = '', $order = '', $limit = '0,20', $key = '')
    {
        $listField = '*';
        global $TOTAL_ROWCOUNT;
        $where = '';
        if ($wh) {
            $where = "WHERE {$wh}";
        }
        if ($order) {
            $sql = "SELECT SQL_CALC_FOUND_ROWS {$listField} FROM store {$where} ORDER BY {$order} LIMIT {$limit}";
        } else {
            $sql = "SELECT SQL_CALC_FOUND_ROWS {$listField} FROM store {$where} LIMIT {$limit}";
        }
        self::$dbNews->query($sql);
        $result = self::$dbNews->fetchAll($key);
        self::$dbNews->query("SELECT FOUND_ROWS() AS total_rows");
        $row = self::$dbNews->fetch();
        $TOTAL_ROWCOUNT = (int)$row['total_rows'];
        return $result;
    }

    /**
     * Lấy danh sách các bản tin trong review
     * @param $wh
     * @param $order
     * @param $limit
     * @param $key
     * @param $pagging
     */
    public function getListReview($wh = '', $order = '', $limit = '0,20', $key = '')
    {
        $listField = 'id,user_id,cate_id,cate_path,cate_other,title,description,content,img1,img2,img3,img4,province_id,district_id,status,type_post,type,origin,author,tag,file,arrange,censor_id,reason_return,time_public,time_created,editor_id,date_time_push_pendding,date_time_return';
        global $TOTAL_ROWCOUNT;
        if ($wh) {
            $where = "WHERE {$wh}";
        }
        if ($order) {
            $sql = "SELECT SQL_CALC_FOUND_ROWS {$listField} FROM review {$where} ORDER BY {$order} LIMIT {$limit}";
        } else {
            $sql = "SELECT SQL_CALC_FOUND_ROWS {$listField} FROM review {$where} LIMIT {$limit}";
        }
        self::$dbNews->query($sql);
        $result = self::$dbNews->fetchAll($key);
        self::$dbNews->query("SELECT FOUND_ROWS() AS total_rows");
        $row = self::$dbNews->fetch();
        $TOTAL_ROWCOUNT = (int)$row['total_rows'];
        return $result;
    }

    /**
     * Lay danh sach cac tin o bang khac nhau
     * @param unknown_type $table
     * @param unknown_type $listField
     * @param unknown_type $wh
     * @param unknown_type $order
     * @param unknown_type $limit
     * @param unknown_type $key
     */
    public function getListData($table, $listField, $wh = '', $order = '', $limit = '0,20', $key = '', $paging = true)
    {
        if ($paging) {
            $where = '';
            global $TOTAL_ROWCOUNT;
            if ($wh) {
                $where = "WHERE {$wh}";
            }
            if ($order) {
                $sql = "SELECT SQL_CALC_FOUND_ROWS {$listField} FROM {$table} {$where} ORDER BY {$order} LIMIT {$limit}";
            } else {
                $sql = "SELECT SQL_CALC_FOUND_ROWS {$listField} FROM {$table} {$where} LIMIT {$limit}";
            }
            self::$dbNews->query($sql);
            $result = self::$dbNews->fetchAll($key);
            self::$dbNews->query("SELECT FOUND_ROWS() AS total_rows");
            $row = self::$dbNews->fetch();
            $TOTAL_ROWCOUNT = (int)$row['total_rows'];
        } else {
            if ($wh) {
                $where = "WHERE {$wh}";
            }
            if ($order) {
                $sql = "SELECT {$listField} FROM {$table} {$where} ORDER BY {$order} LIMIT {$limit}";
            } else {
                $sql = "SELECT {$listField} FROM {$table} {$where} LIMIT {$limit}";
            }
            self::$dbNews->query($sql);
            $result = self::$dbNews->fetchAll($key);
        }
        return $result;
    }

    function getListHit($table, $listField, $wh = '', $order = null, $limit = '0,30', $key = 'id')
    {
        return self::$dbNews->select($table, $listField, $wh, $order, $limit, $key);
    }

    /**
     * Lấy danh sách các bản tin home
     * @param $wh
     * @param $order
     * @param $limit
     * @param $key
     * @param $pagging
     */
    public function getListHome($wh = '', $order = '', $limit = '0,20', $key = '')
    {
        $listField = 'id,nw_id,cate_id,arrange,censor_id,type_post,cate_path,title,description,tag,img1,img2,img3,img4,type_img_show,property,time_public,time_created';
        global $TOTAL_ROWCOUNT;
        if ($wh) {
            $where = "WHERE {$wh}";
        }
        if ($order) {
            $sql = "SELECT SQL_CALC_FOUND_ROWS {$listField} FROM store_home {$where} ORDER BY {$order} LIMIT {$limit}";
        } else {
            $sql = "SELECT SQL_CALC_FOUND_ROWS {$listField} FROM store_home {$where} LIMIT {$limit}";
        }
        self::$dbNews->query($sql);
        $result = self::$dbNews->fetchAll($key);
        self::$dbNews->query("SELECT FOUND_ROWS() AS total_rows");
        $row = self::$dbNews->fetch();
        $TOTAL_ROWCOUNT = (int)$row['total_rows'];
        return $result;
    }

    public function getListNewsHit($news_ids)
    {
        if (!$news_ids) {
            return false;
        }
        return self::$dbNews->select('store_hit', 'hit,nw_id', "nw_id IN ({$news_ids})", null, null, 'nw_id');
    }

    public function insertSeo($array)
    {
        return self::$dbNews->insert('seo', $array);
    }

    public function readSeo($wh)
    {
        return self::$dbNews->selectOne('seo', '*', $wh);
    }

    /**
     * Lấy đường dẫn của một tin khi biết id tin tưc hoặc nhóm tin
     * @param unknown_type $cate_id
     * @param unknown_type $news_id
     */
    public function getOnePathNews($cate_id = 0, $news_id = 0)
    {
        settype($cate_id, 'int');
        settype($news_id, 'int');
        if (!($cate_id || $news_id)) {
            return false;
        }
        if ($cate_id) {
            $cate = self::$dbNews->selectOne('category',
                'name,cate_id1,cate_id2,cate_id3,cate_id4,cate_name1,cate_name2,cate_name3,cate_name4',
                'id=' . $cate_id);
            if ($cate['cate_id4']) {
                return $cate['cate_name1'] . '<font color="#000000">&raquo;</font>' . $cate['cate_name2'] . '<font color="#000000">&raquo;</font>' . $cate['cate_name3'] . '<font color="#000000">&raquo;</font>' . $cate['cate_name4'] . '<font color="#000000">&raquo;</font>' . $cate['name'];
            } elseif ($cate['cate_id3']) {
                return $cate['name'] . ' <font color="#000000">&raquo;</font> ' . $cate['cate_name1'] . ' <font color="#000000">&raquo;</font> ' . $cate['cate_name2'] . ' <font color="#000000">&raquo;</font> ' . $cate['cate_name3'] . ' <font color="#000000">&raquo;</font> ' . $cate['name'];
            } elseif ($cate['cate_id2']) {
                return $cate['name'] . ' <font color="#000000">&raquo;</font> ' . $cate['cate_name1'] . ' <font color="#000000"> &raquo;</font> ' . $cate['cate_name2'] . ' <font color="#000000">&raquo;</font> ' . $cate['name'];
            } elseif ($cate['cate_id1']) {
                return $cate['cate_name1'] . ' &raquo; ' . $cate['name'];
            } else {
                return $cate['name'];
            }
        } else {
            $news = self::$dbNews->selectOne('store', 'id,cate_path', 'id=' . $news_id);
            $cate_ids = trim($news['cate_path'], ',');
            if (!$cate_ids) {
                return false;
            }
            $cate = self::$dbNews->select('category', 'name,id', "id IN ($cate_ids)");
            $path = '';
            foreach ($cate as $_temp) {
                $path .= $_temp['name'] . '<font color="#000000">&raquo;</font>';
            }
            return trim($path, '<font color="#000000">&raquo;</font>');
        }
    }

    /**
     * Lấy nhiều đường dẫn khi biết một danh sách các danh muc hoac mot danh sach cac id
     * @param $cate_ids
     * @param $news_ids
     */
    public function getMultiPathNews($cate_ids = '', $news_ids = '')
    {

        if (!($cate_ids || $news_ids)) {
            return false;
        }
        $path = array();
        if ($cate_ids) {
            $cates = self::$dbNews->select('category',
                'id,name,cate_id1,cate_id2,cate_id3,cate_id4,cate_name1,cate_name2,cate_name3,cate_name4',
                'property & 1 = 1');

            foreach ($cates as $cate) {
                if ($cate['cate_id4']) {
                    $path[$cate['id']] = $cate['cate_name1'] . ' <font color="#000000">&raquo;</font> ' . $cate['cate_name2'] . ' <font color="#000000">&raquo;</font> ' . $cate['cate_name3'] . ' <font color="#000000">&raquo;</font> ' . $cate['cate_name4'] . ' <font color="#000000">&raquo;</font> ' . $cate['name'];
                } elseif ($cate['cate_id3']) {
                    $path[$cate['id']] = $cate['cate_name1'] . ' <font color="#000000">&raquo;</font> ' . $cate['cate_name2'] . ' <font color="#000000">&raquo;</font> ' . $cate['cate_name3'] . ' <font color="#000000">&raquo;</font> ' . $cate['name'];
                } elseif ($cate['cate_id2']) {
                    $path[$cate['id']] = $cate['cate_name1'] . ' <font color="#000000">&raquo;</font> ' . $cate['cate_name2'] . ' <font color="#000000">&raquo;</font> ' . $cate['name'];
                } elseif ($cate['cate_id1']) {
                    $path[$cate['id']] = $cate['cate_name1'] . '<font color="#000000">&raquo;</font> <a title="' . $cate['id'] . '">' . $cate['name'] . '</a>';
                } else {
                    $path[$cate['id']] = $cate['name'];
                }
            }
        } else {
            $list_news = self::$dbNews->select('news', 'id,cate_path,cate_id', 'id IN(' . $news_ids . ')', '', '',
                'id');
            $arr_cate_id_news_id = array();
            foreach ($list_news as $_temp) {
                $arr_cate_id_news_id[$_temp['id']] = trim($_temp['cate_path'], ',');
            }
            return $path;
        }
        return $path;
    }

    /**
     * Xóa một bản tin ở trang chủ
     * @param $news_ids
     * Can update lai cache
     */
    public function deleteHome($news_id)
    {
        $this->log('Xóa bài trong trang chủ', $news_id);
        $this->updateData('store', array('type' => '0'), 'id=' . $news_id);
        return self::$dbNews->delete('store_home', 'nw_id=' . $news_id, 1);
    }

    /**
     * Xóa nhiều bản tin
     * @param unknown_type $wh
     * Can update lai cache
     */
    public function deleteMultiHome($wh)
    {
        if (!$wh) {
            return false;
        }
        $this->log('Xóa nhiều bài trong trang chủ', $wh);
        return self::$dbNews->delete('store_home', $wh, 1000);
    }

    /**
     * Set thuộc tính của bản tin
     * @param $table
     * @param $wh
     * @param $set_property
     * @param $unset_property
     * Can update lai cache
     */
    public function setProperty($table, $wh, $set_property, $unset_property = 0)
    {
        if (!$wh) {
            return false;
        }
        settype($set_property, 'int');
        settype($unset_property, 'int');
        $this->log('Set thuộc tính của bài trong bảng ' . $table, $wh);
        $sql = "UPDATE {$table} SET property= (property | {$set_property})&~{$unset_property} WHERE {$wh}";
        return self::$dbNews->query($sql);
    }

    /**
     * update
     * @param $table
     * @param $data
     * @param $where
     * Can update lai cache
     */
    public function updateData($table, $data, $where)
    {
        if (count($data) == 2 && $table == 'store') {
            $this->log('Xuất bản bài ' . $table, $where);
        } else {
            $this->log('Sửa bài trong bảng ' . $table, $where);
        }
        if ($data['reason_return']) {
            $this->log('Trả bài trong bảng ' . $table, $where);
        }

        return self::$dbNews->update($table, $data, $where);
    }

    /**
     *
     * @param unknown_type $table
     * @param unknown_type $wh
     * @param unknown_type $limit
     * Can update lai cache
     */
    public function delData($table, $wh, $limit = 1)
    {
        $this->log('Xóa bài trong bảng ' . $table, $wh);
        return self::$dbNews->delete($table, $wh, $limit);
    }

    /**
     * Get danh muc
     * @param $w
     * @param $order
     * @param $limit
     * @param $key
     */
    public function getListCategory($wh = '', $order = '', $limit = '0,200', $key = '')
    {
        $listField = 'id,name,name_display,cate_id1,cate_id2,cate_id3,cate_id4,cate_name1,cate_name2,cate_name3,cate_name4,alias,title,keyword,description,arrange,level,property,icon,layout,block_home,order_cate,number';
        if ($wh) {
            $wh = $wh . " AND property & 1=1";
        } else {
            $wh = 'property & 1 = 1';
        }
        if ($order) {
            $sql = "SELECT {$listField} FROM category " . ($wh != "" ? "WHERE " . $wh : "") . " ORDER BY {$order} LIMIT {$limit}";
        } else {
            $sql = "SELECT {$listField} FROM category " . ($wh != "" ? "WHERE " . $wh : "") . " LIMIT {$limit}";
        }

        self::$dbNews->query($sql);
        $result = self::$dbNews->fetchAll($key);
        return $result;
    }

    public function querySql($sql)
    {
        return self::$dbNews->query($sql);
    }

    public function getProvince($is_cache = true)
    {

        if ($is_cache) {
            $Cache = new CacheFile();
            $data = $Cache->get(md5('province'), '', CACHE_FILE_PATH . 'news' . DS, 3600);
            if ($data) {
                return $data;
            } else {
                $result = self::$dbNews->select('province', 'id,name', null, null, null, 'id');
            }
        }
        $result = self::$dbNews->select('province', 'id,name', null, null, null, 'id');
        if ($is_cache) {
            $Cache->set(md5('province'), $result, 3600, '', CACHE_FILE_PATH . 'news' . DS);
        }
        return $result;

    }

    public function countRecord($table, $where = null, $key = 'id')
    {
        return self::$dbNews->count($table, $where, $key);
    }

    /**
     * Danh sach vung
     * @param unknown_type $wh
     * @param unknown_type $order
     * @param unknown_type $limit
     * @param unknown_type $key
     */
    public function getListRegion($wh = null, $order = null, $limit = null, $key = '')
    {
        if (!$wh) {
            $wh = null;
        }
        if (!$order) {
            $order = null;
        }
        if (!$limit) {
            $limit = null;
        }
        return self::$dbNews->select('region', 'id,name,description,property', $wh, $order, $limit, $key);
    }

    /**
     * Danh sach vung va danh muc
     * @param $wh
     * @param $order
     * @param $limit
     * @param $key
     */
    public function getListRegionCate($wh = null, $order = null, $limit = null, $key = '')
    {
        return self::$dbNews->select('region_category',
            'id,cate_id,region_id,skins_type,number_record,arrange,property', $wh, $order, $limit, $key);
    }

    public function getPathNews($time)
    {
        return 'news/' . date('Y/n/j', $time) . '/';
    }

    /**
     * Map tin tuc vao vung
     * @param unknown_type $arr_news
     * @param unknown_type $arr_region
     * Can update lai cache
     */
    public function mapNewsRegion($arr_news, $arr_region)
    {

        if (!(count($arr_news) && count($arr_region))) {
            return false;
        }
        $sql = "INSERT INTO region_store (nw_id,region_id, arrange,property) VALUES ";
        $value = '';
        foreach ($arr_region as $region_id) {
            foreach ($arr_news as $news_id) {
                $value .= "({$news_id},{$region_id},0,0),";
            }
        }
        $value = trim($value, ',');
        if ($value) {
            $sql = $sql . $value;
            return self::$dbNews->query($sql);
        }
        $this->log('Map tin vao vùng', $value);
        return false;
    }

    public function getNewsMapRegion($wh = '', $order = '', $limit = '')
    {
        if ($wh) {
            $wh = 'WHERE ' . $wh;
        }
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }
        if ($limit) {
            $limit = 'LIMIT ' . $limit;
        }
        $sql = "SELECT nw_id,region_id,arrange property FROM region_store {$wh} {$order} {$limit}";
        self::$dbNews->query($sql);
        $result = self::$dbNews->fetchAll();
        return $result;
    }

    public function detail($id)
    {
        $data = array();
        settype($id, 'int');
        $content = self::$dbNews->selectOne('store_content', 'content', "nw_id=" . $id);
        $content = isset($content['content']) ? $content['content'] : null;
        $data = self::$dbNews->selectOne('store', '*', "id=" . $id);
        $data['content'] = $content;
        return $data;

    }

    /**
     * Xoa mot ban ghi o kho
     * @param unknown_type $id
     * Can update lai cache
     */
    public function deleteStore($id)
    {
        $detail = $this->detail($id);
        $user_info = UserCurrent::$current->data;
        $data = array(
            'id' => $id,
            'user_id' => $user_info['id'],
            'title' => json_encode($detail),
            'time_delete' => date('Y-m-d H:i:s')
        );
        $this->insertData('log_delete_from_store', $data);
        $this->log('Xóa bài trong kho', $id);
        self::$dbNews->delete('store_home', 'nw_id=' . $id, 1);
        self::$dbNews->delete('store_content', 'nw_id=' . $id, 1);
        self::$dbNews->delete('store_hit', 'nw_id=' . $id, 1);
        self::$dbNews->delete('search', 'nw_id=' . $id, 1);
        self::$dbNews->delete('region_store', 'nw_id=' . $id, 1);
        self::$dbNews->delete('store', 'id=' . $id, 1);
        return true;
    }

    /**
     * Set bai tren trang chu tu bang store
     * @param unknown_type $id
     * @param unknown_type $property
     * Can update lai cache
     */
    public function setHomeFromStore($id, $property = 0)
    {
        $this->log('Đặt bài trang chủ từ kho', $id, $property);
        settype($id, 'int');
        if ($id == 0) {
            return false;
        }
        $row = $this->getStoreOne($id);
        $arrNewsHome = array(
            'nw_id' => $id,
            'censor_id' => $row['censor_id'],
            'cate_id' => $row['cate_id'],
            'cate_path' => $row['cate_path'],
            'title' => $row['title'],
            'description' => $row['description'],
            'tag' => $row['tag'],
            'img1' => $row['img1'],
            'img2' => $row['img2'],
            'img3' => $row['img3'],
            'img4' => $row['img4'],
            'type_img_show' => 1,
            'type_post' => $row['type_post'],
            'property' => $property,
            'is_video' => $row['is_video'],
            'is_img' => $row['is_img'],
            'time_created' => $row['time_created'],
            'time_public' => $row['time_public']
        );

        $check = $this->countRecord('store_home', 'nw_id=' . $id);// kiem tra xem co tren trang chu chua

        if ($check == 0)// chua co
        {
            $this->updateData('store', array('type' => 1), 'id=' . $id);
            self::$dbNews->insert('store_home', $arrNewsHome);
            return true;
        } else {
            if ($this->updateData('store_home', array('property' => $property), 'nw_id=' . $id)) {
                return true;
            }
            return false;
        }


    }

    public function getNews(
        $table,
        $list_field,
        $wh = null,
        $order = null,
        $limit = null,
        $key = null,
        $is_cache = false
    ) {

        if ($table == 'store') {
            if (!$wh) {
                $wh = 'time_public < ' . time();
            } else {
                $wh .= " AND time_public < " . time();
            }
        }

        if ($is_cache) {
            $Cache = new CacheFile();
            $data = $Cache->get(md5($wh . $table), '', CACHE_FILE_PATH . 'news' . DS, 300);
            if ($data) {
                return $data;
            }
        }
        $result = self::$dbNews->select($table, $list_field, $wh, $order, $limit, $key);
        if ($is_cache) {
            $Cache->set(md5($wh . $table), $result, 300, '', CACHE_FILE_PATH . 'news' . DS);
        }
        return $result;
    }

    public function searchFullTextNews($keyword, $wh = '', $limit = '0,20')
    {
        if ($keyword) {
            //$order = 'Priority DESC';
            $sql = "SELECT nw_id FROM search WHERE $keyword ORDER BY nw_id DESC ";

            if ($limit) {
                $limit = " LIMIT {$limit}";
            } else {
                $limit = "";
            }
            $sql = $sql . $limit;

            self::$dbNews->query($sql);
            $data = self::$dbNews->fetchAll('');

            $news_ids = '';
            if (count($data)) {
                $_result = array();
                foreach ($data as $_temp) {
                    $news_ids .= $_temp['nw_id'] . ',';
                }
                $news_ids = rtrim($news_ids, ',');
                $result = $this->getNews('store', 'id,title,cate_id,img2,description,time_created,time_public',
                    "id IN ({$news_ids})", 'time_public DESC', null, 'id');
                foreach ($data as $_temp) {
                    $_result[] = $result[$_temp['nw_id']];
                }
                return $_result;
            }
            return array();
        }
        return array();
    }

    public function writeFile($data, $path = LOG_PATH, $mode = 'a+')
    {
        if (!$fp = @fopen($path, $mode)) {
            return false;
        }
        flock($fp, LOCK_EX);
        fwrite($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    public function log($action, $id, $ext = '')
    {
        $user_info = UserCurrent::$current->data;
        if ($ext == '') {
            $data = $user_info['user_name'] . ' - ' . $action . '. Bài có id: ' . $id . ' lúc: ' . date('H:i:s d/n/Y',
                    time()) . '-' . $_SERVER['HTTP_X_REAL_IP'] . '<br/>';
        } else {
            $data = $user_info['user_name'] . ' - ' . $action . '. Bài có id: ' . $id . ' (' . $ext . ') lúc: ' . date('H:i:s d/n/Y',
                    time()) . '-' . $_SERVER['HTTP_X_REAL_IP'] . '<br/>';
        }
        $this->writeFile($data, LOG_PATH . 'action.log.html');
    }

}
