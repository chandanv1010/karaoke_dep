$(document).ready(function() {
	$('.productDetail-buy .btn-up').click(function() {
    	var num_order = parseInt($(this).parents('.quantity').find('.input-text').val());
    	num_order += 1;
		$(this).parent().find('.input-text').val(num_order);
    });
    $('.productDetail-buy .btn-down').click(function() {
    	var num_order = parseInt($(this).parents('.quantity').find('.input-text').val());
    	if(num_order <= 1) {
    		num_order = 1
    	}else {
    		num_order -= 1;
    	}
		$(this).parent().find('.input-text').val(num_order);
    });
});