$(function(){
	 // set default 
	 $('.slider-news .navi a:first').removeClass().addClass('on'); 
	 $('.slide-content div:first').removeClass().addClass('joc-slider slider-on'); 
	 // effect
	 $('.slider-news .navi a').click(function(){
	  // Get id cua slide button & id cua content can chuyen
	  var id = $(this).attr("id").substr(10,1);
	  // Get image tiep theo
	  var src = $("#slider-"+id).find("img").attr("src");
	  // remove button
	  $('.slider-news .navi a').removeClass().addClass("off");
	  $('#slider-btn'+id).removeClass().addClass("on"); 
	  // click
	  $("[class='joc-slider slider-on']").animate({
	   opacity: "-=0.7"
	  }, 500, function(){
	   // finish
	   $(this).css({
		"background-image" : "url(" + src + ")"
	   });
	   $(this).removeClass().addClass("joc-slider slider-off").removeAttr("style"); 
	   $('#slider-'+id).attr("class",'joc-slider slider-on').css("opacity", 0);
	   $('#slider-'+id).animate({
		opacity: 1
	   }, 1500, function(){
	   });
	  });  
	 });
	 
	 
	 $('.hautruong-number').click(function(){
			var id = $(this).text();
			$('#hautruong div').removeClass().addClass("slide-off");
			$('#hautruong-'+id).removeClass().addClass("slide-on"); 

			$('.pages-star .left-p-star a').removeClass().addClass("hautruong-number");
			$('#hautruong-number-'+id).removeClass().addClass("hautruong-number active-number"); 
			
			if(id==1)
			{
				$('.next-left').attr('id','hautruong-5'); 
				$('.next-right').attr('id','hautruong-2'); 
			}
			else if(id==5)
			{
				$('.next-left').attr('id','hautruong-4'); 
				$('.next-right').attr('id','hautruong-1'); 
			}
			else
			{
				$('.next-left').attr('id','hautruong-'+(parseInt(id)-1)); 
				$('.next-right').attr('id','hautruong-'+(parseInt(id)+1)); 
			}
				
			$('#hautruong-'+id).attr("class",'slide-on').css("opacity", 0);
		    $('#hautruong-'+id).animate({
			   opacity: 1
			}, 1500, function(){
		    });
		});
		
		$('#hautruong-next-auto a').click(function(){
			var id = $(this).attr('id').substr(10,1);
			$('#hautruong div').removeClass().addClass("slide-off");
			$('#hautruong-'+id).removeClass().addClass("slide-on"); 

			$('.pages-star .left-p-star a').removeClass().addClass("hautruong-number");
			$('#hautruong-number-'+id).removeClass().addClass("hautruong-number active-number"); 
			
			if(id==1)
			{
				$('.next-left').attr('id','hautruong-5'); 
				$('.next-right').attr('id','hautruong-2'); 
			}
			else if(id==5)
			{
				$('.next-left').attr('id','hautruong-4'); 
				$('.next-right').attr('id','hautruong-1'); 
			}
			else
			{
				$('.next-left').attr('id','hautruong-'+(parseInt(id)-1)); 
				$('.next-right').attr('id','hautruong-'+(parseInt(id)+1)); 
			}
			
			$('#hautruong-'+id).attr("class",'slide-on').css("opacity", 0);
		    $('#hautruong-'+id).animate({
			   opacity: 1
			}, 1500, function(){
		    });
		});
	 
	 
	 
	 
	 
	 
	 
	 $('.ketnoi-number').click(function(){
			var id = $(this).text();
			$('#ketnoi div').removeClass().addClass("slide-off");
			$('#ketnoi-'+id).removeClass().addClass("slide-on"); 

			$('.pages-ketnoi .left-p-ketnoi a').removeClass().addClass("ketnoi-number");
			$('#ketnoi-number-'+id).removeClass().addClass("ketnoi-number active-ketnoi"); 
			
			if(id==1)
			{
				$('.pre-ketnoi').attr('id','ketnoi-5'); 
				$('.next-ketnoi').attr('id','ketnoi-2'); 
			}
			else if(id==5)
			{
				$('.pre-ketnoi').attr('id','ketnoi-4'); 
				$('.next-ketnoi').attr('id','ketnoi-1'); 
			}
			else
			{
				$('.pre-ketnoi').attr('id','ketnoi-'+(parseInt(id)-1)); 
				$('.next-ketnoi').attr('id','ketnoi-'+(parseInt(id)+1)); 
			}
				
			$('#ketnoi-'+id).attr("class",'slide-on').css("opacity", 0);
		    $('#ketnoi-'+id).animate({
			   opacity: 1
			}, 1500, function(){
		    });
		});
		
		$('#ketnoi-next-auto a').click(function(){
			var id = $(this).attr('id').substr(7,1);
			$('#ketnoi div').removeClass().addClass("slide-off");
			$('#ketnoi-'+id).removeClass().addClass("slide-on"); 

			$('.pages-ketnoi .left-p-ketnoi a').removeClass().addClass("ketnoi-number");
			$('#ketnoi-number-'+id).removeClass().addClass("ketnoi-number active-ketnoi"); 
			
			if(id==1)
			{
				$('.pre-ketnoi').attr('id','ketnoi-5'); 
				$('.next-ketnoi').attr('id','ketnoi-2'); 
			}
			else if(id==5)
			{
				$('.pre-ketnoi').attr('id','ketnoi-4'); 
				$('.next-ketnoi').attr('id','ketnoi-1'); 
			}
			else
			{
				$('.pre-ketnoi').attr('id','ketnoi-'+(parseInt(id)-1)); 
				$('.next-ketnoi').attr('id','ketnoi-'+(parseInt(id)+1)); 
			}
			
			$('#ketnoi-'+id).attr("class",'slide-on').css("opacity", 0);
		    $('#ketnoi-'+id).animate({
			   opacity: 1
			}, 1500, function(){
		    });
		});
		
		$('.dep-number').click(function(){
			var id = $(this).text();
			$('#content div').removeClass().addClass("slide-off");
			$('#content-'+id).removeClass().addClass("slide-on"); 
			$('.dep-pages a').removeClass().addClass("dep-number");
			$('#dep-number-'+id).removeClass().addClass("dep-number active-dep");
			if(id==1)
			{
				$('.dep-navi .preview').attr('id','navi-9'); 
				$('.dep-navi .next-x').attr('id','navi-2'); 
			}
			else if(id==9)
			{
				$('.dep-navi .preview').attr('id','navi-8'); 
				$('.dep-navi .next-x').attr('id','navi-1'); 
			}
			else
			{
				$('.dep-navi .preview').attr('id','navi-'+(parseInt(id)-1)); 
				$('.dep-navi .next-x').attr('id','navi-'+(parseInt(id)+1)); 
			}
			$('#content-'+id).attr("class",'slide-on').css("opacity", 0);
		    $('#content-'+id).animate({
			   opacity: 1
			}, 1500, function(){
		    });
		});
		$('.dep-navi a').click(function(){
			var id = $(this).attr('id').substr(5,1);
			$('#content div').removeClass().addClass("slide-off");
			$('#content-'+id).removeClass().addClass("slide-on"); 
			$('.dep-pages a').removeClass().addClass("dep-number");
			$('#dep-number-'+id).removeClass().addClass("dep-number active-dep"); 
			if(id==1)
			{
				$('.dep-navi .preview').attr('id','navi-9'); 
				$('.dep-navi .next-x').attr('id','navi-2'); 
			}
			else if(id==9)
			{
				$('.dep-navi .preview').attr('id','navi-8'); 
				$('.dep-navi .next-x').attr('id','navi-1'); 
			}
			else
			{
				$('.dep-navi .preview').attr('id','navi-'+(parseInt(id)-1)); 
				$('.dep-navi .next-x').attr('id','navi-'+(parseInt(id)+1)); 
			}
			$('#content-'+id).attr("class",'slide-on').css("opacity", 0);
		    $('#content-'+id).animate({
			   opacity: 1
			}, 1500, function(){
		    });			
		});
		
		
		$('.number-gt').click(function(){
			
			var id = $(this).text();
			$('#young-ct div').removeClass().addClass("slide-off");
			$('#young-ct-'+id).removeClass().addClass("slide-on"); 
			$('.gioitre-pages a').removeClass().addClass("number-gt");
			$('#number-gt-'+id).removeClass().addClass("number-gt active-gt");
			if(id==1)
			{
				$('.gioitre-preview').attr('id','move-9'); 
				$('.gioitre-next').attr('id','move-2'); 
			}
			else if(id==9)
			{
				$('.gioitre-preview').attr('id','move-8'); 
				$('.gioitre-next').attr('id','move-1'); 
			}
			else
			{
				$('.gioitre-preview').attr('id','move-'+(parseInt(id)-1)); 
				$('.gioitre-next').attr('id','move-'+(parseInt(id)+1)); 
			}
			$('#young-ct-'+id).attr("class",'slide-on').css("opacity", 0);
		    $('#young-ct-'+id).animate({
			   opacity: 1
			}, 1500, function(){
		    });
		});
		$('.dep-gt a').click(function(){
			var id = $(this).attr('id').substr(5,1);
			$('#young-ct div').removeClass().addClass("slide-off");
			$('#young-ct-'+id).removeClass().addClass("slide-on"); 
			$('.gioitre-pages a').removeClass().addClass("number-gt");
			$('#number-gt-'+id).removeClass().addClass("number-gt active-gt");
			if(id==1)
			{
				$('.gioitre-preview').attr('id','move-9'); 
				$('.gioitre-next').attr('id','move-2'); 
			}
			else if(id==9)
			{
				$('.gioitre-preview').attr('id','move-8'); 
				$('.gioitre-next').attr('id','move-1');  
			}
			else
			{
				$('.gioitre-preview').attr('id','move-'+(parseInt(id)-1)); 
				$('.gioitre-next').attr('id','move-'+(parseInt(id)+1)); 
			}
			$('#young-ct-'+id).attr("class",'slide-on').css("opacity", 0);
		    $('#young-ct-'+id).animate({
			   opacity: 1
			}, 1500, function(){
		    });			
		});	
		var check = 0;
		var clear;
		$("#play").click(function(){
			var id = 2;
			var text = $(this).text();
			if(text=="Xem tự động")
			{
				$(this).text("Dừng lại");
				$(this).attr('title',"Dừng lại");
			}
			else
			{
				$(this).text("Xem tự động");
				$(this).attr('title',"Xem tự động");
			}
			if(check%2==0)
			{
				 clear = setInterval(function(){
					$('#young-ct div').removeClass().addClass("slide-off");
					$('#young-ct-'+id).removeClass().addClass("slide-on"); 
					$('.gioitre-pages a').removeClass().addClass("number-gt");
					$('#number-gt-'+id).removeClass().addClass("number-gt active-gt");
					if(id==1)
					{
						$('.gioitre-preview').attr('id','move-9'); 
						$('.gioitre-next').attr('id','move-2'); 
					}
					else if(id==9)
					{
						$('.gioitre-preview').attr('id','move-8'); 
						$('.gioitre-next').attr('id','move-1');  
					}
					else
					{
						$('.gioitre-preview').attr('id','move-'+(parseInt(id)-1)); 
						$('.gioitre-next').attr('id','move-'+(parseInt(id)+1)); 
					}
					$('#young-ct-'+id).attr("class",'slide-on').css("opacity", 0);
					    $('#young-ct-'+id).animate({
						   opacity: 1
						}, 1000, function(){
					    });
					id++;
					if(id==10)
					{
						id=1;
					}
				
				},3000);
			} else {
				clearInterval(clear);
			}	
			check++;
		});	
		
		
		$('.sukien a').click(function(){
			var id = $(this).attr('id').substr(6,1);
			$('#tinsukien div').removeClass().addClass("slide-off");
			$('#tinsukien-'+id).removeClass().addClass("slide-on"); 
			if(id==1)
			{
				$('.sukien-pre').attr('id','moves-9'); 
				$('.sukien-next').attr('id','moves-2'); 
			}
			else if(id==9)
			{
				$('.sukien-pre').attr('id','moves-8'); 
				$('.sukien-next').attr('id','moves-1');
			}
			else
			{
				$('.sukien-pre').attr('id','moves-'+(parseInt(id)-1)); 
				$('.sukien-next').attr('id','moves-'+(parseInt(id)+1)); 
			}
			$('#tinsukien-'+id).attr("class",'slide-on').css("opacity", 0);
		    $('#tinsukien-'+id).animate({
			   opacity: 1
			}, 1500, function(){
		    });			
		});
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	});