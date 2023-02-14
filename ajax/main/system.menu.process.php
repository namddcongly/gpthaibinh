<?php
require_once 'application/main/includes/menu_model.php';
require_once 'application/main/includes/portal_model.php';
require_once 'application/main/includes/page_model.php';
$menuObj=new MenuModel();
$action=SystemIO::post('action','def','');
$id=SystemIO::post('id','int',0);
switch($action)
{
	case 'insert-data':
		$arrNewData= $menuObj->getData();
		$menuObj->insertData($arrNewData);
		break;
	case 'delete-data':
		$menuObj->delData($id);
		break;
	case 'load-data':
		$row=$menuObj->readData($id);
		$menu_list=$menuObj->getList('parent_id=0','position asc','','');
		$count_menu=count($menu_list);
		for($i=0; $i < $count_menu;++$i)
		{
			if($menu_list[$i]['parent_id']==0){
				$arr_menu_level_option_0[$menu_list[$i]['id']]=$menu_list[$i]['name'];
			}
		}
		$menu_option=SystemIO::getOption($arr_menu_level_option_0,$row['parent_id']);

		$portalObj=new PortalModel();
		$portal_list=$portalObj->getList();
		$portal_option=SystemIO::getOption(SystemIO::arrayToOption($portal_list,'name','name'),$row['portal_name']);

		$pageObj= new PageModel();
		$page_list=$pageObj->getList();
		$arr_to_page_option=SystemIO::arrayToOption($page_list,'id','name');
		$page_option=SystemIO::getOption($arr_to_page_option,$row['page_id']);



		echo '<h2>Chỉnh sửa Menu</h2>
		<ul>
			 <li>
				<label for="name">Tên (*) </label>
				<input type="text" id="name" name="name"  style="width:230px;" value="'.$row['name'].'" />
			</li>
			 <li>
				<label for="name">Url </label>
				<input type="text" id="url" name="url"  style="width:230px;" value="'.$row['url'].'" />
				&nbsp;Đường dẫn của menu
			</li>			
			<li>		
				<label for="name">Tên quyền sử dụng (*) </label>
				<input type="text"  id="privilege_name" name="privilege_name" style="width:230px;" value="'.$row['privilege_name'].'" />&nbsp;&nbsp;Quyền sử dụng menu&nbsp;&nbsp;[<a href="#" target="_blank">Danh sách quyền</a>]</li>
			<li>
				<label for="name">Menu mức cao </label>
				<select  style="width:238px;"  id="parent_id" name="parent_id" >
				<option  value="0" >Chọn menu</option> 
				'.$menu_option.'
				</select> 
				&nbsp;Menu (cha) mức cao hơn
			</li>
			<li>
				<label for="name">Portal (*)</label>		
				<select  style="width:238px;"  id="portal_id" name="portal_id" >
				<option  value="0" >Chọn portal</option>
				'.$portal_option.'
				</select>
				&nbsp;Portal menu sử dụng
			</li>            
			<li>
				<label for="name">Page (*)</label>		
				<select  style="width:238px;"  id="page_id" name="page_id" ><option  value="0" >Chọn page</option>'.$page_option.'</select> 
				&nbsp;Trang menu sử dụng
			</li>
			<li>
				<label for="name">Thứ tự (*) </label>
				<input type="text" id="position" name="position"  style="width:30px;" value="'.$row['position'].'" rel="num" /> Thứ tự hiển thị trong hệ thống
			</li>		
			<li>
				<input type="button" class="button" value="Sửa menu" style="margin-left:153px; width:80px;" onclick="editData('.$row['id'].');"/>
			</li>
		</ul>';
		break;
	case 'update-data':
		$arrNewData= $menuObj->getData();
		$menuObj->updateData($arrNewData,$id);
		break;


}
?>