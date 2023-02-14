<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

require_once 'application/news/includes/poll_model.php';

$pollObj = new PollModel();

$list = $pollObj->get_List("","id desc","0,30");

$items = $list["items"];



if(count($items))
{
	echo '<ul>';
	
	echo '<li><input type="radio" name="choice_poll" value="0" class="choice_poll"> Không hiển thị poll</li>';
	
	foreach($items as $item)
	{
		echo '<li><input type="radio" name="choice_poll" value="'.$item["id"].'" class="choice_poll"> '.$item["name"].'</li>';	
	}
	
	echo '</ul>';
}

?>