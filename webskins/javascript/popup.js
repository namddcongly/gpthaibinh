	/*$(".show-list").livequery('click', function(e){		
		openPopup($(this));
	});*/
	$(".show-list").click( function(){		
		openPopup($(this));
	});				
	$(".show-tip").mouseover(function(){		
		openPopup($(this));
	});
	/*
	$(".popup-close").click(function(){			
		closePopup();
	});
	*/
	$(".close").click(function(){			
		closePopup();
	});
	
	$(".button-close").click(function(){			
		closePopup();
	});
	$(".popup-background").click(function(){
		closePopup();
	});
	
	$(document).keypress(function(e){
		if(e.keyCode==27 && popupFlag==1){
			closePopup();
		}
	});		
	
	var popupFlag = 0;
	
	function openPopup(obj){
		if(popupFlag==0){			
			//var pos = obj.position();	
			var thisParent = obj.parent();
			var parentTop = thisParent.offset().top;
			var rel = obj.attr('rel');
			var windowWidth = $(window).width();
			var divWidth = $("#"+rel).width(); 
			var divHeight = obj.height(); 
		//	var windowH = $(window).height();
			var left = (windowWidth - divWidth)/2;
			//var left = thisParent.offset().left + obj.width()/2 -  divWidth/2;
			if(left+divWidth > windowWidth) left = windowWidth - divWidth;
			var sp = rel.split('-');
			var name = sp[0];
			var id = sp[1];			
			var top = parentTop + divHeight + 10;
			$("#"+name+"-"+id).css({
				"position": "absolute",
				"top": 200,
				"left" : 500,
				//"top": pos.top,
				//"left": pos.left				
			});		
	
			$(".popup-background").css({
				"-moz-opacity": 0,
				"opacity": 0.0,
				"z-index": 10,								
				"top": 0,
				"left": 0
			});
	
			$(".popup-background").fadeIn("fast");
			
			$("#"+name+"-"+id).fadeIn("fast");
			
			popupFlag = rel;
		}
	}
	
	function closePopup(){
		if(popupFlag != 0){
			$(".popup-background").fadeOut("fast");
			$(".popup-choose").fadeOut("fast");
			resetForm('form-'+popupFlag);
			popupFlag = 0;
		}
	}
	
	function resetForm(id) {
		if($('#'+id).length > 0)
		{
			$('#'+id).each(function(){
		        this.reset();
			});
		}		
	}