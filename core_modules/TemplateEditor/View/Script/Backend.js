/**
 * This file is loaded by the abstract SystemComponentBackendController
 * You may add own JS files using
 * \JS::registerJS(substr($this->getDirectory(false, true) . '/View/Script/FileName.css', 1));
 * or remove this file if you don't need it
 */

function updateOption(optionName,optionData, callback){
    jQuery('#saveOptionsButton').attr("disabled", "disabled");
    jQuery.post( "index.php?cmd=JsonData&object=TemplateEditor&act=updateOption&tid="+cx.variables.get('themeid','TemplateEditor'), { optionName: optionName, optionData:optionData }, function (reponse) {
        if (reponse.status != 'error'){
            var domainurl = cx.variables.get('domainurl','TemplateEditor');
            try {
                var currentIframeUrl = jQuery("#preview-template-editor").get(0).contentWindow.location.href;
                if (currentIframeUrl.search(domainurl)){
                    jQuery("#preview-template-editor").attr('src', currentIframeUrl);
                }
                else {
                    jQuery("#preview-template-editor").attr('src', cx.variables.get('iframeUrl','TemplateEditor'));
                }
            }
            catch (e){
                jQuery("#preview-template-editor").attr('src', cx.variables.get('iframeUrl','TemplateEditor'));
            }

        }
        callback(reponse);
        jQuery('#saveOptionsButton').removeAttr("disabled");
    }, "json");
}

var saveOptions = function (){
    if (jQuery(this).attr('disabled')){
        return;
    }

    var that = this;
    bootbox.dialog({
        title: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_SAVE_TITLE','TemplateEditor'),
        message: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_SAVE_CONTENT','TemplateEditor'),
        buttons: {
            success: {
                label: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_SAVE','TemplateEditor'),
                className: "btn-success",
                callback: function() {
                    var loading = bootbox.dialog({
                        message: '<img style="margin: 30px auto; display:block;" src="../lib/javascript/jquery/jstree/themes/default/throbber.gif" alt=""/>',
                        title: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_SAVE','TemplateEditor'),
                        onEscape: function() {},
                        closeButton: false
                    });
                    jQuery.post( "index.php?cmd=JsonData&object=TemplateEditor&act=saveOptions&tid="+cx.variables.get('themeid','TemplateEditor'), {}, function (response) {
                        jQuery(that).addClass('saved');
                        setTimeout(function(){
                            jQuery(that).removeClass('saved');
                        }, 2000);
                        loading.modal('hide');
                    }, "json");
                }
            },
            main: {
                label: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_CANCEL','TemplateEditor'),
                className: "btn-danger",
                callback: function() {
                }
            }
        }
    });

};

jQuery(function(){

    jQuery('.option.view .buttons button').click(function(){
        jQuery("#preview-template-editor").css({'width': jQuery(this).data('size')});
        jQuery('.option.view .buttons button').removeClass('active');
        jQuery(this).addClass('active');
    });

    jQuery('#saveOptionsButton').click(saveOptions);

    jQuery('.add-preset').click(function(){
        bootbox.dialog({
                title: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_ADD_PRESET_TITLE','TemplateEditor'),
                message: jQuery('#new-preset').html(),
                buttons: {
                    success: {
                        label: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_SAVE','TemplateEditor'),
                        className: "btn-success",
                        callback: function () {
                            jQuery.post( "index.php?cmd=JsonData&object=TemplateEditor&act=addPreset", {
                                tid: cx.variables.get('themeid','TemplateEditor'),
                                preset: jQuery('#new-preset-name').val(),
                                presetpreset: jQuery('#preset-for-preset').val()
                            }, function (response) {
                                var newlocation = location.href.replace(/preset=[a-z0-9]+/i, "preset="+response.data.preset);
                                window.location.href = (newlocation.search('preset=') == -1 ? newlocation + "&preset=" + response.data.preset : newlocation);
                            }, "json");
                        }
                    },
                    danger: {
                        label: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_CANCEL','TemplateEditor'),
                        className: "btn-danger"
                    }
                }
            }
        );
    });

    jQuery('.activate-preset').click(function(){
        bootbox.dialog({
                title: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_ACTIVATE_PRESET_TITLE','TemplateEditor'),
                message: jQuery('#active-preset').html(),
                buttons: {
                    success: {
                        label: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_YES','TemplateEditor'),
                        className: "btn-success",
                        callback: function () {
                            jQuery.post( "index.php?cmd=JsonData&object=TemplateEditor&act=activatePreset", {
                                tid: cx.variables.get('themeid','TemplateEditor'),
                                preset: jQuery('#preset').val()
                            }, function (response) {
                                window.location.href = window.location.href;
                            }, "json");
                        }
                    },
                    danger: {
                        label: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_NO','TemplateEditor'),
                        className: "btn-danger"
                    }
                }
            }
        );
    });

    jQuery('.remove-preset').click(function(){
        bootbox.dialog({
                title: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_SAVE_TITLE','TemplateEditor'),
                message: jQuery('#remove-preset').html(),
                buttons: {
                    success: {
                        label: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_YES','TemplateEditor'),
                        className: "btn-danger",
                        callback: function () {
                            jQuery.post( "index.php?cmd=JsonData&object=TemplateEditor&act=removePreset", {
                                tid: cx.variables.get('themeid','TemplateEditor'),
                                preset: jQuery('#preset').val()
                            }, function (response) {
                                window.location.href = window.location.href;
                            }, "json");
                        }
                    },
                    danger: {
                        label: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_CANCEL','TemplateEditor'),
                        className: "btn-danger"
                    }
                }
            }
        );
    });

    jQuery('.reset-preset').click(function(){
        bootbox.dialog({
                title: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_SAVE_TITLE','TemplateEditor'),
                message: jQuery('#reset-preset').html(),
                buttons: {
                    success: {
                        label: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_YES','TemplateEditor'),
                        className: "btn-success",
                        callback: function () {
                            jQuery.post( "index.php?cmd=JsonData&object=TemplateEditor&act=resetPreset", {
                                tid: cx.variables.get('themeid','TemplateEditor'),
                                preset: jQuery('#preset').val()
                            }, function (response) {
                                window.location.href = window.location.href;
                            }, "json");
                        }
                    },
                    danger: {
                        label: cx.variables.get('TXT_CORE_MODULE_TEMPLATEEDITOR_CANCEL','TemplateEditor'),
                        className: "btn-danger"
                    }
                }
            }
        );
    });

    jQuery('#layout').change(function(){
        var newloaction = location.href.replace("tid="+cx.variables.get('themeid','TemplateEditor'), "tid="+jQuery(this).val());
        window.location.href = (newloaction.search('tid=') == -1 ? newloaction + "&tid=" + jQuery(this).val() : newloaction);
    });

    jQuery('#preset').change(function(){
        var newloaction = location.href.replace(/preset=[a-z0-9]+/i, "preset="+jQuery(this).val());
        window.location.href = (newloaction.search('preset=') == -1 ? newloaction + "&preset=" + jQuery(this).val() : newloaction);
    });
});
