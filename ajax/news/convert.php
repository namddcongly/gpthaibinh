<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

require(APPLICATION_PATH.'news'.DS.'includes'.DS."xahoi_model.php");
require(UTILS_PATH.'pagination.php');


$newsObj = new XahoiModel();

$cate_id 	= SystemIO::get('cate_id'	, 'int', 0);
$page_no 	= SystemIO::get('page_no'	, 'int', 1);

if($cate_id > 0)
{
	$news = $newsObj->getList("cate_path like '%,$cate_id,%'", "id desc", ($page_no*10).",10");

	$paging = new Pagination();
	
	$page_no = $page_no - 1;
	
	$paging->total = $newsObj->total("cate_path like '%,$cate_id,%'");
	$paging->per_page = 10;
	$paging->page = $page_no;
	$paging->portal = "news";
	$paging->pagename = "convert";
	
	$output = "";
	
	$leng = count($news);
	
	$news_id='';
	if($leng > 0)
	{
		foreach($news as $_temp)
		{
			
			$news_id.=$_temp['id'].',';
		}
		$news_id=rtrim($news_id,',');
		dbObject()->setProperty('news','store');
		$sql='SELECT id FROM store WHERE id IN ('.$news_id.')';
		dbObject()->query($sql);
		$ngoi_sao=dbObject()->fetchAll('id');
		$n_sao=array();
		if(count($ngoi_sao))
		foreach($ngoi_sao as $t)
		{
			$n_sao[]=$t['id'];
		}
		
	}
	if($leng > 0)
	{

		foreach($news as $n)
		{
			if(in_array($n['id'],$n_sao)) continue;
			$link = 'http://xahoi.com.vn/?app=news&page=detail&id='.$n["id"];
			$img = 'http://image.xahoi.com.vn:8001/cnn_132x97/'.date('Y/n/j' , $n['time_created']).'/'.$n["img2"];
			$output .= '<tr>';
			$output .= '<td style="text-align:center" class="bdtop bdbottom bdleft"><p><input class="checkall" type="checkbox" value="'.$n["id"].'"></p></td>';
			$output .= '<td class="bdtop bdbottom bdleft"><p><img src="'.$img.'"></p></td>';
			$output .= '<td class="bdtop bdbottom bdleft"><p><a href="'.$link.'" target="_bank">'.$n["title"].'</a><br>'.$n["description"].'<br>'.date("H:i d-m-Y", $n["time_public"]).'</p></td>';
			$output .= '</tr>';

		}
	}
	
	echo json_encode(array("code" => 1, "html" => $output, "paging" => $paging->create1_ajax("&cate_id=".$cate_id)));
}
else
echo json_encode(array("code" => 0, "html" => '<td colspan="3">Có lỗi xảy ra trong quá trình xử lý dữ liệu</td>'));
?>