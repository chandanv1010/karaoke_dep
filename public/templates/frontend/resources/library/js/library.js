$(window).scroll(function() {
	if($(this).scrollTop() > 200)	$('#goTop').stop().animate({ bottom: '60px' }, 500);
	else $('#goTop').stop().animate({ bottom: '-60px' }, 500);
});
$(document).ready(function() {
	$('#goTop').click(function(event) {
		event.preventDefault();
		$('html, body').animate({scrollTop: 0},500)
	});
});