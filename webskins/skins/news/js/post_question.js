function refreshCaptcha()
		{
			$.post("ajax.php?fnc=news.process&path=news",
				{'action':'refresh_captcha'},
				function(data)
				{
					$("#captcha").html(data);
				}			
			)
		}
		function sendQuestion()
		{
			
			var regex = /^(([\-\w]+)\.?)+@(([\-\w]+)\.?)+\.[a-zA-Z]{2,4}$/;
			if($("#email").val()=="" || $("#email")==null){
				alert("Bạn chưa nhập email");
				$("#email").focus();
				return false;
			}
			if (!regex.test($("#email").val())){
				alert("Email không đúng định dạnh");
				$("#email").focus();
				return false;
			}
			if($("#title").val()=="" || $("#title").val()==null)
			{
				alert("Bạn chưa nhập tiêu đề");
				$("#title").focus();
				return false;
				
			}
			if($("#question_content").val()=="" || $("#question_content").val()==null)
			{
				alert("Bạn chưa nhập tiêu đề");
				$("#question_content").focus();
				return false;
				
			}
			$.post("ajax.php?fnc=news.process&path=news",
				{'action':'post_question','captcha_code':$("#captcha_code").val(),'email':$("#email").val(),'title':$("#title").val(),'content':$("#question_content").val()},
				function(data)
				{
					alert(data);
					if(data==1) 
						window.location.reload();
					else if(data==0)
						alert("Bạn nhập sai mã bảo mật");
					else
						alert("Đã có lỗi xảy ra bạn vui lòng kiểm tra các thông tin đã nhập");		
				}			
			)
			
		}