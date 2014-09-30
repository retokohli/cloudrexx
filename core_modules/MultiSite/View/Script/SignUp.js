function cx_multisite_signup(defaultOptions) {
    var options = defaultOptions;
    var ongoingRequest = false;
    var ongoingSetup = false;
    var submitRequested = false;
    var signUpForm;
    var objModal;
    var objMail;
    var objAddress;
    var objTerms;

    function initSignUpForm() {
        signUpForm = jQuery('#multisite_signup_form');
        objModal = signUpForm.parents('.modal');
        objModal.on('show.bs.modal', init);
        objModal.find('.multisite_cancel').on('click', cancelSetup);

        signUpForm.submit(submitForm);

        objMail = objModal.find('#multisite_email_address');
        objMail.bind('change', verifyEmail);

        objAddress = objModal.find('#multisite_address');
        objAddress.bind('change', verifyAddress);

        objTerms = objModal.find('#multisite_terms');
        objTerms.bind('change', verifyTerms);

        objModal.find('.multisite_submit').on('click', submitForm);

        init();
    }

    function cancelSetup() {
        ongoingRequest = false;
        ongoingSetup = false;
        submitRequested = false;
    }

    function init() {
        if (ongoingRequest) {
            return;
        }

        if (typeof(cx_multisite_options) != 'undefined') {
            options = cx_multisite_options;
        }

        setFormHeader(options.headerInitTxt);
        hideProgress();
        showForm();

        clearFormStatus();

        if (typeof(options.email) == 'string' && !objMail.val()) {
            objMail.val(options.email);
        }
        //objMail.data('valid', false);
        objMail.data('verifyUrl', options.emailUrl);
        objMail.change();

        if (typeof(options.address) == 'string' && !objAddress.val()) {
            objAddress.val(options.address);
        }
        //objAddress.data('valid', false);
        objAddress.data('verifyUrl', options.addressUrl);
        objAddress.change();

        //objTerms.data('valid', false);
        objTerms.change();

        setFormButtonState('close', false);
        setFormButtonState('cancel', true, true);
        setFormButtonState('submit', true, true);
    }

    function verifyEmail() {
        verifyInput(this, {multisite_email_address : jQuery(this).val()});
    }

    function verifyAddress() {
        verifyInput(this, {multisite_address : jQuery(this).val()});
    }

    function verifyTerms() {
        verifyInput(this);
    }

    function verifyInput(domElement, data) {
        jQuery(domElement).parent().next('.alert').remove();

        jQuery(domElement).data('valid', false);
        if (!domElement.checkValidity()) {
            verifyForm();
            return;
        }

        jQuery(domElement).prop('disabled', true);

        if (jQuery(domElement).data('verifyUrl')) {
            jQuery.ajax({
                dataType: "json",
                url: jQuery(domElement).data('verifyUrl'),
                data: data,
                type: "POST",
                success: function(response){parseResponse(response, domElement);}
            });
        } else {
            parseResponse({status:'success',data:{status:'success'}}, domElement);
        }
    }

    function verifyForm() {
        setFormButtonState('submit', true, isFormValid());
    }

    function submitForm() {
        try {
            if (!verifyAddress() || !verifyEmail() || !verifyTerms() ||  !isFormValid()) {
                return;
            }

            if (submitRequested) {
                return;
            }
            //signUpForm.find(':input').prop('disabled', true);
            submitRequested = true;
            callSignUp();
        } catch (e) {}

        // always return false. We don't want to form to get actually submitted
        // as everything is done using AJAX
        return false;
    }

    function isFormValid() {
        return (   objMail.data('valid')
                && objAddress.data('valid')
                && objTerms.data('valid'));
    }

    function setFormHeader(headerTxt) {
        objModal.find('.modal-header .modal-title').html(headerTxt);
    }

    function setFormButtonState(btnName, show, active) {
        var btn = objModal.find('.multisite_' + btnName);
        show ? btn.show() : btn.hide();
        btn.prop('disabled', !active);
    }
    
    function callSignUp() {
        try {
            ongoingRequest = true;
            setFormButtonState('close', true, true);
            setFormButtonState('cancel', false, false);
            setFormHeader(options.headerSetupTxt);

            hideForm();
            showProgress();

            jQuery.ajax({
                dataType: "json",
                url: options.signUpUrl,
                data: {
                    multisite_email_address : objMail.val(),
                    multisite_address : objAddress.val()
                },
                type: "POST",
                success: function(response){parseResponse(response, null);},
                error: function() {
                    showSystemError();
                }
            });
        } catch (e) {
            console.log(e);
        }
    }

    /**
     * @param {{data:{loginUrl}}} response The url to which the user gets redirected if auto-login is active.
     * @param {jQuery} objCaller
     */
    function parseResponse(response, objCaller) {
        var type, message, errorObject,errorMessage,errorType;
        hideProgress();

        if (!response.status) {
            showSystemError();
            return;
        }

        // handle form validation
        if (objCaller) {
            jQuery(objCaller).prop('disabled', false);

            // fetch verification state of form element
            if (response.status == 'success') {
                jQuery(objCaller).data('valid', true);
            } else {
                jQuery(objCaller).data('valid', false);
                type = 'danger';
                message = response.message;
                if (typeof(response.message) == 'object') {
                    message = typeof(response.message.message) != null ? response.message.message : null;
                    type = typeof(response.message.type) != null ? response.message.type : null;
                }
                jQuery('<div class="alert alert-' + type + '" role="alert">' + message + '</div>').insertAfter(jQuery(objCaller).parent());
            }
            verifyForm();

            return;
        }

        // handle signup
        switch (response.status) {
            case 'success':
                // this is a workaround for 
                if (!response.message && !response.data) {
                    showSystemError();
                    return;
                }

                // fetch message
                message = response.data.message;

                // redirect to website, in case auto-login is active
                if (message == 'auto-login') {
                    window.location.href = response.data.loginUrl;
                    return;
                }

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
                setMessage(errorMessage, errorType, errorObject);
                break;
        }
    }

    function setMessage(message, type, errorObject) {
        var objElement;
        if (!type) type = 'info';
        objElement = null;

        switch (errorObject) {
            case 'email':
                objElement = objMail;
                /* FALLTHROUGH */
            case 'address':
                if (!objElement) objElement = objAddress;

                setFormHeader(options.headerInitTxt);
                setFormButtonState('close', false);
                setFormButtonState('cancel', true, true);
                hideProgress();
                showForm();
                jQuery('<div class="alert alert-' + type + '" role="alert">' + message + '</div>').insertAfter(objElement);
                objElement.data('valid', false);
                cancelSetup();
                break;

            case 'form':
                setFormHeader(options.headerErrorTxt);
                setFormButtonState('close', false);
                setFormButtonState('cancel', true, true);
                hideForm();
                hideProgress();
                setFormStatus(type, message);
                cancelSetup();
                break;

            default:
                setFormHeader(options.headerSuccessTxt);
                setFormButtonState('close', true, true);
                setFormButtonState('cancel', false);
                hideForm();
                hideProgress();
                setFormStatus(type, message);
                cancelSetup();
                break;
        }
    }

    function showSystemError() {
        setMessage(options.messageErrorTxt, 'danger');
    }

    function showForm() {
        objModal.find('.multisite-form').show();
        jQuery('#multiSiteSignUp').find('.modal-body').css({'min-height': jQuery('#multiSiteSignUp').find('.multisite-form').height()});
    }

    function hideForm() {
        objModal.find('.multisite-form').hide();
    }

    function showProgress() {
        var message = options.messageBuildTxt;
        message = message.replace('%1$s', '<strong>' + objMail.val() + '</strong>');
        message = message.replace('%2$s', '<strong>' + objAddress.val() + '.' + options.multisiteDomain + '</strong>');
        objModal.find('.multisite-progress div').html(message);
        objModal.find('.multisite-progress').show();
    }

    function hideProgress() {
        objModal.find('.multisite-progress').hide();
    }

    function clearFormStatus() {
        objModal.find('.multisite-status').hide();
        objModal.find('.multisite-status').children().remove();
    }

    function setFormStatus(type, message) {
        clearFormStatus();
        objModal.find('.multisite-status').append('<div class="alert alert-' + type + '" role="alert">' + message + '</div>');
        objModal.find('.multisite-status').show();
    }

    initSignUpForm();
}

jQuery(document).ready(cx_multisite_signup(cx_multisite_options));
