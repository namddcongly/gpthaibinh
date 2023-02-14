$(document).ready(
function(){
	$(".tin-ngay").click(function(){
    	if($(this).hasClass('dong'))
    	{
    		$(this).addClass('mo');
    		$(this).removeClass('dong');
    		$(this).html('Thu gọn');
    		$('.tin-ngay-'+$(this).attr('rel')).slideDown();
    	}
    	else
    	{
    		$(this).addClass('dong');
    		$(this).removeClass('mo');
    		$(this).html('Xem thêm');
    		$('.tin-ngay-'+$(this).attr('rel')).slideUp();
    	}
    });
});