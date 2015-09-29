/**
 * This script removes the strong elements in row
 * The strong elements contain stars for mandatory fields.
 * But we are not checking them on client site, so we do not need them
 *
 * Note: If the fields are not field out, the contact data model from multisite will be loaded, where we are checking them, so
 * the user has no possibility to cheat (not fill out). There the stars are loaded for better usability.
 */
jQuery(document).ready(function(){
    if (jQuery('.row').children('strong').html() == '*') {
        jQuery('.row').children('strong').remove();
    }
});
