function postNews(id,action,obj)
{	
		$(obj).removeAttr('onclik');
		$(obj).html('<font color="red">Đang xử lý...</font>');
		$.post("ajax.php?fnc=administration.news.process&path=news",
			{'action':action,'nw_id':id},
			function(data)
			{
				if(data==1)
					window.location.reload();
				else
					alert('Đã có lỗi xảy ra');
			}			
		)	

}
function getId(id,status)
{
	$("#return_id").val(id);
	$("#status_value").val(status);
}
function postMaket(id)
{
	$.post("ajax.php?fnc=administration.news.process&path=news",
			{'action':'post_maket','nw_id':id},
			function(data)
			{
				if(data==1)
					window.location.reload();
				else
					alert('Đã có lỗi xảy ra');
					
			}			
		)	
}
function newsReturn()
{
	
	var reason =$("#reason").val();
	var id=$("#return_id").val();
	var status_value=$("#status_value").val();
	
	if(reason=='' || reason == null)
		return false;
	else
	{
		$.post("ajax.php?fnc=administration.news.process&path=news",
			{'action':'news_return','nw_id':id,'reason':reason,'status':status_value},
			function(data)
			{
				if(data==1)
					window.location.reload();
				else
					alert('Đã có lỗi xảy ra');
					
			}			
		)	
	}
}
function sendMessage()
{
	
	var content=$("#message").val();
	$.post("ajax.php?fnc=administration.news.process&path=news",
			{'action':'send_message','content':content},
			function(data)
			{
				//alert(data);
				if(data==1)
						window.location.reload();
				else
					alert('Không gửi được chỉ đạo, bạn vui lòng kiểm tra lại!');	
					
			}			
		)	

}
function viewContent(id)
{
	$.post("ajax.php?fnc=administration.news.process&path=news",
			{'action':'view-content-to-review','nw_id':id},
			function(data)
			{
				$("#news-content").html(data);
					
			}			
		)	
}
function delData(id)
{
	var ok=confirm('Bạn có chắc chắn muốn xóa bài này ra hệ thống không?');
	if(ok==true){
		$.post("ajax.php?fnc=administration.news.process&path=news",
			{'action':'delete','nw_id':id},
			function(data)
			{
				if(data==1)
					window.location.reload();
				else
					alert('Đã có lỗi xảy ra');
					
			}			
		)	
	}	
}