<?php
require_once 'application/main/includes/portal_model.php';
$action=SystemIO::post('action','def','');
$id= SystemIO::post('id','int',0);
$portalObj=new PortalModel();
switch($action)
{
	case 'delete':
		$portalObj->delData($id);
		break;
	case 'load-data':
		$row=$portalObj->readData($id);
		echo '<ul>
			<li>
				<label for="name">Tên Portal</label>
				<input type="text" style="width:238px;" name="name" value="'.$row['name'].'" id="name"/>	Chỉ gồm các chữ cái từ a-z, và _ 				
			</li>
			<li>
				<label for="name">Tên hiển thị</label>
				<input type="text" style="width:238px;" name="alias" value="'.$row['alias'].'" id="alias" />	Tên hiển thị của Portal	
			</li>
			<li>
				<label for="name">Mô tả</label>
				<input type="text" style="width:438px;" name="description" value="'.$row['description'].'" id="description"/> Mô tả chức năng hoạt động của trang
			</li>
			<li>
				<input type="button" name="button" class="button" value="Sửa protal" style="margin-left:153px; width:70px;"  onclick="editData('.$row['id'].')"/> 
				<input type="button" name=""button class="button" value="Hủy bỏ" onclick="window.location.reload();" />
			</li>
		</ul><div class="line top"></div>';
		break;
	case 'edit-data':
		$arrNewData=$portalObj->getData();
		$portalObj->updateData($arrNewData,$id);
		break;
	case 'del-cache':
		$portal_name=SystemIO::post('portal_name','def','');
		if($portal_name)
		Folder::clearFilesInPath(CACHE_PATH.$portal_name,'','.',true);
		break;
	default:
		break;
}

?>