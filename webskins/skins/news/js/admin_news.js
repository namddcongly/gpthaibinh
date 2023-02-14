$(document).ready(
function(){
    $(".edit").click(function(e){
		
		var id = $(this).attr('rel');
		$("#title").val($("#t_"+id).html());
		$("#arrange").val($("#a_"+id).html());
		$("#position_title").val($("#p_"+id).html());
		$("#image").val($("#image_"+id).val());
		$("#enter").val(id);
	});
	$(".delete_property").click(function(e){
		
		if(confirm('Bạn có muốn xóa?'))
		{
			$.ajax({
		            url: 'ajax.php?path=news&fnc=delete_property&id='+$(this).attr('rel'),
		            type:'GET',
		            dataType:'json',            
		            success:function(data)
		            {
		            	alert(data.html);
		            	location.reload();
		            },
		            timeout:function(){}
		        });
		}
	});
	$("#add_category").click(function(e){ 		
		if($("#add").css('display') == "none")	
		{	
			$("#add").slideDown(600);
			$("#category").removeClass('header-menu-active');
			//$(this).addClass('header-menu-active');
		}
		else
		{
			$("#add").slideUp(600);
			//$(this).removeClass('header-menu-active');
			$("#category").addClass('header-menu-active');
		}
   });	
   $("#cate1").live('change', function(e){   		
   		var cate_id = $("#cate1 option:selected").val();   		   		
   		$.ajax({
	            url: 'ajax.php?path=news&fnc=change_category&id='+cate_id+"&level=1",
	            type:'GET',
	            dataType:'json',            
	            success:function(data)
	            {
	            	if(data.code == 1)
	            	{
	            		if(data.html != "")
	            			$("#cate2").html(data.html);
	            		
            			$("#cate3").remove();
            			$("#cate4").remove();
            			$("#cate5").remove();
	            		
	            	}
	            },
	            timeout:function(){}
	        });
   });
   $("#cate2").live('change', function(e){
   		var cate_id = $("#cate2 option:selected").val(); 
   		$.ajax({
	            url: 'ajax.php?path=news&fnc=change_category&id='+cate_id+"&level=2"+($("#cate3").length > 0 ? "" : "&cmd=1"),
	            type:'GET',
	            dataType:'json',            
	            success:function(data)
	            {
	            	if(data.code == 1)	   
	            	{
	            		if(data.html != "")
	            		{
		            		if($("#cate3").length > 0)
		            			$("#cate3").html(data.html);
		            		else	            		
		            			$("#cat").append(data.html);
	            		}	 
	            		else
							$("#cate3").remove();
	            		            		
            			$("#cate4").remove();	            		
            			$("#cate5").remove();	            		
	            		
	            	}
	            },
	            timeout:function(){}
	        });
   });
   $("#cate3").live('change', function(e){
   		var cate_id = $("#cate3 option:selected").val();   		   		
   		$.ajax({
	            url: 'ajax.php?path=news&fnc=change_category&id='+cate_id+"&level=3"+($("#cate4").length > 0 ? "" : "&cmd=1"),
	            type:'GET',
	            dataType:'json',            
	            success:function(data)
	            {
	            	if(data.code == 1 && data.html != "")
	            		$("#cate4").html(data.html);
	            	else
	            		$("#cate4").remove();	            		
	            	$("#cate5").remove();
	            	
	            },
	            timeout:function(){}
	        });
   });
   $("#cate4").live('change', function(e){
   		var cate_id = $("#cate4 option:selected").val();   		   		
   		$.ajax({
	            url: 'ajax.php?path=news&fnc=change_category&id='+cate_id+"&level=4"+($("#cate5").length > 0 ? "" : "&cmd=1"),
	            type:'GET',
	            dataType:'json',            
	            success:function(data)
	            {
	            	if(data.code == 1 && data.html != "")
	            		$("#cate5").html(data.html);
	            	else           		
	            		$("#cate5").remove();
	            },
	            timeout:function(){}
	        });
   });
   $(".publish").click(function(){
   		$.ajax({
            url: $(this).attr('rel'),
            type:'GET',
            dataType:'json',            
            success:function(data)
            {
            	alert(data.html);
            	location.reload();
            },
            timeout:function(){}
        });
   });
   $(".category_delete").click(function(){
   		if(confirm('Bạn có muốn xóa danh mục này?'))
   		{
   		$.ajax({
            url: $(this).attr('rel'),
            type:'GET',
            dataType:'json',            
            success:function(data)
            {
            	alert(data.html);
            	location.reload();
            },
            timeout:function(){}
        });
   		}
   });
   $("#category").change(function(){
   		window.location.href = "?app=news&page=admin_category_configuration&id="+$("#category option:selected").val();
   });   
   $("#search").live('click', function(){
   		window.location.href = "?app=news&page=admin_news_category&filter_id="+$("#filter option:selected").val()+"&query="+$("#keyword").val();
   }); 
   $("#all").live('click', function()
   {
   		var check = $("#all").attr('checked');
   		$(".checkall").each(function(){
   			$(this).attr('checked', check);
   		});
   });
   $("#region_delete").live('click', function()
   {
   		if(confirm('Bạn có muốn xóa vùng này?'))
   		{
   		$.ajax({
            url: $(this).attr('rel'),
            type:'GET',
            dataType:'json',            
            success:function(data)
            {
            	alert(data.html);
            	location.reload();
            },
            timeout:function(){}
        });
   		}
   });
   $("#region_publish").live("click",function(){
   	$.ajax({
            url: $(this).attr('rel'),
            type:'GET',
            dataType:'json',
            success:function(data)
            {
            	alert(data.html);
            	location.reload();
            },
            timeout:function(){}
        });
   });
   $("#search_region").live("click", function(){
   	window.location.href = "?app=news&page=admin_news_region&query="+$("#keyword").val()+"&categoryID="+$("#category_region option:selected").val();
   });
   $("#poll_search").click(function(){
	   	window.location.href = "?app=news&page=admin_poll&query="+$("#keyword").val()+"&type="+$("#type option:selected").val();
   });
var option = 1;   
   $("#add_option").click(function(){
	  $("#poll_option").append('<p style="margin-bottom:5px"><label for="name">Option '+option+'</label><input style="width:250px" type="text" name="poll[0][]"></p>');
	  option = option+1;
   });
});

function filltext()
{	
	if($("#cate1").val() > 0)
		$("#cate_name1").val($("#cate1 option:selected").text());	
	if($("#cate2").val() > 0)
		$("#cate_name2").val($("#cate2 option:selected").text());
	if($("#cate3").val() > 0)
		$("#cate_name3").val($("#cate3 option:selected").text());
	if($("#cate4").val() > 0)
		$("#cate_name4").val($("#cate4 option:selected").text());
}
function checkupdate()
{
	var check = 0;
	$(".checkall").each(function(){
   		if($(this).attr('checked') == true)
   			check ++;
   	});
   	if(check == 0) {alert('Chưa tick chọn đối tượng cập nhật');return false;}
}
function move_category(source)
{
    var cate_id = $("#destination").val();   	
    $("#loading").html('Đang xử lý vui lòng chờ trong giây lát ...');
    $("#submit").attr('disabled','true');
    
	$.ajax({
        url: 'ajax.php?path=news&fnc=move_cate&cmd=1&source='+source+'&destination='+cate_id,
        type:'GET',
        dataType:'json',            
        success:function(data)
        {        	
        	tb_remove();
        },
        timeout:function(){}
    });
}