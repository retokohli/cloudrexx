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

