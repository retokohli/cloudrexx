/**
 * MultiSite
 * @author: Thomas Däppen <thomas.daeppen@comvation.com>
 * @version: 1.0
 * @package: contrexx
 * @subpackage: coremodules_multisite
 */

/**
 * Position the account-activate-bar
 */
cx.ready(function() {
    // fetch the current vertical position of the body
    var toolbarOffset = parseInt(cx.jQuery("body").css("padding-top"));
    if (!toolbarOffset) {
        toolbarOffset = 0;
    }
console.log('my height (MAA): ' + parseInt(cx.jQuery("#MultiSiteAccountActivation").outerHeight())); 
console.log('set body top to ' + (parseInt(cx.jQuery("#MultiSiteAccountActivation").outerHeight()) + toolbarOffset) + "px");
    // position the body and the account-activation-bar
    cx.jQuery("body").css("padding-top", (parseInt(cx.jQuery("#MultiSiteAccountActivation").outerHeight()) + toolbarOffset) + "px");
    cx.jQuery("#MultiSiteAccountActivation").css({
        top: toolbarOffset + "px"
    });
console.log('new body top is ' + cx.jQuery("body").css("padding-top"));

});
