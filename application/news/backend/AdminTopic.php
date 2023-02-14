<?php
ini_set('display_errors', 0);
if (defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/news/backend/includes/backend.news.php';
require_once 'application/main/includes/user.php';
require_once UTILS_PATH . 'paging.php';
require_once UTILS_PATH . 'image.upload.php';

class AdminTopic extends Form
{
    function __construct()
    {
        Form::__construct($this);
    }

    function on_submit()
    {

        require_once UTILS_PATH . 'image.resize.php';
        $newsObj = new BackendNews();
        $user_info = UserCurrent::$current->data;
        $id = SystemIO::post('id', 'int', 0);
        $path_img_upload = ROOT_PATH . 'data/topic';
        if ($_FILES['img']['name']) {
            $uploader = new Uploader();
            $imageResize = new ImageResize();
            $uploader->setPath($path_img_upload);
            $uploader->setMaxSize(500000);
            $uploader->setFileType('custom', array('jpg', 'jpeg', 'png', 'gif'));
            $result = $uploader->doUpload('img');
            $img = (string)$result['name'];
        }
        $name = SystemIO::post('name', 'def');
        $position = SystemIO::post('position', 'def');
        $cate_id = SystemIO::post('cate_id', 'int');
        $desc = SystemIO::post('description', 'def');
        $keyword = SystemIO::post('keyword', 'def');
        $title = SystemIO::post('title', 'def');
        $type = SystemIO::post('type', 'int', 0);
        $content = SystemIO::post('content', 'def', '');
        $arrNewData = array('user_id' => $user_info['id'], 'type' => $type, 'content' => $content, 'name' => $name, 'property' => 1, 'position' => $position, 'cate_id' => $cate_id, 'time_created' => time(), 'description' => $desc, 'keyword' => $keyword, 'title' => $title);
        if ($img)
            $arrNewData['img'] = $img;
        if ($id) {
            if ($newsObj->updateData('topic', $arrNewData, 'id=' . $id)) {
                Url::redirectUrl(array(), '?app=news&page=admin_topic&cmd=admin_topic');
            }
        } else {
            if ($newsObj->insertData('topic', $arrNewData))
                Url::redirectUrl(array(), '?app=news&page=admin_topic&cmd=admin_topic');
        }


    }

    function index()
    {
        if (!UserCurrent::isLogin()) {
            @header('Location:?app=main&page=admin_login');
        }
        $cmd = SystemIO::get('cmd', 'str', 'admin_topic');
        $id = SystemIO::get('id', 'int', 0);
        switch ($cmd) {
            case 'admin_topic':
                return $this->adminTopic();
                break;
            case 'add_edit':
                return $this->addAndEdit($id);
                break;
        }
    }

    function adminTopic()
    {

        joc()->set_file('AdminTopic', Module::pathTemplate() . "backend/topic_store.htm");
        $newsObj = new BackendNews();
        $item_per_page = 20;
        $page_no = SystemIO::get('page_no', 'int', 1);
        if ($page_no < 1) $page_no = 1;
        $stt = ($page_no - 1) * $item_per_page + 1;
        $limit = (($page_no - 1) * $item_per_page) . ',' . $item_per_page;
        $list_category = $newsObj->getListCategory('cate_id1=0', '', 200, 'id');
        $q = SystemIO::get('q', 'def', '');
        joc()->set_var('q', $q);
        $q = trim(str_replace(array('"', "'", '%'), array('', '', ''), $q), ' ');
        $cate_id = SystemIO::get('cate_id', 'int', 0);
        joc()->set_var('option_category', SystemIO::getOption(SystemIO::arrayToOption($list_category, 'id', 'name'), $cate_id));
        $wh = ' 1=1 ';
        if ($q) $wh .= " AND (name LIKE '%{$q}%')";
        if ($cate_id) $wh .= " AND cate_id = " . $cate_id;
        $list_data = $newsObj->getListData('topic', 'id,name,title,description,img,property,keyword,time_created', $wh, 'time_created DESC', $limit);
        joc()->set_block('AdminTopic', 'ListRow', 'ListRow');
        $txt_html = '';
        foreach ($list_data as $row) {
            joc()->set_var('stt', $stt);
            joc()->set_var('id', $row['id']);
            ++$stt;
            joc()->set_var('name', $row['name']);
            joc()->set_var('title', $row['title']);
            joc()->set_var('description', $row['description']);
            joc()->set_var('src', 'data/topic/' . $row['img']);
            joc()->set_var('time_created', date('H:i d/m/Y', $row['time_created']));
            joc()->set_var('path', $list_category[$row['cate_id']]['name']);
            if ($row['property'] > 0) {
                if ($row['property'] == 1)
                    joc()->set_var('set_property', '<a href="javascript:;" onclick="updateProperty(' . $row['id'] . ',0)">Hủy bỏ hiển thị</a><br/><a href="javascript:;" onclick="updateProperty(' . $row['id'] . ',4)">Thiết lập trang chủ</a><br/><a href="javascript:;" onclick="updateProperty(' . $row['id'] . ', 8)">Lãnh đạo tòa án</a>');
                if ($row['property'] == 4)
                    joc()->set_var('set_property', '<a href="javascript:;" onclick="updateProperty(' . $row['id'] . ',1)">Hủy bỏ trang chủ</a><br/><a href="javascript:;" onclick="updateProperty(' . $row['id'] . ',0)">Hủy bỏ hiển thị</a>');
            } else {
                joc()->set_var('set_property',
                    '<a href="javascript:;" onclick="updateProperty(' . $row['id'] . ',1)">Thiết lập hiển thị</a>
                <br/><a href="javascript:;" onclick="updateProperty(' . $row['id'] . ',4)">Thiết lập trang chủ</a></br><a href="javascript:;" onclick="updateProperty(' . $row['id'] . ', 8)">Lãnh đạo tòa án</a>');
            }

            $txt_html .= joc()->output('ListRow');
        }
        joc()->set_var('ListRow', $txt_html);
        global $TOTAL_ROWCOUNT;
        $totalRecord = $TOTAL_ROWCOUNT;
        joc()->set_var('total_rowcount', $totalRecord);
        joc()->set_var('paging', '<li>Tổng số: ' . $totalRecord . '</li>' . Paging::paging($totalRecord, $item_per_page, 10));
        $html = joc()->output("AdminTopic");
        joc()->reset_var();
        return $html;
    }

    function addAndEdit($id)
    {
        joc()->set_file('AdminTopic', Module::pathTemplate() . "backend/topic_add_edit.htm");
        Page::registerFile('ckeditor.js', 'webskins/richtext/ckeditor/ckeditor.js', 'footer', 'js');
        Page::registerFile('admin Js', Module::pathSystemJS() . 'admin.js', 'header', 'js');
        joc()->set_var('begin_form', Form::begin(false, "POST", ' enctype="multipart/form-data" onsubmit="return checkForm()"'));
        joc()->set_var('end_form', Form::end());
        $newsObj = new BackendNews();
        if ($id) {
            $list_data = $newsObj->getListData('topic', '*', 'id=' . $id);
            $row = current($list_data);
        }
        joc()->set_var('id', (int)$row['id']);
        joc()->set_var('name', $row['name']);
        joc()->set_var('title', $row['title']);
        joc()->set_var('description', $row['description']);
        joc()->set_var('keyword', $row['keyword']);
        joc()->set_var('position', $row['position']);
        joc()->set_var('content', $row['content']);
        joc()->set_var('type', $row['type']);
        if ($row['type'])
            joc()->set_var('check', 'checked="checked"');
        else
            joc()->set_var('check', '');
        joc()->set_var('img', '<img width="200px" src="data/topic/' . $row['img'] . '" />');
        $list_category = $newsObj->getListCategory('property&1=1 AND cate_id1 = 0', 'id ASC');
        joc()->set_var('option_cate', SystemIO::getOption(SystemIO::arrayToOption($list_category, 'id', 'name'), (int)$row['cate_id']));
        $html = joc()->output("AdminTopic");
        joc()->reset_var();
        return $html;

    }


}