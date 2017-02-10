cx.jQuery(document).ready(function() {
    // reload page automatically when changing locale
    cx.jQuery('#form-locale-select').change(function() {
        cx.jQuery(this).submit();
    });
});