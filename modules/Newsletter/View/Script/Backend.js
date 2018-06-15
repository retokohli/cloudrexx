cx.jQuery(document).ready(function(){
  var consentConfirmDiv = jQuery('#consentConfirmdiv'),
      consentCheckbox   = jQuery('#consentConfirm'),
      checkboxChange    = 0,
      errorMsg          = cx.variables.get('NEWSLETTER_CONSENT_CONFIRM_ERROR', 'Newsletter');

  // Checks a url values to hide a consent confirm div during edit by default
  if (
    jQuery('form[name="userAddEdit"]') &&
    getUrlVariables()['tpl'] == 'edit' &&
    getUrlVariables()['id'] &&
    getUrlVariables()['id'] != 0
  ) {
    consentConfirmDiv.addClass('inactive');
    consentCheckbox.prop('checked', true);
  }

  jQuery('input[name^="newsletter_recipient_associated_list"]').change(function(){
    if (consentConfirmDiv.hasClass('inactive')) {
      consentCheckbox.prop('checked', false);
      consentConfirmDiv.removeClass('inactive');
      consentConfirmDiv.addClass('active');
      checkboxChange = 1;
    }
  });

  jQuery('input[name="imported"]').click(function(e){
    if (!jQuery('#consentConfirmImport').is(':checked')) {
      e.preventDefault();
      showErrorMsg(errorMsg);
    }
  })

  jQuery('input[name="newsletter_recipient_save"], input[name="newsletter_import_plain"]').click(function(e){
    if (checkboxChange && !consentCheckbox.is(':checked')) {
      e.preventDefault();
      showErrorMsg(errorMsg);
    } else if (!consentCheckbox.is(':checked')) {
      e.preventDefault();
      showErrorMsg(errorMsg);
    }
  });
});

/*
 * Get a url variables with value
 */
function getUrlVariables()
{
  var vars = {};
  var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
    vars[key] = value;
  });
  return vars;
}

/**
 * Show error message
 */
function showErrorMsg(errorMsg)
{
 if (jQuery('.consentError').length === 0) {
    jQuery('#subnavbar_level1').removeClass('no_margin');
    jQuery('#subnavbar_level1').after('<br><div class="consentError" id="alertbox">' + errorMsg +'</div>');
  }
}
