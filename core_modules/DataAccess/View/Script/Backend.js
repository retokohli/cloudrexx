cx.jQuery(document).ready(function() {
    addGenerateButton();
    initializeConditions();
    loadJsonMethods();
});

function addGenerateButton() {
    let inputField = cx.jQuery('#form-0-apiKey');

    if (undefined === inputField.val() || inputField.val().length) {
        return;
    }

    // Show btn
    const btnText = cx.variables.get('TXT_CORE_MODULE_DATA_ACCESS_GENERATE_BTN', 'DataAccess');
    let btn = '<button id="generate-api-key">' + btnText + '</button>';
    inputField.after(btn);

    // Generate API-Key on click.
    cx.jQuery('#generate-api-key').click(function (event) {
        event.preventDefault();

        let apiKey = Math.random().toString(36).substring(7);
        inputField.val(apiKey);
        cx.jQuery('#generate-api-key').hide();
    });
}

function initializeConditions() {
    let conditionWrapper = cx.jQuery('#form-0-accessCondition');

    if (undefined === conditionWrapper || !conditionWrapper.length) {
        return;
    }

    addEventListenerForConditions(conditionWrapper);
}

function loadJsonMethods() {
    cx.jQuery('.json-adapter').change(function () {
        var el = cx.jQuery(this);
        cx.ajax(
            'DataAccess',
            'getJsonControllerMethods',
            {
                type: 'POST',
                data: {
                    controller: cx.jQuery(el).find('option:selected').text()
                },
                success: function(response) {
                    if (response.status == 'success') {
                        replaceSelectOptions(el, response.data);
                    }
                },
            }
        );
    });
}

function replaceSelectOptions(select, newOptions) {
    const methodsSelect = cx.jQuery(select).siblings();

    // Clear select from options.
    cx.jQuery(methodsSelect).empty();

    // Add new option for each element in newOptions
    cx.jQuery.each(newOptions, function(key, value) {
        cx.jQuery(methodsSelect).append('<option>'+value+'</option>').attr('value', value);
    });

    // Select first option
    cx.jQuery(methodsSelect).find('option:first').attr('selected', true);
}