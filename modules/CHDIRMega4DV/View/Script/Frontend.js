/* global cx */
if (!evaluateURLAndOpenKlappi) {
  function evaluateURLAndOpenKlappi() {
    var klappiId = getParam('open');
    if (klappiId) {
      document.location = '#Link' + klappiId;
      cx.jQuery('#klappi' + klappiId).slideToggle('fast');
    }
  }
  /*  This function gets the value of the URL parameter provided */
  function getParam(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split('&');
    for (var i = 0; i < vars.length; ++i) {
      var pair = vars[i].split('=');
      if (pair[0] === variable) {
        return pair[1];
      }
    }
    return false;
  }
  cx.jQuery(function() {
    evaluateURLAndOpenKlappi();
  });
}
