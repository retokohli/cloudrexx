cx.jQuery(document).ready(function() {

    var languageSelectForm = cx.jQuery('#form-language-select');
    // reload page automatically when changing language
    languageSelectForm.change(function() {
        cx.jQuery(this).submit();
    });

    // add hidden field for selected language to placeholder form
    cx.jQuery('#form-0').append(function() {
        var hiddenInput = cx.jQuery("<input type='hidden' />");
        var languageSelect = languageSelectForm.find('select');
        hiddenInput.attr('name', languageSelect.attr('name'));
        hiddenInput.val(languageSelect.val());
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

    // add reset button to each placeholder
    cx.jQuery("#form-0 .group").append(
      "<input type=\"button\" class=\"reset-placeholder\" value=\"" +
      cx.variables.get("resetText", "Locale/Locale") +
      "\" />"
    );

    cx.jQuery("input.reset-placeholder").click(function() {
        resetPlaceholder(this);
    });

    function resetPlaceholder(button) {
        var placeholderName = cx.jQuery(button).siblings("label").html();
        var languageCode = cx.jQuery("input[name='languageCode'").val();
        var frontend = cx.jQuery("#subnavbar_level2 ul li a[title='Frontend']").hasClass("active");
        // @TODO: get component name dynamically
        var componentName = "Core";

        cx.ajax(
          "Locale",
          "getPlaceholderDefaultValue",
          {
              data: {
                  placeholderName: placeholderName,
                  languageCode: languageCode,
                  frontend: frontend,
                  componentName: componentName
              },
              success: function(json) {
                  if (json.data) {
                      cx.jQuery(button).siblings(".controls").children("input").val(json.data);
                  } else {
                      alert("default value not found.");
                  }
              }
          }
        );

    }
});