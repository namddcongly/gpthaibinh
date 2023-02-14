// JavaScript Document
function insertData()
{
	if(checkData()){
		var level=0;
		if($("#parent_id option:selected").val() > 0) level=1;
		$.post("ajax.php?fnc=system.menu.process&path=main",
			{
				'action':'insert-data',
				'name':$("#name").val(),
				'url': $("#url").val(),
				'privilege_name':$("#privilege_name").val(),
				'page_id':$("#page_id").val(),
				'parent_id':$("#parent_id option:selected").val(),
				'page_name':$("#page_id option:selected").html(),
				'portal_id':$("#portal_id").val(),
				'position': $("#position").val(),
				'level':level,
				'portal_name':$("#portal_id option:selected").html()
			},
			function(data)
			{
				window.location.reload();
			}			
		)
	}
}
function checkData()
{
	
	
	if($("#name").val()==null || $("#name").val()=='')
	{
		alert("Bạn phải nhập tên cho menu!");
		$("#name").focus();
		return false;
	}
	if($("#url").val()==null || $("#url").val()=='')
	{
		alert("Bạn phải nhập Url cho menu!");
		$("#url").focus();
		return false;
	}
	if($("#privilege_name").val()==null || $("#privilege_name").val()=='')
	{
		alert("Bạn phải nhập  quyền cho menu!");
		$("#privilege_name").focus();
		return false;
	}
	
	var portal_id=$("#portal_id option:selected").val();
	if(portal_id==0)
	{
		alert("Bạn phải chọn Portal cho menu");
		return false;
	}
	var page_id=$("#page_id option:selected").val();
	if(page_id==0)
	{
		alert("Bạn phải chọn Portal cho menu");
		return false;
	}
	if($("#position").val()=='' ||$("#position").val()==null )
	{
		alert("Bạn phải chọn thứ tứ cho menu!");
		$("#position").focus();
		return false;
	}
	return true;
	
}

function loadData(id)
{
	$.post("ajax.php?fnc=system.menu.process&path=main",
			{
				'action':'load-data',
				'id'  : id
			},
			function(data){
				$("#AdminMenuForm").html(data);
			}
		)		
}
function editData(id)
{
	if(checkData()){
		var level=0;
		if($("#parent_id option:selected").val() > 0) level=1;
		$.post("ajax.php?fnc=system.menu.process&path=main",
			{
				'action':'update-data',
				'id'	:id,	
				'name':$("#name").val(),
				'url': $("#url").val(),
				'privilege_name':$("#privilege_name").val(),
				'page_id':$("#page_id").val(),
				'parent_id':$("#parent_id option:selected").val(),
				'page_name':$("#page_id option:selected").html(),
				'portal_id':$("#portal_id").val(),
				'position': $("#position").val(),
				'level':level,
				'portal_name':$("#portal_id option:selected").html()
			},
			function(data)
			{
				window.location.reload();
			}			
		)
	}
}
function delData(id)
{
	var ok=confirm("Bạn có thật sự muốn xóa menu này ra khỏi hệ thống không?");
	if(ok==true)
	{
		$.post("ajax.php?fnc=system.menu.process&path=main",
			{
				'action':'delete-data',
				'id'  : id
			},
			function(data){
				window.location.reload();
			}
		)	
	}
}