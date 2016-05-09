/**
 * This file is loaded by the abstract SystemComponentBackendController
 * You may add own JS files using
 * \JS::registerJS(substr($this->getDirectory(false, true) . '/View/Script/FileName.css', 1));
 * or remove this file if you don't need it
 */
function prepareConfiguration() {
  // Trigger the download of the configuration
  jQuery('[data-group="edit"]').click();
  // get the content of the textarea
  var configuration = jQuery('.configCode').val();
  // go back to the configurator view
  jQuery('[data-group="config"]').click();
  var pattern = /config\.removeButtons\s=\s'([a-z0-9,]*)*/i;
  if (pattern.test(configuration)) {
    var removedButtons = pattern.exec(configuration);
    jQuery('form[name="toolbarConfigurator"]').append(jQuery('<input />').attr({
      "type":   'hidden',
      "name":   'removedButtons',
      "value":  removedButtons[1]
    }));
  }
  return true;
}