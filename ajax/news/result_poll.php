<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />		
<?php

require_once 'application/news/includes/poll_model.php';
require_once 'application/news/includes/poll_option_model.php';

$pollObj = new PollModel();

$opollObj = new PollOptionModel();

$poll_id = SystemIO::get("poll_id", "int", 0);

if($poll_id > 0)
{
	$poll = $pollObj->PollOne("name",$poll_id);

	$options = $opollObj->getList("*","poll_id=".$poll_id,"id asc");

	echo '<div class="poll">';
		echo '<table class="fb-table"><tr><th colspan="3"> '.$poll["name"].' </th></tr>';
			if(count($options))
			{
				$total = 0;
				foreach($options as $op)
					$total += $op["voted"];
				
				$color = array("#FF3300","#009900","#0C186D","#cccccc","#ffee00","#ff0088","#000");
				$tcolor = array("#000","#000","#fff","#000","#000","#000","#fff");
					
				$i=0;
				foreach($options as $op)
				{
					$v = ($total > 0 ?(($op["voted"]/$total)*100) : 0);
					echo '<tr><td>'.$op["text"].'</td><td style="width:200px;padding-right:5px"><p style="color:'.$tcolor[$i].';padding:2px 5px;margin-right:5px;width:'.$v.'%;background:'.$color[$i].'"> '.round($v).'%</p></td><td>'.$op["voted"].' phiếu</td></tr>';
					$i++;
				}
				echo '<tr><td colspan="3" style="text-align:right;padding-right:5px">Tổng cộng: '.$total.' Phiếu </td></tr>';
			}
		echo '</table>';
	echo '</div>';
	
}
?>
<style>
table.fb-table { background: #ccc; border-spacing: 1px; width:100%;text-align:left;font-family:arial;font-size:15px}
table.fb-table tr.background-0 td { background: #f7f3f3; color: #187302; font-weight: bold }
table.fb-table td, table.fb-table th { background: #fff; height: 24px; text-align: left;padding-left:5px }
table.fb-table th { background: #e3fcdb; }
table.fb-table th.name, table.fb-table td.name { text-align: left; padding-left: 10px }
</style>