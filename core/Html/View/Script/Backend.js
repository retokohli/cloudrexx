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
    var cadminPath = cx.variables.get('cadminPath', 'contrexx'),
        component  = cx.variables.get('component', 'ViewGenerator/sortBy'),
        entity     = cx.variables.get('entity', 'ViewGenerator/sortBy'),
        sortField  = cx.variables.get('sortField', 'ViewGenerator/sortBy'),
        jsonObject = cx.variables.get('jsonObject', 'ViewGenerator/sortBy'),
        jsonAct    = cx.variables.get('jsonAct', 'ViewGenerator/sortBy'),
        isSortByActive = cx.variables.get('isSortByActive', 'ViewGenerator/sortBy');
    if (!isSortByActive) {
        return;
    }
    jQuery('.adminlist').addClass('sortable');
    jQuery('.adminlist tbody').sortable({
        axis: "y",
        items: "> tr.row1,> tr.row2 ",
        update: function (event, ui) {
            if (jsonObject && jsonAct) {
                var that = this,
                    sortOrder = jQuery('.adminlist tbody').sortable('serialize'),
                    data = sortOrder + '&sortField=' + sortField;
                if (component && entity) {
                    data += '&component=' + component + '&entity=' + entity;
                }
                jQuery.ajax({
                    type: 'POST',
                    data: data,
                    url:  cadminPath + 'index.php&cmd=JsonData&object=' + jsonObject + '&act=' + jsonAct,
                    beforeSend: function() {
                        jQuery('body').addClass('loading');
                        jQuery(that).sortable("disable");
                        jQuery(ui.item).find('td:first-child').addClass('sorter-loading');
                    },
                    complete: function() {
                        var pagingPosition = 1;
                        jQuery('table.sortable tbody > tr:not(:nth-child(1), :nth-child(2), :last-child) > td.sortBy').each(function(){
                            jQuery(this).text(pagingPosition);
                            pagingPosition++;
                        });
                        jQuery(that).sortable("enable");
                        jQuery('body').removeClass('loading');
                        jQuery(ui.item).find('td:first-child').removeClass('sorter-loading');
                    }
                });
            }
        }
    });
    
});
