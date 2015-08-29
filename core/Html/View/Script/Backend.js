/**
 * This file is loaded by the abstract SystemComponentBackendController
 * You may add own JS files using
 * \JS::registerJS(substr($this->getDirectory(false, true) . '/View/Script/FileName.css', 1));
 * or remove this file if you don't need it
 */

/**
 * Script for initializing the row sorting functionality in ViewGenerator 
 */
cx.jQuery(function(jQuery) {
    cadminPath = cx.variables.get('cadminPath', 'contrexx');
    jsonObject = cx.variables.get('jsonObject', 'ViewGenerator/sortBy');
    jsonAct    = cx.variables.get('jsonAct', 'ViewGenerator/sortBy');
    isSortByActive = cx.variables.get('isSortByActive', 'ViewGenerator/sortBy');
    if (isSortByActive) {
        jQuery('.adminlist').addClass('sortable');
        jQuery('.adminlist tbody').sortable({
            axis: "y",
            items: "> tr.row1,> tr.row2 ",
            update: function (event, ui) {
                if (jsonObject && jsonAct) {
                    var that = this;
                    var data = jQuery('.adminlist tbody').sortable('serialize');
                    jQuery.ajax({
                        type: 'POST',
                        data: data,
                        url:  cadminPath + 'index.php&cmd=JsonData&object=' + jsonObject + '&act=' + jsonAct,
                        beforeSend: function() {
                            jQuery('body').addClass('loading');
                            jQuery(that).sortable("disable");
                            jQuery(ui.item).find('td:first-child').css({'background-image': 'url(../../../../core_modules/News/View/Media/loading.gif)'});
                        },
                        complete: function() {
                            setTimeout(function () {
                                jQuery(that).sortable("enable");
                                jQuery('body').removeClass('loading');
                                jQuery(ui.item).find('td:first-child').css({'background-image': ''})
                            }, 300);
                        }
                    });
                }
            }
        });
    }
});
