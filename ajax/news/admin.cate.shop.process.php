<?php
require_once 'application/news/includes/category_shopping.php';
$action=SystemIO::post('action','def','');
$user_info=UserCurrent::$current->data;
$cateShop=new CategoryShopping();
$id=SystemIO::post('id','int',0);
switch($action)
{
	case 'add-edit':
		$name=SystemIO::post('name','def');
		$parent_id=SystemIO::post('parent_id','int',0);
		$arrange=SystemIO::post('arrange','int',0);
		if($id == 0){
			if($cateShop->insertData(array('name'=>$name,'parent_id'=>$parent_id,'arrange'=>$arrange,'property'=>1)))
				echo 1;
			else 
				echo 0;
		}
		else
		{
			if($cateShop->updateData(array('name'=>$name,'parent_id'=>$parent_id,'arrange'=>$arrange,'property'=>1),$id))
				echo 1;
			else 
				echo 0;
		}	
		break;
	case 'del':
		$row=$cateShop->readData($id);
		if($row['parent_id']){
			if($cateShop->deleteData($id))
				echo 1;
			else
				echo 0;	
		}
		else
		{
			$total=$cateShop->count('parent_id='.$row['id']);
			if($total > 0)
				echo 2;
			else
			{
				if($cateShop->deleteData($id))
					echo 1;
				else
					echo 0;
			}
					
		}
		break;
	case 'load-data':
		$row=$cateShop->readData($id);
		$list_cate=$cateShop->getList('parent_id=0','arrange ASC');
		$option_cate1=SystemIO::getOption(SystemIO::arrayToOption($list_cate,'id','name'),$row['parent_id']);
		echo '<ul id="data">
		<li>
			<label for="name">Tên danh mục</label>
			<input type="text" name="name" style="width:250px;" value="'.$row['name'].'" id="name"/>		
			Tên danh mục			
		</li>
		<li>
			<label for="name">Chọn danh mục</label>
			<select name="parent_id" id="parent_id">
				<option value="0">Chọn danh mục cấp 1</option>						
				'.$option_cate1.'
			</select>
		</li>
		<li>
			<label for="name">Thứ tự hiển thị</label>
			<input type="text" name="arrange" style="width:20px;" value="'.$row['arrange'].'" id="arrange"/>
		</li>
		<li style="padding:0px;">
			<label for="name">&nbsp;</label>
			<input type="button" value="Sửa" class="button" style="height: 25px; margin-top: 12px;" name="them mo"  onclick="postData('.$row['id'].')">
			<input type="button" value="Thêm mới" class="button" style="height: 25px; margin-top: 12px;" name="them mo"  onclick="postData(0)">
		</li>';
		break;		
					
}