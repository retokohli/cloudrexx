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