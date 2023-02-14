<?php
require_once 'application/main/includes/user.php';
require_once 'application/main/includes/group.php';
require_once 'application/main/includes/group_user.php';
require_once 'application/main/includes/group_privilege.php';
require_once 'application/main/includes/privilege.php';
$action = SystemIO::post('action', 'def', '');
$id = SystemIO::post('id', 'int', 0);
$userObj = new User();
switch ($action) {
    case 'change_pass':
        $password_old = SystemIO::post('password_old', 'def');
        $password_new = SystemIO::post('password_new', 'def');
        $password_new_confirm = SystemIO::post('password_new_confirm', 'def');
        $password = md5($password_new . DEFAULT_PREFIX_PASSWORD);
        $row = $userObj->readData($id);
        if ($row['password'] != md5($password_old . DEFAULT_PREFIX_PASSWORD)) {
            echo 0;
            exit();
        }
        if ($password_new == $password_new_confirm) {
            if ($userObj->updateData(array(
                'password' => $password,
                'time_last_change_password' => date('Y-m-d H:i:s', time())
            ),
                $id)) {
                echo 1;
            } else {
                echo 0;
            }
        }
        break;
    case 'reset-password':
        $password_new = SystemIO::post('password_new', 'def', '');
        $password_new_confirm = SystemIO::post('password_new_confirm', 'def', '');
        $password = md5($password_new . DEFAULT_PREFIX_PASSWORD);
        if ($password_new == $password_new_confirm) {
            if ($userObj->updateData(array(
                'password' => $password,
                'time_last_change_password' => date('Y-m-d H:i:s', time())
            ),
                $id)) {
                echo 1;
            } else {
                echo 0;
            }
        } else {
            echo 0;
        }
        break;
    case 'lock':
        $type = SystemIO::post('type', 'def', '');
        if ($userObj->updateData(array('active' => $type), $id)) {
            echo 1;
        } else {
            echo 0;
        }
        break;
    case 'insert-group':
        $objGroup = new Group();
        $arrNewData = $objGroup->getData();
        if ($objGroup->insertData($arrNewData)) {
            echo 1;
        } else {
            echo 0;
        }

        break;
    case 'del-group':
        $objGroup = new Group();
        $objGroupUser = new GroupUser();
        $objGroupPrivilege = new GroupPrivilege();
        if ($objGroup->delData($id)) {
            $objGroupUser->delMultiData('group_id=' . $id);
            $objGroupPrivilege->delMultiData('group_id=' . $id);
            echo 1;
        } else {
            echo 0;
        }
        break;
    case 'load-group':
        $objGroup = new Group();
        $row = $objGroup->readData($id);
        echo '<li>
			<label for="name">Tên nhóm </label>
			<input type="text" value="' . $row['name'] . '" style="width: 150px;" name="name" id="name"> Chỉ gồm các chữ cái từ a-z, và _
		</li>
		<li>
			<label for="name">Mô tả</label>
			<input type="text" value="' . $row['description'] . '" style="width: 238px;" name="description" id="description"> 
			Mô tả thông tin về nhóm
		</li>
		<li><input type="button" onclick="updateData(' . $row['id'] . ');" style="margin-left: 153px; width: 100px;" value="Sửa nhóm" class="button">
		</li>';
        break;
    case 'update-group':
        $objGroup = new Group();
        $arrNewData = array(
            'name' => SystemIO::post('name', 'def'),
            'description' => SystemIO::post('description', '')
        );
        if ($objGroup->updateData($arrNewData, $id)) {
            echo 1;
        } else {
            echo 0;
        }
        break;
    case 'del-privilege':
        $privilege_id = SystemIO::post('privilege_id', 'int');
        $group_id = SystemIO::post('group_id', 'int');
        $groupPrivilegeObj = new GroupPrivilege();
        $cond = "group_id={$group_id} AND privilege_id={$privilege_id}";
        if ($groupPrivilegeObj->delMultiData($cond)) {
            echo 1;
        } else {
            echo 0;
        }
        break;
    case 'add-privilege-to-group':
        $privilege_name = SystemIO::post('privilege_name', 'def');
        $group_id = SystemIO::post('group_id', 'def');
        $privilegeObj = new Privilege();
        $cond = "name='{$privilege_name}'";
        $privilege = $privilegeObj->readData('', $cond);
        if ($privilege['id']) {
            $groupPrivilegeObj = new GroupPrivilege();
            if ($groupPrivilegeObj->insertData(array('group_id' => $group_id, 'privilege_id' => $privilege['id']))) {
                echo 1;
            } else {
                echo 0;
            }
        } else {
            echo 0;
        }
        break;
    case 'add-user-to-group':
        $user_name = SystemIO::post('user_name', 'def');
        $group_id = SystemIO::post('group_id', 'def');
        $userObj = new User();
        $cond = "user_name='{$user_name}'";
        $user = $userObj->readData('', $cond);
        if ($user['id']) {
            $groupUserObj = new GroupUser();
            if ($groupUserObj->insertData(array('group_id' => $group_id, 'user_id' => $user['id']))) {
                echo 1;
            } else {
                echo 0;
            }
        } else {
            echo 0;
        }
        break;
    case 'del-user-from-group':
        $groupUserObj = new GroupUser();
        $group_id = SystemIO::post('group_id', 'int');
        $user_id = SystemIO::post('user_id', 'int');
        $cond = "user_id={$user_id} AND group_id={$group_id}";
        if ($groupUserObj->delMultiData($cond)) {
            echo 1;
        } else {
            echo 0;
        }
        break;

}
?>