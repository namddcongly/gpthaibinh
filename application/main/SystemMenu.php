<?php
if (defined(IN_JOC)) die("Direct access not allowed!");
if (!UserCurrent::havePrivilege('ADMIN_SYSTEM_MENU')) {
    Url::urlDenied();
}

require_once 'application/main/includes/portal_model.php';
require_once 'application/main/includes/menu_model.php';

class SystemMenu
{
    function __construct()
    {
    }

    function index()
    {
        joc()->set_file('Menu', Module::pathTemplate() . "system_menu.htm");
        Page::setHeader("Quản trị hệ thống Menu", "Quản trị hệ thống Menu", "Quản trị hệ thống Menu");
        Page::registerFile('SystemMenu', Module::pathJS() . 'SystemMenu.js', 'footer', 'js');
        $pageObj = SystemIO::createObject('PageModel');
        /*Danh sách trang*/
        $page_list = $pageObj->getPage();
        $arr_to_page_option = SystemIO::arrayToOption($page_list, 'id', 'name');
        joc()->set_var('page_option', SystemIO::getOption($arr_to_page_option, ''));
        /*Danh sách portal*/
        $portalObj = SystemIO::createObject('PortalModel');
        $portal_list = $portalObj->getList();
        joc()->set_var('portal_option', SystemIO::getOption(SystemIO::arrayToOption($portal_list, 'id', 'name'), ''));

        /*Danh sách các menu cập 0*/
        $menuObj = SystemIO::createObject('MenuModel');
        $arr_menu_level_0 = array();
        $arr_menu_level_1 = array();
        $arr_parent_child = array();

        $menu_list = $menuObj->getList('', 'position asc', '', '');

        $count_menu = count($menu_list);
        for ($i = 0; $i < $count_menu; ++$i) {
            if ($menu_list[$i]['parent_id'] == 0) {
                $arr_menu_level_option_0[$menu_list[$i]['id']] = $menu_list[$i]['name'];
                $arr_menu_level_0[$menu_list[$i]['id']] = $menu_list[$i];
                for ($j = 0; $j < $count_menu; ++$j) {
                    if ($menu_list[$i]['id'] == $menu_list[$j]['parent_id'])
                        $arr_parent_child[$menu_list[$i]['id']][] = $menu_list[$j];
                }
            }
        }
        joc()->set_var('menu_option', SystemIO::getOption($arr_menu_level_option_0, ''));
        $text_html = '';
        $i = 0;
        foreach ($arr_menu_level_0 as $row) {
            ++$i;
            $text_html .= '
			<tr align="center" valign="top">
			  <td align="left" class="bdleft"><p><strong>' . $i . '</strong></p></td>
			  <td align="left" class="bdleft"><p><strong><a href="?app=' . $row['portal_name'] . '&page=' . $row['page_name'] . '">' . $row['name'] . '</a></strong></p><p>Url: ' . $row['url'] . '</p></td>
			  <td align="left" class="bdleft"><p>' . $row['portal_name'] . '</p></td>
			  <td align="left" class="bdleft"><p>' . $row['page_name'] . '</p></td>
			  <td align="left" class="bdleft"><p>' . $row['privilege_name'] . '</p></td>
			  <td align="center" valign="middle" class="bdleft"><p>' . $row['position'] . '</p></td>
			  <td class="bdleft">
			  <p><a href="javascript:;" onclick="loadData(' . $row['id'] . ')">Sửa</a>&nbsp;|&nbsp;<a href="javascript:;" onclick="delData(' . $row['id'] . ')" >Xóa</a></p></td>
			</tr>';
            if (array_key_exists($row['id'], $arr_parent_child) && count($arr_parent_child[$row['id']])) {
                $j = 0;
                foreach ($arr_parent_child[$row['id']] as $child) {
                    ++$j;
                    $text_html .= '
						<tr align="center" valign="top">
						  <td align="center" class="bdleft"><p>&nbsp;&nbsp;<strong>' . $i . ' - ' . $j . '</strong></p></td>
						  <td align="left" class="bdleft"><p><strong>&nbsp;&nbsp;<a href="?app=' . $child['portal_name'] . '&page=' . $child['page_name'] . '">' . $row['name'] . '&nbsp;--->&nbsp;' . $child['name'] . '</a></strong></p><p>&nbsp;&nbsp;Url: ' . $child['url'] . '</p></td>
						  <td align="left" class="bdleft"><p>' . $child['portal_name'] . '</p></td>
						  <td align="left" class="bdleft"><p>' . $child['page_name'] . '</p></td>
						  <td align="left" class="bdleft"><p>' . $child['privilege_name'] . '</p></td>
						  <td align="center" valign="middle" class="bdleft"><p>' . $child['position'] . '</p></td>
						  <td class="bdleft">
						  <p><a href="javascript:;" onclick="loadData(' . $child['id'] . ')">Sửa</a>&nbsp;|&nbsp;<a href="javascript:;" onclick="delData(' . $child['id'] . ')" >Xóa</a></p></td>
						</tr>';
                }
            }
        }
        if (!UserCurrent::isLogin()) {
            joc()->set_var('text_html', '');
        } else {
            joc()->set_var('text_html', $text_html);
        }
        
        $html = joc()->output("Menu");
        joc()->reset_var();
        return $html;
    }
}

?>
