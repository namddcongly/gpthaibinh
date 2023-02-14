// JavaScript Document
function checkData()
{
	return true;	
}
function changePassword(id)
{
	if($("#password_new").val()!=$("#password_new_confirm").val())
	{
			alert('Bạn nhập sai password !');
			$("#password_new").focus();
			return false;
	}
	$.post("ajax.php?fnc=user.process&path=main",
		{'action':'change_pass','id':id,'password_new':$("#password_new").val(),'password_new_confirm':$("#password_new_confirm").val(),'password_old':$("#password_old").val()},
		function(data)
		{
			if(data==1)
			{
				window.location.reload();	
			}
			else
			{
				alert("Mật khẩu cũ không đúng, xin mời bạn kiểm tra lại");
			}
		}			
	)
}
function searchUser()
{
	var url = "?app=main&page=admin_user&cmd=list";
	url += '&active='+$('#active option:selected').val();
	url += '&q='+$('#q').val();
	window.location.href = url;
}
function lock(id,type)
{
	$.post("ajax.php?fnc=user.process&path=main",
		{'action':'lock','id':id,'type':type},
		function(data)
		{
			if(data==1)
				window.location.reload();
			else
				alert('Đã có lỗi xảy ra');
				
		}			
	)	
}
function getInfor(id,user_name)
{
	$("#user_name_reset").html(user_name);
	$("#user_id").val(id);
}
function resetPassword()
{
		if($("#password_new").val()!=$("#password_new_confirm").val())
		{
				alert('Bạn nhập sai password !');
				$("#password_new").focus();
				return false;
		}
		$.post("ajax.php?fnc=user.process&path=main",
		{
			'action':'reset-password','id':$("#user_id").val(),'password_new':$("#password_new").val(),'password_new_confirm':$("#password_new_confirm").val()
		},
		function(data)
		{
			if(data==1)
			{
				window.location.reload();	
			}
			else
			{
				alert("Đã có lỗi xảy ra, bạn vui lòng kiểm tra lại thông tin");
			}
		}			
	)
}
