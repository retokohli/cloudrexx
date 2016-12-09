cx.jQuery(document).ready(function() {
    // set unique with to all fallback dropdowns
    var maxFallbackSelectWidth = 0;
    // get width of widest fallback dropdown
    cx.jQuery('.localeFallback select').each(function() {
        var fallbackSelectWidth = cx.jQuery(this).width();
        if (fallbackSelectWidth > maxFallbackSelectWidth) {
            maxFallbackSelectWidth = fallbackSelectWidth;
        }
    });
    cx.jQuery('.localeFallback select').width(maxFallbackSelectWidth);
});

cx.jQuery(function() {
    updateCurrent(true);
});

function updateCurrent(init) {
    cx.jQuery(".adminlist tbody tr").each(function(index, el) {
        var locale = cx.jQuery(el);
        var localeId = locale.children(".localeId").text();
        var defField = locale.children(".localeDefault").children("input");
        var fallbackField = locale.children(".localeFallback").children("select");
        // handle fallbacks according to defField
        if (init) {
            fallbackField.children("option[value=" + localeId + "]").remove();
        }
        if (defField.is(":checked")) {
            fallbackField.hide();
        } else {
            fallbackField.show();
        }
    });
}