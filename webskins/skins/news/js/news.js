$(document).ready(function(){
    $("#send_comment").click(function(){
        var name = $("#fullname").val();
        var email = $("#email").val();
        var content = $("#cm_content").val();
        if(name != "" && name != "Họ tên")
        {
            if(email != "" && email!= "Email")
            {      
                if(valid(email) == false) 
                {
                    alert('Địa chỉ email không hợp lệ');
                    $('#email').focus();
                }
                else
                {
                    if(content != "" && content != "Nội dung" && content.length > 10)
                    {
                        var news_id = $("#news_id").val();
                        var title = $("#news_title").val();
                        
                        $.ajax({
            	            url: 'ajax.php?path=news&fnc=post_comment&id='+news_id+"&title="+title,
            	            type:'POST',
            	            dataType:'json',            
            	            data:{fullname:name, mail:email, comment:content},
            	            success:function(data)
            	            {
            	            	alert(data.text);
            	            },
            	            timeout:function(){}
            	        });
                    }
                    else
                    {
                        alert('Bạn chưa nhập nội dung hoặc nội dung phải nhiều hơn 30 ký tự.');
                        $('#cm_content').focus();
                    }
                }
            }
            else
            {
                alert('Bạn chưa nhập email.');
                $('#email').focus();
            }
        }
        else
        {
            alert('Bạn chưa nhập họ tên.');        
            $('#fullname').focus();
        }
    });
    $("#fullname").focus(function()
    {
        if($.trim($(this).val()) == "Họ tên")
            $(this).val('');
    });
    $("#fullname").blur(function()
    {
        if($.trim($(this).val()) == "" )
            $(this).val('Họ tên');
    });
    $("#email").focus(function()
    {
        if($.trim($(this).val()) == "Email")
            $(this).val('');
    });
    $("#email").blur(function()
    {
        if($.trim($(this).val()) == "" )
            $(this).val('Email');
    });
    $("#cm_content").focus(function()
    {
        if($.trim($(this).val()) == "Nội dung")
            $(this).val('');
    });
    $("#cm_content").blur(function()
    {
        if($.trim($(this).val()) == "" )
            $(this).val('Nội dung');
    });
})
function valid(email)
{
    var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    return reg.test(email);
}