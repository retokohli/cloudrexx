cx.jQuery(document).ready(function() {

    var localeSelectForm = cx.jQuery('#form-locale-select');
    // reload page automatically when changing locale
    localeSelectForm.change(function() {
        cx.jQuery(this).submit();
    });

    // add hidden field for selected language to placeholder form
    cx.jQuery('#form-0').append(function() {
        var hiddenInput = cx.jQuery("<input type='hidden' />");
        var localeSelect = localeSelectForm.find('select');
        hiddenInput.attr('name', localeSelect.attr('name'));
        hiddenInput.val(localeSelect.val());
        return hiddenInput;
    });

    // set width of form labels according to the longest content equally
    var equalWidth = 0;
    var formLabels = cx.jQuery("#form-0 .group label");
    formLabels.each(function() {
        if (cx.jQuery(this).width() > equalWidth) {
            equalWidth = cx.jQuery(this).width();
        }
    });
    formLabels.width(equalWidth);

    // wrap names of placeholder inputs properly
    // to get all values in subarray of post when submitting the form
    var placeholderInputs = cx.jQuery("#form-0 input[type='text']");
    placeholderInputs.each(function() {
        var placeholderName = cx.jQuery(this).attr('name');
        var wrappedName = "placeholders[" + placeholderName + "]";
        cx.jQuery(this).attr('name', wrappedName);
    });
});