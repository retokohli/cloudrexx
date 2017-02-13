cx.jQuery(document).ready(function() {
    // reload page automatically when changing locale
    cx.jQuery('#form-locale-select').change(function() {
        cx.jQuery(this).submit();
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
    var placeHolderInputs = cx.jQuery("#form-0 input[type='text']");
    placeHolderInputs.each(function() {
        var placeHolderName = cx.jQuery(this).attr('name');
        var wrappedName = "placeholders['" + placeHolderName + "']";
        cx.jQuery(this).attr('name', wrappedName);
    });
});