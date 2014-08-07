jQuery(document).ready(function() {
    function registerFormHandlers() {
        jQuery("#multisite_signup_form").submit(submitHandler);
        jQuery('#multisite_email_address').bind('change', verifyEmail);
    }

    function verifyEmail() {
        jQuery('#multisite_email_address').next('.alert').remove();

        if (!this.checkValidity()) {
            return;
        }

        jQuery.ajax({
            dataType: "json",
            url: cx_multisite.emailUrl,
            data: {multisite_email_address : jQuery(this).val()},
            type: "POST",
            success: parseResponse
        });
    }

    function submitHandler() {
        try {
            cx_multisite.signUpForm = this;
            jQuery.ajax({
                dataType: "json",
                url: cx_multisite.signUpUrl,
                data: {
                    multisite_email_address : jQuery(this).find("#multisite_email_address").val(),
                    multisite_address : jQuery(this).find("#multisite_address").val()
                },
                type: "POST",
                beforeSend: function(){
                    // show progress screen
                    jQuery(cx_multisite.signUpForm).find('.alert').remove();
                    jQuery('.multisite-status').hide();
                    jQuery(cx_multisite.signUpForm).find('.multisite-form').hide();
                    jQuery(cx_multisite.signUpForm).find('.multisite-progress').show();
                    jQuery(cx_multisite.signUpForm).find('.modal-footer').hide();
                },
                success: parseResponse,
                error: function(response, statusMessage, error) {
// TODO: replace statusMessage by a generic error message with guidance on how to contact the helpdesk
                    setMessage(statusMessage, 'danger');
                }
            });
        } catch (e) {}

        // always return false. We don't want to form to get actually submitted
        // as everything is done using AJAX
        return false;
    }

    function parseResponse(response) {
        switch (response.status) {
            case 'success':
                setMessage(response.message, 'success');
                break;

            case 'error':
            default:
                errorObject = null;
                errorObject = 'danger';
                errorMessage = response.message;
                if (typeof(response.message) == 'object') {
                    errorObject = typeof(response.message.object) != null ? response.message.object : null;
                    errorMessage = typeof(response.message.message) != null ? response.message.message : null;
                    errorType = typeof(response.message.type) != null ? response.message.type : null;
                }
                jQuery(cx_multisite.signUpForm).find('.multisite-form').show();
                jQuery(cx_multisite.signUpForm).find('.multisite-progress').hide();
                jQuery(cx_multisite.signUpForm).find('.modal-footer').show();
                setMessage(errorMessage, errorType, errorObject);
                break;
        }
    }

    function setMessage(message, type, errorObject) {
        if (!type) type = 'info';

        switch (errorObject) {
            case 'email':
                jQuery('<div class="alert alert-' + type + '" role="alert">' + message + '</div>').insertAfter(jQuery('#multisite_email_address'));
                break;

            default:
                jQuery('.multisite-status').children().remove();
                jQuery('.multisite-status').append('<div class="alert alert-' + type + '" role="alert">' + message + '</div>');
                jQuery('.multisite-status').show();
                break;
        }
    }

    registerFormHandlers();
});
