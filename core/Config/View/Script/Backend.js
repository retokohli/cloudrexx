(function($){
    $(document).ready(function(){
        $('#defaultCaptcha').change(function() {
            var displayReCaptcha = ($.trim($(this).val()) === 'contrexxCaptcha') ? 'none' : 'table-row';
            $('input[name="recaptchaSiteKey"]').closest('tr').css('display', displayReCaptcha);
            $('input[name="recaptchaSecretKey"]').closest('tr').css('display', displayReCaptcha);
        });
        $('#defaultCaptcha').trigger('change');
    });
})(cx.jQuery);
