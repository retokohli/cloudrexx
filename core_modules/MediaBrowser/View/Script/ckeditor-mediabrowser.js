CKEDITOR.plugins.add( 'mediabrowser', {
    icons: 'image',
    init: function( editor ) {
        editor.addCommand( 'addImage', {
            exec: function( editor ) {
                jQuery('#ckeditor_image_button').trigger('click');
            }
        });
        editor.ui.addButton( 'mediabrowser.image', {
            label: 'Add Image',
            command: 'addImage',
            toolbar: 'addImage',
            icon: 'image'
        });
    }
});

window.ckeditor_image_callback = function(callback){
    if (callback.type == 'close'){
        return;
    }
    $J.ajax({
        type: "GET",
        url: "index.php?cmd=jsondata&object=MediaBrowser&act=createThumbnails&file="+callback.data[0].datainfo.filepath
    });
    var dialog = MediaBrowserjQuery(cx.variables.get('thumbnails_template', 'mediabrowser'));
    var image = dialog.find('.image');
    image.attr('src',callback.data[0].datainfo.filepath );
    bootbox.dialog({
            title:  cx.variables.get('TXT_FILEBROWSER_SELECT_THUMBNAIL', 'mediabrowser'),
            message: dialog.html(),
            buttons: {
                success: {
                    label: cx.variables.get('TXT_FILEBROWSER_SELECT_THUMBNAIL', 'mediabrowser'),
                    className: "btn-success",
                    callback: function () {
                        var image, thumbnail = $J("[name='size']").val();
                        if (thumbnail == 0){
                            image = callback.data[0].datainfo.filepath;
                        }
                        else {
                            image = callback.data[0].datainfo.thumbnail[thumbnail];
                        }
                        CKEDITOR.instances.cm_ckeditor.insertHtml('<img class="img-responsive" src="'+image+'" />')
                    }
                }
            }
    });
};