$(document).ready(function(){
	$('.main-detail-content img').each(function() {
		$(this).wrap("<a rel=\"gm\" title=\"Click vào đây để xem toàn bộ ảnh\" href='" + this.src + "'/>");
	});
	$( "a[rel='gm']" ).colorbox({slideshow: true, loop: false, title: true, slideshowSpeed: 4000, transition:'fade'});

});