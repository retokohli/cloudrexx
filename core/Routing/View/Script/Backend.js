cx.jQuery(function(jQuery){
    jQuery('.adminlist tbody').sortable({
        axis: "y",
        items: "> tr.row1,> tr.row2 ",
        update: function (event, ui) {
            jQuery('body').addClass('loading');
            jQuery(this).sortable("disable");
            var that = this;
            jQuery(ui.item).find('td:first-child').css({'background-image': 'url(../../../../core_modules/News/View/Media/loading.gif)'});
            setTimeout(function () {
                jQuery(that).sortable("enable");
                jQuery('body').removeClass('loading');
                jQuery(ui.item).find('td:first-child').css({'background-image': ''})
            }, 300);
        }
    });
});
