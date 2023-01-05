function close_accordion_section() {
    $('.accordion .accordion-section-title').removeClass('active');
    $('.accordion .accordion-section-content').slideUp(300).removeClass('open');
}
function check_coupon() {
	var amount = 1;
	var currentCouponVal = $("#ascoupon").prop("checked");
	$('.cart input[type="number"]').each(function(){
		if ($(this).val() > 1) {
			amount ++
			$("#ascoupon").attr("disabled", true).prop("checked", true)
		}
	})
	if (amount == 1) {
		$("#ascoupon").attr("disabled", false).prop("checked", currentCouponVal)
	}
}
$(document).ready(function () {
    $('.cart input[type="number"]').change(function () {
        var qty = $(this).val();
        var form = $(this).parent('form');
        check_coupon();
        $(form).append('<input type="hidden" name="action" value="update">');
        $(form).append('<input type="hidden" name="qty" value="' + qty + '">');
        $(form).submit();
    });

    $('.cart #ascoupon').change(function () {
        var qty = $(this).val();
        var form = $(this).parent('form');

        $(form).submit();
    });
    $(document).change(check_coupon())
    
    $('.accordion-section-title').click(function (e) {
        // Grab current anchor value
        var currentAttrValue = $(this).attr('href').split('#')[1];

        if ($(e.target).is('.active')) {
            close_accordion_section();
        } else {
            close_accordion_section();

            // Add active class to section title
            $(this).addClass('active');

            // Open up the hidden content panel
            $(".accordion #" + currentAttrValue).slideDown(300).addClass('open');
        }

        e.preventDefault();
    });

    $('#agb-checkbox').click(function () {
        $('.accordion').toggleClass('active');
    });
    
});