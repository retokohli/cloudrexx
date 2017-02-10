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
});