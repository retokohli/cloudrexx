jQuery(document).ready(function() {
    function registerFormHandlers() {
        jQuery("#multisite_signup_form").submit(submitHandler);
        jQuery('#multisite_email_address').bind('change', verifyEmail);
        jQuery('#multisite_address').bind('change', verifyAddress);
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

    function verifyAddress() {
        jQuery('.multisite-address').next('.alert').remove();

        if (!this.checkValidity()) {
            return;
        }

        jQuery.ajax({
            dataType: "json",
            url: cx_multisite.addressUrl,
            data: {multisite_address : jQuery(this).val()},
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
                    jQuery(cx_multisite.signUpForm).find('.multisite-status').find('.alert').remove();
                    jQuery(cx_multisite.signUpForm).find('.multisite-form').find('.alert').remove();
                    jQuery('.multisite-status').hide();
                    jQuery(cx_multisite.signUpForm).find('.modal-header').hide();
                    jQuery(cx_multisite.signUpForm).find('.multisite-form').hide();
                    jQuery(cx_multisite.signUpForm).find('.multisite-progress').show();
                    jQuery(cx_multisite.signUpForm).find('.modal-footer').hide();
                },
                success: parseResponse,
                error: function(response, statusMessage, error) {
// TODO: replace statusMessage by a generic error message with guidance on how to contact the helpdesk
                    message = 'Unfortunately, the build process of your website failed.';
                    setMessage(statusMessage, 'danger');
                }
            });
        } catch (e) {}

        // always return false. We don't want to form to get actually submitted
        // as everything is done using AJAX
        return false;
    }

    function parseResponse(response) {
        jQuery(cx_multisite.signUpForm).find('.multisite-progress').hide();

        if (!response.message && !response.data) return;

        switch (response.status) {
            case 'success':
                message = response.data.message;
                setMessage(message, 'success');
                break;

            case 'error':
            default:
                errorObject = null;
                errorType = 'danger';
                errorMessage = response.message;
                if (typeof(response.message) == 'object') {
                    errorObject = typeof(response.message.object) != null ? response.message.object : null;
                    errorMessage = typeof(response.message.message) != null ? response.message.message : null;
                    errorType = typeof(response.message.type) != null ? response.message.type : null;
                }
                jQuery(cx_multisite.signUpForm).find('.multisite-form').show();
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

            case 'address':
                jQuery('<div class="alert alert-' + type + '" role="alert">' + message + '</div>').insertAfter(jQuery('.multisite-address'));
                break;

            case 'form':
                jQuery(cx_multisite.signUpForm).find('.modal-header').hide();
                jQuery(cx_multisite.signUpForm).find('.multisite-form').hide();
                jQuery(cx_multisite.signUpForm).find('.modal-footer').hide();
                jQuery('.multisite-status').children().remove();
                jQuery('.multisite-status').append('<div class="alert alert-' + type + '" role="alert">' + message + '</div>');
                jQuery('.multisite-status').show();
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
