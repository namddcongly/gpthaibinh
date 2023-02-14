<?php
if (defined(IN_JOC)) die("Direct access not allowed!");
require_once 'application/main/includes/user.php';

class AdminUser extends Form
{
    function __construct()
    {
        Form::__construct($this);
    }

    function on_submit()
    {
        $phone = SystemIO::post('phone', 'def');
        $mobile_phone = SystemIO::post('mobile_phone', 'def');
        $address = SystemIO::post('address', 'def');
        $CMTND = SystemIO::post('CMTND', 'def');
        $nick_yahoo = SystemIO::post('nick_yahoo', 'def');
        $nick_skype = SystemIO::post('nick_skype', 'def');
        $full_name = SystemIO::post('full_name', 'def');
        $birthday = SystemIO::post('birthday', 'def');
        /*Creat user*/

        $email = SystemIO::post('email', 'def');
        $user_name = SystemIO::post('user_name', 'def');
        $zone_name = SystemIO::post('zone_name', 'def');
        $password = SystemIO::post('password', 'def');
        $password_confirm = SystemIO::post('password_confirm', 'def');
        $gender = SystemIO::post('gender', 'int', '');


        $userObj = SystemIO::createObject('User');
        $user_id = SystemIO::post('user_id', 'int', 0);
        if ($user_id) {
            $arrNewData = array('phone' => $phone, 'mobile_phone' => $mobile_phone, 'address' => $address, 'CMTND' => $CMTND, 'nick_yahoo' => $nick_yahoo, 'nick_skype' => $nick_skype, 'birthday' => $birthday, 'full_name' => $full_name, 'gender' => $gender);
            if ($userObj->updateData($arrNewData, $user_id)) {
                Url::redirectUrl(array(), '?app=main&page=admin_user&user_id=' . $user_id);
            } else {
                echo '<script type="text/javascript">alert("Đã có lỗi xay ra, bạn vui lòng kiểm tra các thông tin đã nhập")</script>';
            }
        } else {
            if ($password_confirm != $password) {
                echo '<script type="text/javascript">alert("Bạn đã nhập sai mật khẩu")</script>';

            } else {
                $arrNewData = array('phone' => $phone, 'mobile_phone' => $mobile_phone, 'address' => $address, 'CMTND' => $CMTND, 'nick_yahoo' => $nick_yahoo, 'nick_skype' => $nick_skype, 'birthday' => $birthday, 'user_name' => $user_name, 'gender' => $gender, 'full_name' => $full_name, 'email' => $email, 'zone_name' => $zone_name, 'password' => md5($password . DEFAULT_PREFIX_PASSWORD), 'time_register' => time());
                if ($user_id = $userObj->insertData($arrNewData))
                    Url::redirectUrl(array(), '?app=main&page=admin_user&user_id=' . $user_id);
                else
                    echo '<script type="text/javascript">alert("Đã có lỗi xay ra, bạn vui lòng kiểm tra các thông tin đã nhập")</script>';

            }
        }

    }

    function index()
    {
        Page::setHeader("Thông tin tài khoản & khởi tạo tài khoản ", "Thông tin tài khoản & khởi tạo tài khoản", "Thông tin tài khoản & khởi tạo tài khoản");
        joc()->set_file('AdminUser', Module::pathTemplate() . "admin_user.htm");
        Page::registerFile('popup-js', JAVASCRIPT_PATH . 'popup.js', 'footer', 'js');
        Page::registerFile('jqDnR', JAVASCRIPT_PATH . 'jqDnR.js', 'footer', 'js');
        Page::registerFile('popup-css', 'webskins/css/popup.css', 'header', 'css');
        $cmd = SystemIO::get('cmd', 'def', 'info');
        $user_info=UserCurrent::$current->data;
        if ($cmd == 'creat_user') {
            if($user_info['id'] != 1) Url::urlDenied(); // namdd
            if (!UserCurrent::havePrivilege('ADMIN_USER')) {
                Url::urlDenied();
            }
            return $this->creatUser();
        } elseif ($cmd == 'list') {
            if($user_info['id'] != 1) Url::urlDenied(); // namdd
            if (!UserCurrent::havePrivilege('ADMIN_USER')) {
                Url::urlDenied();
            }
            return $this->listUser();
        } elseif ($cmd == 'group') {
            if($user_info['id'] != 1) Url::urlDenied(); // namdd
            if (!UserCurrent::havePrivilege('ADMIN_USER')) {
                Url::urlDenied();
            }
            return $this->groupUser();
        } else{
            return $this->infoUser();
        }
            

    }

    function infoUser()
    {
        joc()->set_file('AdminUser', Module::pathTemplate() . "admin_user.htm");
        joc()->set_var('begin_form', Form::begin(false, "POST", 'onsubmit="return checkData();"'));
        joc()->set_var('end_form', Form::end());

        $userObj = SystemIO::createObject('User');

        $user_id = SystemIO::get('user_id', 'int', UserCurrent::$current->data['id']);
        joc()->set_var('user_id', $user_id);
        $user_info = $userObj->readData($user_id);
        $men = '';
        $woman = '';
        if ($user_info['gender'] == 1) $man = 'checked="checked"';
        else  $woman = 'checked="checked"';
        joc()->set_var('man', $man);
        joc()->set_var('woman', $woman);
        joc()->set_var('user', $user_info);
        $html = joc()->output("AdminUser");
        joc()->reset_var();
        return $html;
    }

    function creatUser()
    {
        joc()->set_var('begin_form', Form::begin(false, "POST", 'onsubmit="return checkData();"'));
        joc()->set_var('end_form', Form::end());
        joc()->set_file('AdminUser', Module::pathTemplate() . "creat_user.htm");
        $html = joc()->output("AdminUser");
        joc()->reset_var();
        return $html;
    }

    function listUser()
    {
        joc()->set_file('AdminUser', Module::pathTemplate() . "list_user.htm");
        Page::registerFile('AdminUser', Module::pathJS() . 'AdminUser.js', 'footer', 'js');
        joc()->set_block('AdminUser', 'ListRow', 'ListRow');
        require_once UTILS_PATH . 'paging.php';

        $userObj = SystemIO::createObject('User');

        $item_per_page = 20;
        $page_no = SystemIO::get('page_no', 'int', 1);
        if ($page_no < 1) $page_no = 1;
        $stt = ($page_no - 1) * $item_per_page + 1;
        $limit = (($page_no - 1) * $item_per_page) . ',' . $item_per_page;

        /*Search*/
        $wh = '1=1';
        $arr_active = array('2' => 'Tình trạng Khóa', '0' => 'Đang khóa', '1' => 'Đang mở');
        $active = (int)SystemIO::get('active', 'int', '2');
        $q = SystemIO::get('q', 'def', '');
        $q = str_replace(array('%', '"', "'"), array('', '', ''), $q);
        joc()->set_var('q', $q);
        if ($active != 2)
            $wh .= " AND active={$active}";
        if ($q)
            $wh .= " AND (user_name LIKE '%{$q}%' OR email LIKE '%{$q}%' OR address LIKE '%{$q}%' OR mobile_phone ='{$q}' OR phone = '$q' OR CMTND = '{$q}')";

        joc()->set_var('action_option', SystemIO::getOption($arr_active, $active));
        $user_list = $userObj->getList($wh, 'active DESC,time_register DESC', $limit, '');
        $text_html = '';

        foreach ($user_list as $row) {
            joc()->set_var('stt', $stt);
            ++$stt;
            joc()->set_var('user_id', $row['id']);
            joc()->set_var('user_name', $row['user_name']);
            joc()->set_var('email', $row['email']);
            joc()->set_var('time_register', date('H:i d/m/y', $row['time_register']));
            joc()->set_var('email', $row['email']);
            joc()->set_var('time_last_login', date('H:i d/m/y', $row['time_last_login']));
            joc()->set_var('address', $row['address']);
            joc()->set_var('mobile_phone', $row['mobile_phone']);
            joc()->set_var('CMTND', $row['CMTND']);

            if ($row['active'] == 1) $lock = '<a href="javascript:;" onclick="lock(' . $row['id'] . ',0)">Khóa</a>';
            else    $lock = '<a href="javascript:;" onclick="lock(' . $row['id'] . ',1)">Mở khóa</a>';

            joc()->set_var('lock', $lock);
            $text_html .= joc()->output('ListRow');
        }
        joc()->set_var('ListRow', $text_html);

        $totalRecord = $userObj->count($wh);
        joc()->set_var('paging', '<li>Tổng số: ' . $totalRecord . '</li>' . Paging::paging($totalRecord, $item_per_page, 10));

        $html = joc()->output("AdminUser");
        joc()->reset_var();
        return $html;
    }

    function groupUser()
    {
        require_once 'application/main/includes/group.php';
        require_once 'application/main/includes/group_user.php';
        require_once 'application/main/includes/group_privilege.php';
        require_once 'application/main/includes/privilege.php';

        joc()->set_file('AdminUser', Module::pathTemplate() . "admin_group_user.htm");
        $groupObj = SystemIO::createObject('Group');
        $group_list = $groupObj->getList();
        $group_ids = '';
        $user_ids = '';
        foreach ($group_list as $_temp) {
            $group_ids .= $_temp['id'] . ',';
        }
        $group_ids = trim($group_ids, ',');

        $groupPrivilege = new GroupPrivilege();


        $cond = "group_id IN ({$group_ids})";
        $sql_group_privilege = "SELECT GROUP_CONCAT(privilege_id) AS privilege_ids, group_id FROM group_privilege WHERE {$cond} GROUP BY group_id";
        $groupPrivilege->query($sql_group_privilege);
        $list_group_user_privilege = $groupPrivilege->fetchAll('group_id');// danh sach nhom nguoi dung voi nhom quyen

        /*Lấy tên quyền trong danh sách*/
        $all_privilege = '';
        foreach ($list_group_user_privilege as $_temp) {
            $all_privilege .= $_temp['privilege_ids'] . ',';
        }
        $all_privilege = trim($all_privilege, ',');
        $privilegeObj = new Privilege();

        $privilege_list = $privilegeObj->getList('id IN (' . $all_privilege . ')');

        $groupUserObj = new GroupUser();
        $userObj = new User();
        $userObj->listField = 'id,user_name';
        $sql_group_user = "SELECT GROUP_CONCAT(user_id) AS user_ids, group_id FROM group_user WHERE {$cond} GROUP BY group_id";
        $groupUserObj->query($sql_group_user);
        $list_group_user = $groupUserObj->fetchAll('group_id');// danh sach nhom nguoi dung voi nhom quyen
        $all_user = '';
        foreach ($list_group_user as $_temp) {
            $all_user .= $_temp['user_ids'] . ',';
        }
        $all_user = trim($all_user, ',');
        if ($all_user)
            $user_list = $userObj->getList('id IN (' . $all_user . ')');
        joc()->set_block('AdminUser', 'ListRow', 'ListRow');
        $text_html = '';
        $stt = 0;
        foreach ($group_list as $row) {
            ++$stt;
            joc()->set_var('group_id', $row['id']);
            joc()->set_var('stt', $stt);
            joc()->set_var('name', $row['name']);
            joc()->set_var('description', $row['description']);

            $_arr_group_user_privilege = explode(',', $list_group_user_privilege[$row['id']]['privilege_ids']);

            $_privilege_name = '';
            foreach ($_arr_group_user_privilege as $_temp)
                if (isset($privilege_list[$_temp]['name']))
                    $_privilege_name .= $privilege_list[$_temp]['name'] . ' (<a href="javascript:;" onclick="delPrivilege(' . $row['id'] . ',' . $privilege_list[$_temp]['id'] . ')" style="color:red">x</a>) | ';

            $_arr_group_user = explode(',', $list_group_user[$row['id']]['user_ids']);
            $_user_name = '';
            foreach ($_arr_group_user as $_temp) {
                if (isset($user_list[$_temp]['user_name']) && $user_list[$_temp]['user_name'])
                    $_user_name .= $user_list[$_temp]['user_name'] . '(<a href="javascript:;" onclick="delUserFromGroup(' . $row['id'] . ',' . $user_list[$_temp]['id'] . ')" style="color:red">x</a>) | ';
            }
            $_user_name = trim($_user_name, '| ');
            joc()->set_var('list_user', $_user_name);
            joc()->set_var('privilege', trim($_privilege_name, '| '));
            $text_html .= joc()->output('ListRow');
        }
        joc()->set_var('ListRow', $text_html);
        $html = joc()->output("AdminUser");
        joc()->reset_var();
        return $html;
    }
}

?>
