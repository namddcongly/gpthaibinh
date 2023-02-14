// JavaScript Document
function delData(id)
{
	$.post("ajax.php?fnc=portal.process&path=main",
		{'action':'delete','id':id},
		function(data)
		{
			window.location.reload();
		}			
	)
}
function loadData(id)
{
	$.post("ajax.php?fnc=portal.process&path=main",
		{'action':'load-data','id':id},
		function(data)
		{
			$("#add").html(data);
		}			
	)
}
function editData(id)
{
		$.post("ajax.php?fnc=portal.process&path=main",
		{'action':'edit-data','name':$('#name').val(),'description':$('#description').val(),'alias':$("#alias").val(),'id':id},
		function(data)
		{
			window.location.reload();
		}			
	)
}
function checkData()
{
	
	if($(".name").val()==null || $(".name").val()=='')
	{
		alert('Bạn chưa nhập tên Portal!');	
		$(".name").focus();
		return false;
	}
	if($(".alias").val()==null || $(".alias").val()=='')
	{
		alert('Bạn chưa nhập tên hiện thị cho Portal');	
		$(".alias").focus();
		return false;
	}
	if($(".description").val()==null || $(".description").val()=='')
	{
		alert('Bạn chưa nhập mô tả cho Portal!');	
		$(".description").focus();
		return false;
	}
	return false;
}
function delCache(name)
{
	$.post("ajax.php?fnc=portal.process&path=main",
	{'action':'del-cache','portal_name':name},
	function(data){
		alert(data);window.location.reload();
	}
	)
}