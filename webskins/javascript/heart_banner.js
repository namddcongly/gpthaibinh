var currentHeart = ($("#currentHeart").val())*1;
var Timeout_ID = '';
function display_heart_banner(direct)
{
	var totalHeart = $("#totalHeart").val();
	
	$("#heart_"+currentHeart).hide();
	$("#thumb"+currentHeart).removeClass('current');
	if(direct == '-')
		currentHeart = currentHeart - 1;
	else 
		currentHeart = currentHeart + 1;
	if(currentHeart > totalHeart) currentHeart = 1;
	else 
		if(currentHeart == 0)
			currentHeart = totalHeart;
	$("#heart_"+currentHeart).show();
	$("#thumb"+currentHeart).addClass('current');
	clearTimeout(Timeout_ID);
	setTimeOutHeart();
}
$(function()
{
	display_heart_banner('+');
});

function setTimeOutHeart()
{
	Timeout_ID = setTimeout("display_heart_banner('+')",5000);
}
function change_banner(i)
{
	clearTimeout(Timeout_ID);	
	if(i != currentHeart)
	{
		$("#heart_"+currentHeart).hide();
		$("#thumb"+currentHeart).removeClass('current');
		currentHeart = i;
		$("#heart_"+currentHeart).show();
		$("#thumb"+currentHeart).addClass('current');
	}	
}