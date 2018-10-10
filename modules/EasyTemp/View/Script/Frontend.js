/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */
/* global cx */
cx.ready(function() {
  var jQuery = cx.jQuery;
  jQuery("#application-geburtsdatum,#application-bewilverfall,#application-verfuegbarab")
    .datepicker({
      dateFormat: "dd.mm.yy",
      //defaultDate: 1, // Tomorrow
      //minDate: 0, // Today
      gotoCurrent: true
    });
  // Iff the "Inserat ID" is non-empty, hide both the
  // "Wunschregion" and "Wunschberuf" elements.
  if (jQuery("#application-inseratid").val() !== "") {
    jQuery("#application-wunschregion").parent().hide();
    jQuery("#application-wunschberuf").parent().hide();
  }
  function refresh() {
    var parameters = jQuery("#easytemp-jobs-filter-form")
      .serialize()
      // Remove empty parameter names, like "key="
      .replace(/\w+=(?:&|$)/g, "")
      // Trim trailing "?&" or "&"
      .replace(/\??&$/, "");
    location.search = parameters;
  }
  jQuery("#easytemp-jobs-filter-form input, #easytemp-jobs-filter-form select")
    .bind("change", refresh);
  function application() {
    // Do not -- repeat -- DO NOT use .data("hash") here.
    // .data() tries to convert the large integerish hash to an actual number,
    // losing several digits of precision in the process!
    // .attr() prevents this, and returns the actual string.
    var hash = jQuery("#job-application").attr("data-hash");
    var url =
      cx.variables.get("application_base_url", "EasyTemp") +
      "?hash=" + hash;
    location.href = url;
  }
  jQuery("#job-application")
    .bind("click", application);
  function removeFile(event) {
    var button = jQuery(event.target);
    button.hide();
    var id = button.data("file");
    validate(jQuery("#" + id).val(""));
  }
  jQuery("#easytemp-application-form button.removefile")
    .bind("click", removeFile);
  // Define mandatory fields in the page template
  // by adding the "mandatory" class to its <label>
  var validationRules = {
    anrede: {regex: /^[0-9]{1}$/},
    name: {},
    vorname: {},
    zusatzadresse: {},
    strasse: {},
    plz: {regex: /^[a-z0-9\-\s]*$/i},
    ort: {},
    staat: {regex: /^[0-9a-z]{1,6}$/i},
    geschlecht: {regex: /^(10|20)$/},
    zivilstand: {regex: /^[0-9]{2,3}$/},
    heimatort: {},
    geburtsdatum: {regex: /^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.]([0]?[1-9]|[1][0-2])[.]([0-9]{4}|[0-9]{2})$/},
    muttersprache: {regex: /^[a-z]{2}$/i},
    fahrzeug: {regex: /^[0-9]{1,2}$/},
    fuehrerschein: {},
    telefonp: {regex: /^[0-9\s\+\(\)\.\-]*$/},
    telefong: {regex: /^[0-9\s\+\(\)\.\-]*$/},
    telefonm: {regex: /^[0-9\s\+\(\)\.\-]*$/},
    email: {regex: /^[a-z0-9._-]+@[a-z0-9.-]+\.[a-z]{2,}$/i},
    sozialversnr: {regex: /^[0-9\.]{1,16}$/},
    kuendigungsfrist: {},
    verfuegbarab: {regex: /^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.]([0]?[1-9]|[1][0-2])[.]([0-9]{4}|[0-9]{2})$/},
    bewilstatus: {regex: /^[0-9]{1,2}$/},
    bewilnr: {},
    bewilverfall: {},
    beruferlernt: {},
    wunschregion: {},
    wunschberuf: {regex: /^[a-z0-9]{1,6}$/},
    wunschbranche: {regex: /^[a-z0-9]{1,6}$/},
    anstellungsgrad: {regex: /^\d{2,3}$/},
    anstellungsart: {regex: /^\d$/},
    noattachment: {},
    file1: {regex: /\.pdf$/i, extension: /\.pdf$/},
    file2: {regex: /\.pdf$/i, extension: /\.pdf$/},
    file3: {regex: /\.pdf$/i, extension: /\.pdf$/}
    // Internal:
    // Set by the module:
    //hash: {mandatory: 1, regex: /^[0-9]*$/},
    //inseratid: {mandatory: 1, regex: /^[0-9\-]*$/},
    //language: {mandatory: 1, regex: /^(DE|FR|IT|EN)$/},
    // Not used:
    //bemerkung: {}, // Note: Textarea, has no id attribute
    //response: {mandatory: 1, regex: /^(json|xml|redirect)$/},
    //"redirect-success": {regex: /^(http|https):\/\/[a-z0-9\.\/\-\&\%\?\_]*$/},
    //"redirect-error": {regex: /^(http|https):\/\/[a-z0-9\.\/\-\&\%\?\_]*$/},
  };
  // Find <label>s with the "mandatory" class,
  // append red asterisks to these labels,
  // and set the corresponding flag in the validation rules.
  jQuery("#easytemp")
    .find("label.mandatory")
    .each(function(index, element) {
      element = jQuery(element);
      element.append(' <span class="mandatory">*</span>');
      var name = element
        .parent().find("input, select, textarea")
        .attr("name");
      if (validationRules[name]) {
        validationRules[name].mandatory = 1;
      }
    });
  // The response to applicationSend() received by applicationSendSuccess()
  // has the format:
  // [ log: [ key, code, message ], ... ]
  // Where
  //  key (String):     field key where the error occurred; if the error is not
  //                    assigned to a field key the value is "global"
  //  code (int):       error code
  //  message (String): log message
  // ]
  var validationErrorCodes = {
    1: "Pflichtfeld",
    2: "Ungültiger Wert",
    3: "Datei ist zu gross",
    4: "Falscher Dateityp",
    5: "field keys not matching with regex keys",
    6: "Fehler beim Dateiupload",
    7: "POST size over the limit of 16MB (global)",
    8: "Double submit detected (global)",
    9: "no POST or FILES data received (global)",
    10: "empty POST or FILES data (global)",
    11: "no referrer found (global)",
    12: "referrer not matching regex (global)",
    13: "internal error –1 (global)",
    14: "internal error -2 (global)",
    15: "internal error -3 (global)"
  };
  var noattachment = false;
  function validate(it, forceError) {
    // Only validate the field just modified, if possible
    if (it) {
      return _validate(it, forceError);
    }
    clearMessages();
    var success = true;
    for (var name in validationRules) {
      success &= _validate("#application-" + name);
    }
    if (success) {
      return true;
    }
    return false;
  }
  function _validate(it, forceError) {
    it = jQuery(it);
    var name = it.attr("name");
    if (!validationRules[name]) {
      return true;
    }
    var error = [];
    if (forceError) {
      error.push(validationErrorCodes[forceError]);
    }
    var val = it.val();
    var rule = validationRules[name];
    if (val === '') {
      if (rule.mandatory) {
        if (name !== 'file1' || !noattachment) {
          error.push(validationErrorCodes[1]);
        }
      }
    } else {
      if (rule.regex && !rule.regex.test(val)) {
        error.push(validationErrorCodes[2]);
      }
    }
    if (rule.extension && val) {
      var button = it.parent().find("button.removefile");
      button.show();
      if (!val.match(rule.extension)) {
        error.push(validationErrorCodes[4]);
      }
    }
    if (name === 'noattachment') {
      noattachment = jQuery("#application-noattachment").prop("checked");
      var parents = jQuery(
        "#application-file1,#application-file2,#application-file3")
        .parent();
      if (noattachment) {
        parents.hide();
      } else {
        parents.show();
      }
    }
    it.removeClass("error");
    it.siblings("div.error").remove();
    if (error.length) {
      it.addClass("error");
      var errors = error.join(", ");
      it.parent().append('<div class="error">' + errors + "</div>");
      return false;
    }
    return true;
  }
  jQuery("form#easytemp-application-form input, form#easytemp-application-form select")
    .bind("change", function(event) {
      validate(event.target);
    });
  function displayMessage(_class, message) {
    jQuery("#easytemp-validation-messages").append(
      '<div class="message ' + _class + '">' + message + "</div>"
      );
  }
  jQuery("#easytemp-validation-messages").delegate(
    "", "click", function(event) {
      jQuery(event.target).remove();
    });
  function clearMessages() {
    jQuery("#easytemp-validation-messages").children().click();
  }

  function applicationSend() {
    // For testing, use:
    //applicationSendSuccess(); return;
    if (!validate()) {
      return false;
    }
    jQuery("#easytemp .error").remove();
    var files = jQuery(
      "#application-file1,#application-file2,#application-file3");
    if (noattachment) {
      files.val("");
    }
    // Cross domain posting is not possible, thus the form is sent
    // to our server, which then passes the request on to EasyTemp.
    var url = cx.variables.get("application_post_url", "EasyTemp");
    var data = jQuery("form#easytemp-application-form").serializeArray();
    if (window.FormData) {
      data = new FormData(document.getElementById("easytemp-application-form"));
    }
    var len = files.length;
    for (var i = 0; i < len; ++i) {
      var file = files[i];
      if (file.files[0]) {
        if (window.FileReader) {
          var reader = new FileReader();
          reader.readAsDataURL(file.files[0]);
        }
      }
    }
    jQuery.ajax({
      type: 'post',
      url: url,
      data: data,
      dataType: "json",
      processData: false,
      contentType: false,
      success: applicationSendSuccess
    });
  }
  jQuery("#application-send")
    .bind("click", applicationSend);
// For testing, use:
//  var gi = 0;
//  function applicationSendSuccess(data) {
//    return displayMessage(
//      ["error", "success"][gi % 2],
//      "" + (++gi));
//  }
  function applicationSendSuccess(data) {
    // Response format (JSON):
    //  "status" 0 | 1 (int) 0 = success or 1 = failed
    // Samples for testing:
    // {"status":0,"log":{"id":"20160302102214_269840"}}, success, { [...] }
    // {"status":1,"log":{"geburtsdatum":[2,"birthday check failed"]}}
    if (!data) {
      displayMessage("error",
        "Fehler bei der Übermittlung Ihrer Anfrage." +
        " Bitte versuchen Sie es erneut.");
      return;
    }
    if (data.status === 0) {
      jQuery("#easytemp-application-form").remove();
      return displayMessage(
        "success", "Ihre Bewerbung wurde erfolgreich aufgenommen");
    }
    for (var name in data.log) {
      // E.g. 2 (from [2,"birthday check failed"])
      var value = data.log[name][0];
      if (validationRules[name]) {
        var id = "#application-" + name;
        validate(id, value);
      } else {
        displayMessage("error", validationErrorCodes[value]);
      }
    }
  }
});
