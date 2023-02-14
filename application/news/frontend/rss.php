<?php
ini_set('display_errors',0);
class Rss
{
	function __construct()
	{
		
	}
	function index()
	{		
		joc()->set_file('Rss', Module::pathTemplate('news')."frontend".DS."rss.htm");	
		Page::setHeader('Tin moi nhat 24h Đọc báo tin tức pháp luật Công lý','Tin tuc, tin mới, tin tức mới nhất, tin tuc moi, tin tuc 24h, doc bao, đọc báo, báo, tin mới nhất','Tin tức mới nhất trong ngày, tin tức cập nhất 24h các lĩnh vực đời sống pháp luật. Đọc báo mới nhất hôm này, thông tin thời sự mới nhất trong ngày');
		$list_category=$frontendObj->getCategory();
		$txt='<ul><li>';
		foreach($list_category as $row)
		{
			if($row['property'] & 1 ==1)
			{
				if($row['cate_id1'] == 0)
				{
					$txt.='+<a href="'.$row['alias'].'.rss">'.$row['name'].'</a><br/>';
					$k = 0 ;
					foreach($list_category as $_temp)
					{
						if($_temp['property'] & 1)
						{
							
							if(($_temp['cate_id1'] == $row['id']) && $_temp['cate_id2'] == 0)
							{	
								$txt.='  -<a href="'.$_temp['alias'].'.rss">'.$_temp['name'].'</a><br/>';
                            	++$k;    
							}
						}
					}
				}
			}
		}
		$txt.='</li></ul>';
		joc()->set_var('rss',$txt);
		$html= joc()->output("Rss");
		joc()->reset_var();
		return $html;
	}
}
?>