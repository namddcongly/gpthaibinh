$(document).ready(

function() {
	var imgs = $("#fb-slider ul li img");
	var leng = $("#fb-slider").find("img").length;
	var frame = 0;
	var current_frame = 1;
	var current = 0;
	var width = 610;
	var size = 10;
	var play = 0;
	if ((leng % size) == 0) frame = parseInt(leng / size);
	else
	frame = parseInt((leng / size) + 1);

	var obj = $('#fb-slider ul');

	$("#next").click(function() {
		if (current_frame < frame) {
			current_frame += 1;
			frame_click(-width);
		}
	});
	$("#prev").click(function() {
		if (current_frame > 1) {
			current_frame -= 1;
			frame_click(width);
		}
	});
	$("#fb-slider ul li img").click(function() {
		for (var i = 0; i < leng; i++) {
			$(imgs[i]).css('border', '');
			if ($(this).attr('src') == $(imgs[i]).attr('src')) current = i;
		}

		img_click($(this));
	});
	$("#n_one").click(function() {
		next_one(0);
	});

	$("#p_one").click(function() {
		if (current > 0) {
			current -= 1;

			if (current_frame > 1 && current < (current_frame - 1) * size) {
				current_frame -= 1;
				frame_click(width);
			}

			img_click($(imgs[current]));
		}
	});

	function img_click(obj) {
		$("#show").animate({
			opacity: 0,
		}, 1000, function() {
			$("#show").attr('src', obj.attr('src'));
			$('#g-descview').html( obj.attr('title') );
		});
		$("#show").animate({
			opacity: 1,
		}, 1000, function() {

		});
		var img = $("#fb-slider ul li img");
		img.css('border', '');
		obj.css('border', '1px solid red');
	}

	function frame_click(number) {
		obj.animate({
			'left': (number < 0 ? '-=' : '+=') + Math.abs(number) + 'px'
		}, "slow");
	}
	$("#play").click(function() {
		if ($(this).attr("rel") == 0) {
			$(this).html('Tạm dừng');
			$(this).attr('rel', 1);
			auto_play();
		} else {
			$(this).html('Xem tự động');
			$(this).attr('rel', 0);
		}
	});

	function auto_play() {
		// $("#show").fadeOut( 0 );
		if ($("#play").attr("rel") == 1) {
			if (current == leng - 1) {
				current_frame = 1;
				current = -1;
				frame_click((frame - 1) * width);
			}

			next_one();

			setTimeout(function() {
				auto_play();

			}, 4960);

		}

	}

	function next_one() {

		if (current < leng - 1) {
			current += 1;

			if (current == current_frame * size) {
				current_frame += 1;
				frame_click(-width);

			}
			//$( '#show' ).fadeOut( 60, function() { $(this).fadeIn( 1500,function (){ $(this).delay( 0 ).fadeOut(2400)} )} );
			img_click($(imgs[current]));
		}
	}
});