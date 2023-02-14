var currentHeart = ( $( '#currentHeart' ).val() )*1;
var Timeout_ID = '';
function display_heart_banner(direct) {
	var totalHeart = $( '#totalHeart' ).val();
	
	
	$( '#thumb' + currentHeart + ' div img' ).fadeOut()
	$( '#thumb' + currentHeart + ' div p.sapo' ).fadeOut();
	$( '#thumb' + currentHeart).removeClass('current');	
	
	if(direct == '-')
		currentHeart = currentHeart - 1;
	else
		currentHeart = currentHeart + 1;
	if( currentHeart > totalHeart ) 
		currentHeart = 1;
	else
		if( currentHeart == 0 )
			currentHeart = totalHeart;
	
	$( '#thumb' + currentHeart ).addClass('current');
	$( '#thumb' + currentHeart + ' div img' ).fadeIn(800, function() {
        // Animation complete
		$( '#thumb' + currentHeart + ' div p.sapo' ).fadeIn('slow');
    });
	
		clearTimeout(Timeout_ID);
		setTimeOutHeart();	
	}
	$(function()
	{
	display_heart_banner('+');
	});
	function setTimeOutHeart()
	{
	Timeout_ID = setTimeout("display_heart_banner('+')",5000);
}
function change_banner(i) {
	clearTimeout( Timeout_ID );
	if(i != currentHeart)
	{
		
		
		$( '#thumb' + currentHeart + ' div img' ).fadeOut()
		$( '#thumb' + currentHeart + ' div p.sapo' ).fadeOut();
		$( '#thumb' + currentHeart ).removeClass('current');
		
		currentHeart = i;
		$( '#thumb' + currentHeart + ' div img' ).fadeIn(800, function() {
			// Animation complete
			$( '#thumb' + currentHeart + ' div p.sapo' ).fadeIn('slow');
		});
		$( '#thumb' + currentHeart ).addClass('current');
	}
}