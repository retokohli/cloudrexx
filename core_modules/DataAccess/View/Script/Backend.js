cx.jQuery(document).ready(function() {
    addGenerateButton();
    initializeConditions();
    addEventListenerForConditions();
    togglePermission();
    loadJsonMethods();
});

// This and the next method are copied from https://stackoverflow.com/a/27747377
function dec2hex (dec) {
  return ('0' + dec.toString(16)).substr(-2)
}

// generateId :: Integer -> String
function generateId (len) {
  var arr = new Uint8Array((len || 40) / 2)
  window.crypto.getRandomValues(arr)
  return Array.from(arr, dec2hex).join('')
}

function addGenerateButton() {
    let inputField = cx.jQuery('#form-0-apiKey');

    if (undefined === inputField.val() || inputField.val().length) {
        return;
    }

    // Show btn
    const btnText = cx.variables.get('TXT_CORE_MODULE_DATA_ACCESS_GENERATE_BTN', 'DataAccess/lang');
    let btn = ' <button id="generate-api-key">' + btnText + '</button>';
    inputField.after(btn);

    // Generate API-Key on click.
    cx.jQuery('#generate-api-key').click(function (event) {
        event.preventDefault();

        inputField.val(generateId(20));
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

function addEventListenerForConditions(conditionWrapper) {
    cx.jQuery(conditionWrapper).find('.condition-fields').change(function() {
        changeConditionInputName(this, true);
    });

    cx.jQuery(conditionWrapper).find('.condition-operations').change(function() {
        changeConditionInputName(this, false);
    });

    cx.jQuery(conditionWrapper).find('.delete').click(function () {
        cx.jQuery(this).closest('.condition-row').remove();
    });
}

function changeConditionInputName(el, fieldChanged) {
    const newValue = cx.jQuery(el).find(":selected").text();
    const input = cx.jQuery(el).parent().find('.condition-input');
    const oldName = input.attr('name');
    const oldNameParts = oldName.split('[');
    const oldNameField = oldNameParts[1].split(']')[0];
    const oldNameOp = oldNameParts[2].split(']')[0];

    let replace = '[' + oldNameField + '][' + newValue + ']';

    if (fieldChanged) {
        replace = '[' + newValue + '][' + oldNameOp + ']';
    }

    const newName = oldName.replace(
        '[' + oldNameField + '][' + oldNameOp + ']',
        replace
    );

    cx.jQuery(el).parent().find('.condition-input').attr('name', newName);
}

function togglePermission() {
    let permissionWrapper = cx.jQuery('.permission-legend').parent();

    if (undefined === permissionWrapper || !permissionWrapper.length) {
        return;
    }

    cx.jQuery(permissionWrapper).find('.permission-legend').click(function()
    {
        cx.jQuery(this).parent().find('.permission-content').slideToggle();
    });
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