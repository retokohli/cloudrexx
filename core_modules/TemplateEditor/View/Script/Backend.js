/**
 * This file is loaded by the abstract SystemComponentBackendController
 * You may add own JS files using
 * \JS::registerJS(substr($this->getDirectory(false, true) . '/View/Script/FileName.css', 1));
 * or remove this file if you don't need it
 */

jQuery(document).ready(function () {
    jQuery('.color input').keyup(function() {
        var color = jQuery(this).val();
        if (!color.match(/^#/)){
            color = '#'+color;
        }
        jQuery(this).prev().css({'backgroundColor': color})
    });
});

function updateOption(optionName,optionData, callback){
    jQuery.post( "index.php?cmd=JsonData&object=TemplateEditor&act=updateOption", { optionName: optionName, optionData:optionData }, function (data) {
        jQuery("#preview-template-editor").attr('src', jQuery("#preview-template-editor").get(0).contentWindow.location.href);
        callback(data);
    }, "json");
}

