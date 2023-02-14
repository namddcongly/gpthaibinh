$(document).ready(function(){
   $("#add_page").click(function(e){ 
		if($("#add").css('display') == "none")		
			$("#add").slideDown(600);
		else
			$("#add").slideUp(600);
   });
	$('.delete_module').click(function(e){
		if(confirm('Bạn thực sự muốn xóa'))
		{
			var obj = $(this).parent();
			 $.ajax({
	            url: $(this).attr('rel'),
	            type:'GET',
	            dataType:'json',            
	            success:function(data)
	            {
	            	if(data.code == 1)
	            	{
	            		obj.remove();
	            		alert("Xóa thành công");
	            	}
	            	else
	            		alert("Xóa không thành công");
	            },
	            timeout:function(){}
	        });
		}
	});
	$('.change_possition').click(function(e){
		$.ajax({
            url: $(this).attr('rel'),
            type:'GET',
            dataType:'json',
            data:$(this).serialize(),
            success:function(data)
            {  
            	location.reload();
            },
            timeout:function(){}
        });
	});
	$('.delete_all_module').click(function(e){
		if(confirm('Bạn thực sự muốn xóa'))
		{
			$.ajax({
	            url: $(this).attr('rel'),
	            type:'GET',
	            dataType:'json',            
	            success:function(data)
	            {
	            	if(data.code == 1)            	
	            		window.location.reload();
	            	else
	            		alert("Xóa không thành công");
	            },
	            timeout:function(){}
	        });
		}
	});
	$('.page_delete').click(function(e){
		$.ajax({
            url: $(this).attr('rel'),
            type:'GET',
            dataType:'json',            
            success:function(data)
            {
            	if(data.code == 1)
					window.location.reload();
            	else
            		alert("Xóa không thành công");
            	
            },
            timeout:function(){}
        });
	});
});
function delModule(id)
{
	var ok=confirm("Bạn có chắc là xóa module ra khỏi hệ thống không?");
	if(ok==true){
		$.post("ajax.php?fnc=module.process&path=main",
				{'id':id},
				function(data)
				{
					if(data==1)
						window.location.reload();
					else
						alert('Bạn không có quyền, hoặc đã có lỗi xảy ra!');
				}			
			)
	}
}
