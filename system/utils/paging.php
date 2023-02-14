<?php
class Paging
{
	function __construct(){}
	public static function paging($total, $perPage, $numPageShow = 10,$classCurrent = '', $classNormal = '', $url_rewrite = false)
	{
		$content='';
		//tong trang
		$totalpage = ceil($total/$perPage);
		if ($totalpage < 2) { return '';}// Return neu chi co 1 trang
		//trang hien tai
		$page_name='page_no';
		
		if(isset($_GET['page_no'])){
			$currentpage = (int)$_GET['page_no'];
		}else{
			$currentpage = 0;			
		}	
			
		if($currentpage==0) $currentpage=1;
		
		$currentpage = round($currentpage);
		
		if($classCurrent == '')
		$classCurrent = 'class="paging-active"';
		else
		$classCurrent = 'class="'.$classCurrent.'"';
		if($classNormal)
		$classNormal = 'class="'.$classNormal.'"';
		if($currentpage <= 0 || $currentpage > $totalpage)
		{
			$currentpage = 1;
		}
		if($currentpage > ($numPageShow/2))
		{
			$startpage = $currentpage - floor($numPageShow/2);
			if($totalpage - $startpage < $numPageShow)
			{
				$startpage = $totalpage - $numPageShow + 1;
			}
		}
		else
		{
			$startpage = 1;
		}
		if($startpage < 1)
		{
			$startpage = 1;
		}
		//Link den trang truoc
		if($currentpage > 1)
		{

			$url = Url::buildUrlAll(array($page_name),$page_name.'='.($currentpage-1));
			$content.= '<li><a href = "'.$url.'" >&laquo; Trước</a></li>';
		}
		//Danh sach cac trang
		if($startpage > 1)
		{
			$url = Url::buildUrlAll(array($page_name),$page_name.'=1');
			$content.= '<li><a '.$classNormal.' href= "'.$url.' ">1</a></li>';
		}
		for($i = $startpage; $i <= $startpage + $numPageShow - 1 && $i <= $totalpage; $i++)
		{
			if($i == $currentpage)
			{
				$content.= '<li><a '.$classCurrent.' href="javascript:;">'.$i.'</a></li>';
			}
			else
			{
				if($url_rewrite)
				$url = str_replace('{page_no}',$i,$url_rewrite);
				else
				$url = Url::buildUrlAll(array($page_name),$page_name.'='.$i);
				$content .= '<li><a '.$classNormal.' href= "'.$url.' ">'.$i.'</a></li>';
			}
		}
		if($i == $totalpage)
		{
			$url = Url::buildUrlAll(array($page_name),$page_name.'='.$totalpage);
			$content .= '<li><a '.$classNormal.' href= "'.$url.' ">'.$totalpage.'</a></li>';
		}
		else
		if($i < $totalpage)
		{
			$url = Url::buildUrlAll(array($page_name),$page_name.'='.$totalpage);
			$content .= '<li>...</li><li><a '.$classNormal.' href= "'.$url.' ">'.$totalpage.'</a></li>';
		}
		//Trang sau
		if($currentpage < $totalpage)
		{
			$url = Url::buildUrlAll(array($page_name),$page_name.'='.($currentpage+1));
			$content .= '<li><a '.$classNormal.' href = "'.$url.'">Sau &raquo;</a></li>';
		}
			
		return $content;
	}
}
?>