/**
 * This file is loaded in FormGenerator over \JS::registerJS for requests over cx.ajax
 *
 */

jQuery(document).ready(function(){
    jQuery('.mappedAssocciationButton, .edit').click(function() {
        editAssociation(jQuery(this));
    });
});

function editAssociation (thisElement) {
    var paramAssociativeArray = {};
    if (jQuery(thisElement).attr("class").indexOf('mappedAssocciationButton') >= 0) {
        paramIndexedArray = jQuery(thisElement).attr('data-params').split(';');
    } else {
        paramIndexedArray = jQuery(thisElement).parent().siblings('.mappedAssocciationButton').attr('data-params').split(';');
    }

    jQuery.each(paramIndexedArray, function(index, value){
        paramAssociativeArray[value.split(':')[0]] = value.split(':')[1];
    });
    existingData = '';
    if (jQuery(thisElement).hasClass('edit')) {
        jQuery(thisElement).parent().children('input').addClass('current');
        existingData = jQuery(thisElement).parent().children('input').attr('value')
    }
    createAjaxRequest(
        paramAssociativeArray['entityClass'],
        paramAssociativeArray['mappedBy'],
        paramAssociativeArray['cssName'],
        paramAssociativeArray['sessionKey'],
        existingData
    );
}
/*
* This function creates a cx dialag for the ViewGenerator and opens it
*
*/
function openDialogForAssociation(content, className, existingData)
{

    buttons = [
        {
            text: cx.variables.get('TXT_CANCEL', 'Html/lang'),
            click: function() {
                jQuery(this).dialog('close');
                jQuery('.oneToManyEntryRow').children('.current').removeClass('current');
            }
        },
        {
            text: cx.variables.get('TXT_SUBMIT', 'Html/lang'),
            click: function() {

                var element = jQuery(this).closest('.ui-dialog').children('.ui-dialog-content').children('form');
                saveToMappingForm(element, className);
                jQuery(this).dialog('close');
            }
        }
    ];
    cx.ui.dialog({
        width: 600,
        height: 300,
        autoOpen: true,
        content: content,
        modal: true,
        resizable: false,
        buttons:buttons,
        close: function() {
            jQuery(this).dialog('close');
        }
    });
    jQuery.each(existingData.split('&'), function(index, value){
        property = value.split('=');
        jQuery('input[name='+property[0]+']').attr('value', property[1]);
        if (property[0] == 'id') {
            jQuery('<input>').attr({
                value: property[1],
                id: 'id',
                name: 'id',
                type: 'hidden'
            }).appendTo(jQuery('.ui-dialog-content').children('form'));
        }
    });


}

/*
 * This function takes the data from dialog form and writes it into our many form
 *
 */
function saveToMappingForm(element, className)
{
    value = element.serialize().split('&');
    var valuesAsString = '';
    jQuery.each(value, function(index, value){
        if(value.split('=')[0] != 'vg_increment_number' && value.split('=')[0] != 'id'){
            if (value.split('=')[1] != "") {
                decodedValue = value.split('=')[1];
                decodedValue = decodedValue.replace('+', ' '); // because serialize makes a plus out of whitespaces
                valuesAsString += decodeURIComponent(decodedValue) + ' / ';
            } else {
                valuesAsString += '-' + ' / ';
            }
        }
    });

    // if the last attribute is not set, we remove the notSetString "- / " for better optic
    while (valuesAsString.slice(-5) == ' - / ') {
        valuesAsString = valuesAsString.substr(0, valuesAsString.length - 5);
    }

    // remove the last slash and the last two whitespaces for better optic
    valuesAsString = valuesAsString.substr(0, valuesAsString.length - 3);

    // we only create a new element if it is not empty
    if (valuesAsString != "") {
        current = jQuery('.oneToManyEntryRow').children('.current');
        if(jQuery(current).is(':empty')){
            jQuery(current).attr('value', element.serialize());
            jQuery(current).parent().children('span').html(valuesAsString);
            jQuery(current).removeClass('current');
        } else {
            jQuery('.add_'+className+'').before('<div class=\'oneToManyEntryRow\'>'
                + '<span>' + valuesAsString + '</span>'
                + '<input type=\'hidden\' name=\'' + className + '[]\' value=\'' + element.serialize() + '\'>'
                + '<a onclick=\'editAssociation(this)\' class=\'edit\' title=\'' + cx.variables.get('TXT_EDIT', 'Html/lang') + '\'></a>'
                + '<a onclick=\'deleteAssociationMappingEntry(this)\' class=\'remove\' title=\'' + cx.variables.get('TXT_DELETE', 'Html/lang') + '\'></a>'
                + '</div>'
            );
        }
    }
}

/*
 * This function removes an association which we created over dialog
 *
 */
function deleteAssociationMappingEntry(element)
{
    // if we have an already existing entry (which is saved in the database), we only hide it, because we will remove
    // it as soon as the main formular is submitted.
    // otherwise we have an entry which doesn't exists in the database and we can simply remove the element, because we
    // do not need to store it and so it is useless
    if (jQuery(element).hasClass('existing')) {
        jQuery(element).parent().css('display', 'none');
        entryInput = jQuery(element).parent().children('input');
        entryInput.attr('value', entryInput.attr('value') + '&delete=1');
    } else {
        jQuery(element).parent().remove();
    }
}


/*
 * This function creates an ajax request to the ViewGenerator and on success call the function to open the dialog where
 * we can insert the data for the mapped association
 *
 */
function createAjaxRequest(entityClass, mappedBy, className, sessionKey, existingData){
    cx.ajax(
        'Html',
        'getViewOverJson',
    {
        data: {
            entityClass: entityClass,
            mappedBy:    mappedBy,
            sessionKey:  sessionKey
        },
        success: function(data) {
            openDialogForAssociation(
                data.data,
                className,
                existingData
            );
            jQuery('.datepicker').datepicker({
                dateFormat: 'dd.mm.yy'
            });
        }
    });
}
