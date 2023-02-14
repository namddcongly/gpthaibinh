<?php
if (defined(IN_JOC)) die("Direct access not allowed!");


require_once 'application/main/includes/menu_model.php';

class AdminHeader
{
    function index()
    {
        joc()->set_file('AdminHeader', TEMPLATE_PATH . "main/admin_header.htm");
        Page::registerFile('css standard', 'webskins' . DS . 'skins' . DS . 'main' . DS . 'css' . DS . 'standard.css', 'header', 'css');
        Page::registerFile('css ja.menu', 'webskins' . DS . 'skins' . DS . 'main' . DS . 'css' . DS . 'ja.cssmenu.css', 'header', 'css');
        Page::registerFile('jquery', Module::pathSystemJS() . 'jquery-1.4.4.min.js', 'header', 'js');
        $menuObj = SystemIO::createObject('MenuModel');
        /*Check login*/
        joc()->set_block('AdminHeader', 'Login', 'Login');
        $login = '';
        if (UserCurrent::isLogin()) {
            joc()->set_var('login_name', UserCurrent::$current->data['user_name']);
            $login = joc()->output('Login');
        }
        joc()->set_var('Login', $login);

        $arr_menu_level_0 = array();

        $arr_parent_child = array();

        $menu_list = $menuObj->getList('', 'position asc', '', '');

        $count_menu = count($menu_list);

        for ($i = 0; $i < $count_menu; ++$i) {
            if ($menu_list[$i]['parent_id'] == 0) {
                $arr_menu_level_0[$menu_list[$i]['id']] = $menu_list[$i];

                for ($j = 0; $j < $count_menu; ++$j) {
                    if ($menu_list[$i]['id'] == $menu_list[$j]['parent_id'])
                        $arr_parent_child[$menu_list[$i]['id']][] = $menu_list[$j];
                }
            }
        }
        $text_html = '';

        foreach ($arr_menu_level_0 as $row) {
            //if(!UserCurrent::havePrivilege($row['privilege_name'])) continue;
            $text_html .= '<li class="havechild">
						<a href="' . $row['url'] . '">' . $row['name'] . '</a>';

            if (is_array($arr_parent_child[$row['id']]) && count(@$arr_parent_child[$row['id']])) {
                $text_html .= '<ul>';
                foreach ($arr_parent_child[$row['id']] as $child) {
                    $text_html .= '<li><a class="first-item" href="' . $child['url'] . '">' . $child['name'] . '</a></li>';
                }
                $text_html .= '</ul>';
            }
            $text_html .= '</li>';
        }
        if (UserCurrent::isLogin()) {
            joc()->set_var('text_html', $text_html);
        } else {
            joc()->set_var('text_html','');
        }


        $url_current = '?' . $_SERVER['QUERY_STRING'];

        $cond = "url='{$url_current}'";

        $path_menu = $menuObj->readData(0, $cond);

        joc()->set_var('path_menu_parent', isset($arr_menu_level_0[$path_menu['parent_id']]['name']) ? @$arr_menu_level_0[$path_menu['parent_id']]['name'] : '');

        joc()->set_var('path_menu_child', $path_menu['name']);

        joc()->set_var('url_parent', isset($arr_menu_level_0[$path_menu['parent_id']]['url']) ? @$arr_menu_level_0[$path_menu['parent_id']]['url'] : '');

        joc()->set_var('date_of_week', (date('w', time())) ? 'Thứ ' . (date('w', time()) + 1) . ', Ngày ' . date('d/m/Y', time()) : 'Chủ nhật' . ', Ngày ' . date('d/m/Y', time()));


        $html = joc()->output("AdminHeader");
        joc()->reset_var();
        return $html;
    }
}

?>
