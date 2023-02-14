$(document).ready(function(){			
    $("textarea.comment").elastic().Watermark("Viết bình luận...");
    $("textarea#wall_post").elastic().Watermark("Hãy viết gì đó để chia sẻ cho bạn bè");
    
    $("div.box-user").livequery(function(){
        $(this).hover(function(){
            $(this).addClass('box-user-hover'); 
        },function(){
           $(this).removeClass('box-user-hover'); 
        });    
    });
    /*var link = "";
    var images = "";
    $("textarea#wall_post").livequery("keyup", function(e)
    {
        var parttern = /http\:\/\/[www.]*([^\s]*)/g;
        
        var text = $('#wall_post').val();
        
        if(e.keyCode == 32)
        {        
            if(regs = text.match(parttern))
            {
                if($.trim(link) != $.trim(regs[0]))
                {
                    link = regs[0];
                    
                    $("#detect_link").html('<img src="webskins/skins/channel/images/loading.gif">');
                    $.ajax({
                        url:'ajax.php?path=dashboard&fnc=detect.link',
                        type:'POST',
                        dataType:'json',
                        data:{link : regs[0]},
                        success:function(data){
                            if(data.code == 1)
                            {
                                $("#detect_link").html(data.html); 
                                images = data.img;                               
                            }
                        },
                        timeout:function(){}
                    });
                }                
            }
        }
    });*/
    
    $('p.action_user a.comment_box').livequery("click", function(e){
        var relID =  $(this).attr('rel');
        $("div."+relID).slideDown();
        $("form#" + relID + " textarea").focus();
    });
    
    $('form.form-comment').livequery('submit',function(e)
    {
        var objID = $(this).attr('id');
        var objHtml = $(this).parents('li');
        var text = $.trim($('form#'+objID+' textarea').val());
        
        if(text != "" && text != "Viết bình luận...")
        {            
            $('form#'+objID+' button[type=submit]').attr('disabled', 'disabled');
            $('form#'+objID+' textarea').attr('readonly', 'readonly').addClass('posting_wall');
            
            $.ajax({
                url:'ajax.php?path=dashboard&fnc=post.feed.comment',
                type:'POST',
                dataType:'json',
                data:$(this).serialize(),
                success:function(data){
                         if(data.code == 1){
                            $(objHtml).before(data.html);
                            $('form#'+objID+' button[type=submit]').removeAttr('disabled').parent().hide();
                            $('form#'+objID).parent().prev().hide(); 
                            $('form#'+objID+' textarea').val('Viết bình luận...').removeAttr('readonly').removeClass('comment-large posting_wall');
                            
                         }else{
                            $('form#'+objID+' button[type=submit]').removeAttr('disabled');
                            $('form#'+objID+' textarea').removeAttr('readonly').removeClass('posting_wall');  
                         }
                },
                timeout:function(){
                   $('form#'+objID+' button[type=submit]').removeAttr('disabled');
                   $('form#'+objID+' textarea').removeAttr('readonly').removeClass('posting_wall'); 
                }
            });
        }
        else
            jAlert('Vui lòng nhập nội dung bình luận');
       return false; 
    });
    
    $("textarea.comment").livequery("focus", function(e){
        $(this).addClass('comment-large').parent().parent().parent().prev().show();
        $(this).css({'height':30+'px'});
        $(this).parent().next().show();
    });
    $("textarea.comment").livequery("focusout", function(e){
        if($.trim($(this).val()) =="Viết bình luận..."){
            $(this).parent().parent().parent().prev().hide();   
            $(this).css({'height':15+'px'});
            $(this).removeClass('comment-large').parent().next().hide(); 
        } 
    });
    $("ul.list-item2 li.view_all a.onload").livequery("click", function(e){
        var obj = $(this);
        $.ajax({
            beforeSend:function(){
                 $(obj).removeClass('onload').addClass('cm_loading');
            },
            url: 'ajax.php?fnc=view.all.comment&path=dashboard',
            type:'GET',
            data:{id:$(this).attr('rel')},
            dataType:'json',
            success:function(data){
                 if(data.code ==1){
                     $(obj).parent().after(data.html);
                     $(obj).removeClass('cm_loading');
                     $(obj).parent().remove();
                 }else{
                     $(obj).addClass('onload').removeClass('cm_loading');
                     jAlert('Có lỗi trong quá trình xử lý','Thông báo');
                 }
            }  
        }); 
    });
    
     $('p.action_user a.like_this').livequery("click", function(e){
         var obj = $(this);
         var option =0;
         if($(this).hasClass('unlike_this')) {
             option = 1;
         }
         $.ajax({
             beforeSend:function(){
                 if(option == 0){
                    $(obj).addClass('unlike_this').text("Bỏ thích");
                 }else{
                    $(obj).removeClass('unlike_this').text("Thích");   
                 }
            },
            url: 'ajax.php?fnc=like.feed&path=dashboard&option='+option,
            type:'GET',
            data:{id:$(this).attr('rel')},
            dataType:'json',
            success:function(data){
                       likeCallBack(obj,data,option);
            }  
        });
     });
     function likeCallBack (obj,data,option){
          if(option == 0){
                 if(data.code ==1){
                     $(obj).parent().next().slideDown().children('.list_like').prepend(data.html);
                 }else{
                     $(obj).removeClass('unlike_this').addClass('like_this').text('Thích'); 
                     jAlert('Có lỗi trong quá trình xử lý','Thông báo');
                 }
          }else{
                switch(data.code){
                     case 0 :   
                             $(obj).addClass('unlike_this').text("Bỏ thích"); 
                             jAlert('Có lỗi trong quá trình xử lý','Thông báo');
                             break;

                     case 1 :
                            $(obj).parent().next().children('.list_like').html(data.html);
                            break;    
                     case 2 : 
                            $(obj).parent().next().slideUp().children('.list_like').empty();
                            break;
                }
          }
     }
     
     $("a.btn_hidden_feed").livequery("click", function(e){
            var obj = $(this).parents('.box-user');
            $.ajax({
                url: 'ajax.php?fnc=hide.feed&path=dashboard',
                type:'GET',
                data:{id:$(this).attr('rel')},
                dataType:'json',
                success:function(data){
                    $(data.html).prependTo(obj);        
                }  
            });
     });
     $("a.btn_delete_feed").livequery("click", function(e){
            var obj = $(this).parents('.box-user');
            var id_mongo = $(this).attr('rel');
            jConfirm("Bạn có chắc chắn muốn xóa nội dung này?","Xác nhận",function(res)
            {
                if(res == true)
                {
                    $.ajax({
                        url: 'ajax.php?fnc=delete.feed&path=dashboard',
                        type:'GET',
                        data:{id:id_mongo},
                        dataType:'json',
                        success:function(data)
                        {
                            if(data.code == 1)
                                $(obj).remove();                     
                        }  
                    });
                }
            });
     });
     $("a.agree").livequery("click", function(e){
        var obj = $(this).parents('.box-user');
            $.ajax({
                url: 'ajax.php?fnc=hide.feed&path=dashboard&cmd=1',
                type:'GET',
                data:{id:$(this).attr('rel')},
                dataType:'json',
                success:function(data){
                    $(obj).remove();        
                }  
            });
     });
     $("a.cancel").livequery("click", function(e){
        var obj = $(this).parent();
        $(obj).remove();                       
     }); 
     $("a#view_more").livequery("click", function(e){ 
           var obj = $(this);
           var startView = $(obj).attr('rel'); 
           $.ajax({
               beforeSend:function(){
                    $(obj).attr('rel',startView + 10);
                    $(obj).addClass('cm_loading');
                },
                url: 'ajax.php?fnc=feed.viewmore&path=dashboard',
                type:'GET',
                data:{'start':startView},
                dataType:'json',
                success:function(data){
                    $(obj).removeClass('cm_loading'); 
                    if(data.code ==1){
                        $(obj).parent().before(data.html);
                    }else{
                       var start = $(obj).attr('rel'); 
                       $(obj).attr('rel',start - 10);
                       jAlert('Có lỗi trong quá trình xử lý','Thông báo');  
                    }        
                }  
            });
     });
     
     $("form#my_wall_post_feed").submit(function(){
        var objID = $(this).attr('id');  
        var text = $.trim($('#wall_post').val());
        if(text != "" && text != 'Bạn đang nghĩ gì?')
        {            
         $.ajax({
            beforeSend: function(){
               $('form#'+objID+' button[type=submit]').attr('disabled', 'disabled');
               $('form#'+objID+' textarea').attr('readonly', 'readonly').addClass('posting_wall'); 
            },
            url:'ajax.php?path=dashboard&fnc=feed',
            type:'POST',
            dataType:'json',
            data:$(this).serialize(),
            success:function(data){
                if(data.code ==1)
                {
                    $('form#'+objID+' button[type=submit]').removeAttr('disabled');
                    $('form#'+objID+' textarea').removeClass('posting_wall').removeAttr('readonly').val("Hãy viết gì đó để chia sẻ cho bạn bè của bạn");
                    $("form#"+objID).parent().after(data.html);
                    if(data.blast != "")
                        $(".profile-name .status").html(data.blast);
                }
                else
                {
                    $('form#'+objID+' button[type=submit]').removeAttr('disabled');
                    $('form#'+objID+' textarea').removeClass('posting_wall').removeAttr('readonly');
                    jAlert("Có lỗi trong quá trình xử lý","Thông báo");
                }
                $("#detect_link").html('');    
            }
        });
        }
        else
            jAlert('Vui lòng nhập nội dung');
       return false; 
     });
     $("a.delete_comment").livequery("click", function(e){
         var obj = $(this);  
         var infoID = $(this).attr('rel'); 
        jConfirm("Bạn có chắc chắn muốn xóa bình luận này?","Xác nhận",function(res){
             if(res==true){
                 $.ajax({
                    url:'ajax.php?path=dashboard&fnc=delete.comment',
                    type:'GET',
                    dataType:'json',
                    data:{'info':infoID},
                    success:function(data){
                        if(data.code ==1){
                            $(obj).parents("li").slideUp().remove();
                        }
                    }
                })
             }
        });
        return false; 
     }); 
     $("a.next_img").livequery("click", function(e){
           var next = $(this).attr('rel');
           next = parseInt(next) + 1;
           
           $("#info_image").val(images[next]);
           $("#simg").html((next + 1) +'/'+images.length);
           $("#show_img").attr('src', images[next]);
           $(this).attr('rel', next);
     });
     $("a.prev_img").livequery("click", function(e){
         
           var next = $("a.next_img").attr('rel');
           $("#simg").html(next+'/'+images.length);
           next = parseInt(next) - 1;
           
           $("#info_image").val(images[next]);
           
           $("#show_img").attr('src', images[next]);
           
           $("a.next_img").attr('rel', next);
     });
     $("a.cm_all").livequery("click", function(e){
         var curr = $(this).prev();
         curr.css({"display":"block"});  
         $(this).css({"display":"none"});
     });
});