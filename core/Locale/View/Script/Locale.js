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
            fallbackField.val("NULL").hide();
        } else {
            fallbackField.show();
        }
    });
}

function copyPages(toLangId) {
    performLanguageAction("copy", toLangId, function(json) {
        cx.ui.dialog({
            content: cx.variables.get("copySuccess", "Locale/Locale"),
            modal: true,
            autoOpen: true
        });
    });
}

function linkPages(toLangId) {
    performLanguageAction("link", toLangId, function(json) {
        cx.ui.dialog({
            content: cx.variables.get("linkSuccess", "Locale/Locale"),
            modal: true,
            autoOpen: true
        });
    });
}

function performLanguageAction(actionName, toLangId, action) {
    var defaultLangId = cx.jQuery(".localeDefault input:checked").parent("tr").children('.localeId').text();
    var toLangRow = cx.jQuery(".localeId:contains(" + toLangId + ")").parent("tr");
    var fromLangId = toLangRow.children(".localeFallback").children("select").val();
    if (fromLangId === "") {
        return;
    } else if (fromLangId == 0) {
        fromLangId = defaultLangId;
    }
    var fromLangRow = cx.jQuery(".localeId:contains(" + fromLangId + ")").parent("tr");
    var fromLangName = fromLangRow.children(".localeLabel").text();
    var toLangName = toLangRow.children(".localeLabel").text();
    showActionDialog(actionName, fromLangName, toLangName, function() {
        var waitDialog = cx.ui.dialog({
            title: cx.variables.get("waitTitle", "Locale/Locale"),
            content: cx.variables.get("waitText", "Locale/Locale"),
            modal: true,
            autoOpen: true,
            open: function(event, ui) {
                cx.jQuery(".ui-dialog-titlebar-close").hide();
            }
        });
        var offset = 0;
        var count = 0;
        while ((offset < count) || offset == 0) {
            cx.ajax(
                "cm",
                actionName,
                {
                    data: {
                        to: toLangId,
                        offset: offset,
                        limit: 1,
                    },
                    async: false,
                    dataType: "json",
                    type: "GET",
                    success: function(json) {
                        if (json.status != "success") {
                            cx.ui.dialog({
                                title: json.status,
                                content: json.message,
                                modal: true,
                                autoOpen: true
                            });
                            return;
                        }
                        offset = json.data.offset;
                        count = json.data.count;
                        var newText = cx.variables.get("waitText", "Locale/Locale") + "\n\n" + offset + " / " + count + " (" + Math.round(offset * 100 / count) + "%)";
                        //console.log(offset + " / " + count + " (" + Math.round(offset * 100 / count) + "%)");
                        waitDialog.getElement().html(newText);
                    }
                }
            );
        }
        waitDialog.close();
        action();
    }, cx.variables.get("warningText", "Locale/Locale").replace("%1", fromLangName).replace("%2", toLangName));
}

/**
 * @param string action Name of action
 * @param string fromLang Language name to copy from
 * @param string toLang Language name to copy to
 * @param function yesAction Function to call when "yes" is clicked
 * @param string checkboxText (optional) Text for checkbox label. If null, no checkbox is shown
 * @return cx.ui.dialog Cx Ui Dialog object
 */
function showActionDialog(action, fromLang, toLang, yesAction, checkboxText) {
    var yesOption = cx.variables.get("yesOption", "Locale/Locale");
    var noOption = cx.variables.get("noOption", "Locale/Locale");
    var buttons = new Object();
    buttons[yesOption] = function() {
        cx.jQuery(this).dialog("close");
        if (checkboxText) {
            if (!dialog.getElement().children().children("#really").is(":checked")) {
                return;
            }
        }
        yesAction();
    };
    buttons[noOption] = function() {cx.jQuery(this).dialog("close");}
    var content = "<p>" + cx.variables.get(action + "Text", "Locale/Locale");
    if (action == 'link') {
        content = content.replace("%1", toLang).replace("%2", fromLang);
    } else {
        content = content.replace("%1", fromLang).replace("%2", toLang);
    }
    if (checkboxText) {
        content += "<br /><br /><input type=\"checkbox\" id=\"really\" class=\"really\" value=\"true\" /> <label for=\"really\" class=\"really\">" + checkboxText + "</label>";
    }
    content += "</p>";
    var dialog = cx.ui.dialog({
        title: cx.variables.get(action + "Title", "Locale/Locale"),
        content: content,
        buttons: buttons,
        modal: true,
        width: 400,
        autoOpen: false
    });
    var yesButton = dialog.getElement().siblings(".ui-dialog-buttonpane").children(".ui-dialog-buttonset").children("button").first();
    yesButton.hide();
    cx.jQuery("#really").change(function() {
        console.log('changed');
        yesButton.toggle(cx.jQuery("#really").is(":checked"));
    });
    dialog.open();
    return dialog;
}