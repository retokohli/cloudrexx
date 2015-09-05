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
        sortOrder  = cx.variables.get('sortOrder', 'ViewGenerator/sortBy'),
        jsonObject = cx.variables.get('jsonObject', 'ViewGenerator/sortBy'),
        jsonAct    = cx.variables.get('jsonAct', 'ViewGenerator/sortBy'),
        pagingPosition = cx.variables.get('pagingPosition', 'ViewGenerator/sortBy'),
        isSortByActive = cx.variables.get('isSortByActive', 'ViewGenerator/sortBy');
    if (typeof(isSortByActive) === 'undefined') {
        return;
    }
    
    jQuery('.adminlist').addClass('sortable');
    jQuery('.adminlist tbody').sortable({
        axis: "y",
        items: "> tr.row1,> tr.row2 ",
        start: function (event, ui) {
            jQuery(ui.item).data('pIndex', ui.item.index());
        },
        update: function (event, ui) {
            if (    typeof(jsonObject) === 'undefined'
                ||  typeof(jsonAct) === 'undefined'
                ||  !jsonObject
                ||  !jsonAct
            ) {
                return;
            }

            var that   = this,
                sortTd = jQuery('table.sortable tbody > tr:not(:nth-child(1), :nth-child(2), :last-child) > td.sortBy'),
                currentIndex   = ui.item.index() - 1,
                previousIndex  = jQuery(ui.item).data('pIndex') - 1,
                data = 'sortOrder=' + sortOrder + '&curPosition=' + currentIndex
                       + '&prePosition=' + previousIndex
                       + '&sortField=' + sortField + '&pagingPosition=' + pagingPosition;
            if (component && entity) {
                data += '&component=' + component + '&entity=' + entity;
            }
            jQuery(ui.item).removeData('pIndex');
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
                    var obj, currentOrder, order, firstObj,
                        condition = currentIndex > previousIndex,
                        min       = condition ? previousIndex : currentIndex,
                        max       = condition ? currentIndex : previousIndex,
                        first     = true;
                    while (min <= max) {
                        obj = condition ? sortTd.eq(min - 1) : sortTd.eq(max - 1);
                        currentOrder = obj.text();
                        if (first) {
                            first = false;
                            order = currentOrder;
                            firstObj = obj;
                            continue;
                        } else if (min == max) {
                            firstObj.text(currentOrder);
                            obj.text(order);
                        }
                        obj.text(order);
                        order = currentOrder;
                        condition ? min++ : max--;
                    }
                    jQuery(that).sortable("enable");
                    jQuery('body').removeClass('loading');
                    jQuery(ui.item).find('td:first-child').removeClass('sorter-loading');
                }
            });
        }
    });
    
});
