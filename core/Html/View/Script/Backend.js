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
                updatedOrder  = jQuery('.sortable tbody').sortable('serialize'), recordCount,
                currentIndex  = ui.item.index() - 1,
                previousIndex = jQuery(ui.item).data('pIndex') - 1,
                repeat = isOrderNoRepeat(sortTd, previousIndex, currentIndex),
                data   = 'sortOrder=' + sortOrder + '&curPosition=' + currentIndex
                       + '&prePosition=' + previousIndex
                       + '&sortField=' + sortField + '&pagingPosition=' + pagingPosition;
            if (component && entity) {
                data += '&component=' + component + '&entity=' + entity;
            }
            if (repeat) {
                data += '&' + updatedOrder;
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
                success: function(msg) {
                    recordCount = msg.data.recordCount;
                },
                complete: function() {
                    updateOrder(sortTd, previousIndex, currentIndex, repeat, recordCount);
                    jQuery(that).sortable("enable");
                    jQuery('body').removeClass('loading');
                    jQuery(ui.item).find('td:first-child').removeClass('sorter-loading');
                }
            });
        }
    });

    //Check the same 'order' field value is repeated or not
    function isOrderNoRepeat(obj, pIndex, cIndex) {
        var orderArray = [], currentval,
            condition = cIndex > pIndex,
            min       = condition ? pIndex : cIndex,
            max       = condition ? cIndex : pIndex;
        while (min <= max) {
            currentval = condition ? obj.eq(min - 1).text() : obj.eq(max - 1).text();
            if (jQuery.inArray(currentval, orderArray) == -1) {
                orderArray.push(currentval);
                condition ? min++ : max--;
                continue;
            }
            return true;
        }
        return false;
    }

    //Update the sorted order in the 'order' field
    function updateOrder(obj, pIndex, cIndex, repeat, recordCnt) {
        var currentObj, currentOrder, order, firstObj,
            condition = cIndex > pIndex,
            min       = condition ? pIndex : cIndex,
            max       = condition ? cIndex : pIndex,
            first     = true;
    
        if (repeat) {
            var pagingCnt = (sortOrder == 'DESC') 
                            ? (recordCnt - pagingPosition) + 1
                            : pagingPosition;
            obj.each(function() {
                (sortOrder == 'DESC') ? pagingCnt-- : pagingCnt++;
                jQuery(this).text(pagingCnt);
            });
        } else {
            while (min <= max) {
                currentObj = condition ? obj.eq(min - 1) : obj.eq(max - 1);
                currentOrder = currentObj.text();
                if (first) {
                    first = false;
                    order = currentOrder;
                    firstObj = currentObj;
                    continue;
                } else if (min == max) {
                    firstObj.text(currentOrder);
                    currentObj.text(order);
                }
                currentObj.text(order);
                order = currentOrder;
                condition ? min++ : max--;
            }
        }
    }
});
