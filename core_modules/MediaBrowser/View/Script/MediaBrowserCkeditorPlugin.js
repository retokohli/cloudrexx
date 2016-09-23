CKEDITOR.on('dialogDefinition', function (event) {
    var editor = event.editor;
    var dialogDefinition = event.data.definition;

    var tabCount = dialogDefinition.contents.length;

    //Customize the advanced tab
    var advancedTab = dialogDefinition.getContents( 'advanced' );
    if (advancedTab !== null) {
        advancedTab.add({
            type: 'text',
            label: 'Srcset',
            id: 'txtdlgGenSrcSet',
            'default': ''
        });
        var style = advancedTab.get('txtdlgGenStyle');
        style['default'] = '';
    }

    //Customize the info tab
    var infoTab = dialogDefinition.getContents( 'info' );
    if (infoTab !== null) {
        infoTab.remove( 'txtWidth' );
        infoTab.remove( 'txtHeight' );
    }

    //Customize the code inserted for image
    dialogDefinition.onOk = function (e) {
        var dialog = this;
        var img = editor.document.createElement( 'img' );
        setTagAttribute(img, 'src', dialog.getValueOf('info', 'txtUrl'));
        setTagAttribute(img, 'alt', dialog.getValueOf('info', 'txtAlt'));
        setTagAttribute(img, 'id', dialog.getValueOf('advanced', 'linkId'));
        setTagAttribute(img, 'dir', dialog.getValueOf('advanced', 'cmbLangDir'));
        setTagAttribute(img, 'lang', dialog.getValueOf('advanced', 'txtLangCode'));
        setTagAttribute(img, 'longdesc', dialog.getValueOf('advanced', 'txtGenLongDescr'));
        setTagAttribute(img, 'class', dialog.getValueOf('advanced', 'txtGenClass'));
        setTagAttribute(img, 'title', dialog.getValueOf('advanced', 'txtGenTitle'));
        setTagAttribute(img, 'style', dialog.getValueOf('advanced', 'txtdlgGenStyle'));
        setTagAttribute(img, 'srcset', dialog.getValueOf('advanced', 'txtdlgGenSrcSet'));

        var html = img;
        if (dialog.getValueOf('Link', 'txtUrl')) {
            var aTag = editor.document.createElement( 'a' );
            setTagAttribute(aTag, 'href', dialog.getValueOf('Link', 'txtUrl'));
            setTagAttribute(aTag, 'target', dialog.getValueOf('Link', 'cmbTarget'));
            aTag.setHtml(img.getOuterHtml());
            html = aTag;
        }
        editor.insertElement(html);
    };

    var setTagAttribute = function (tag, attrName, attrVal) {
        if (!tag || !attrName) {
            return;
        }

        if (attrVal) {
            //If the attribute is style, check and remove the property
            //values of width and height from style attribute
            if (attrName == 'style') {
                attrVal = attrVal.replace(/(width|height):(\s|)\d{2,3}px(;|)/g, '');
            }
            tag.setAttribute(attrName, attrVal);
        }
    };

    for (var i = 0; i < tabCount; i++) {
        if(dialogDefinition.contents[i] == undefined){
            continue;
        }
        var browseButton = dialogDefinition.contents[i].get('browse');

        if (browseButton !== null) {
            /**
             * Handling image selection.
             */
            if (browseButton.filebrowser.target == 'info:txtUrl' || browseButton.filebrowser.target == 'info:src') {
                var targetType = browseButton.filebrowser.target.split(':');
                browseButton.hidden = false;
                var filelistCallback = function (callback) {
                    if (callback.type == 'close') {
                        return;
                    }
                    $J.ajax({
                        type: "GET",
                        url: "index.php?cmd=jsondata&object=MediaBrowser&act=createThumbnails&file=" + callback.data[0].datainfo.filepath
                    });
                    var dialog = cx.variables.get('jquery','mediabrowser')(cx.variables.get('thumbnails_template', 'mediabrowser'));
                    var image = dialog.find('.image');
                    image.attr('src', callback.data[0].datainfo.filepath);
                    bootbox.dialog({
                        title: cx.variables.get('TXT_FILEBROWSER_SELECT_THUMBNAIL', 'mediabrowser'),
                        message: dialog.html(),
                        buttons: {
                            success: {
                                label: cx.variables.get('TXT_FILEBROWSER_SELECT_THUMBNAIL', 'mediabrowser'),
                                className: "btn-success",
                                callback: function () {
                                    var image, thumbnail = $J("[name='size']").val();
                                    if (thumbnail == 0) {
                                        image = callback.data[0].datainfo.filepath;
                                    } else {
                                        image = callback.data[0].datainfo.thumbnail[thumbnail];
                                    }
                                    dialogDefinition.dialog.setValueOf(targetType[0], targetType[1], image);

                                    //Set default value to srcSet
                                    var srcSetValue = [];
                                    $J.each(callback.data[0].datainfo.thumbnail, function(i, v) {
                                        srcSetValue.push(v + ' ' + i + 'w');
                                    });
                                    dialogDefinition.dialog.setValueOf('advanced', 'txtdlgGenSrcSet', srcSetValue.join(', '));
                                }
                            }
                        }
                    });
                };

                browseButton.onClick = function (dialog, i) {
                    editor._.filebrowserSe = this;
                    //editor.execCommand ('image');
                    cx.variables.get('jquery','mediabrowser')('#ckeditor_image_button').trigger("click", {
                        callback: filelistCallback,
                        cxMbViews: 'filebrowser,uploader',
                        cxMbStartview: 'MediaBrowserList'
                    });
                };

                dialogDefinition.dialog.on('show', function (event) {
                    var that = this;
                    setTimeout(function () {
                        var inputfield = that.getValueOf('info', 'txtUrl');
                        if (inputfield == '') {
                            cx.variables.get('jquery','mediabrowser')('#ckeditor_image_button').trigger("click", {
                                callback: filelistCallback,
                                cxMbViews: 'filebrowser,uploader',
                                cxMbStartview: 'MediaBrowserList'
                            });
                        }
                    }, 2);
                });

            }
            /**
             * Handling node links.
             */
            else if (browseButton.filebrowser.target == 'Link:txtUrl' || browseButton.filebrowser.target == 'info:url'){
                var target = browseButton.filebrowser.target.split(':');
                var sitestructureCallback = function (callback) {
                    var link;
                    if (callback.type == 'close') {
                        return;
                    }
                    if (callback.data[0].node){
                        link = callback.data[0].node;
                    }
                    else {
                        link = callback.data[0].datainfo.filepath;
                    }

                    dialogDefinition.dialog.setValueOf(target[0], target[1], link);
                    /**
                     * Protocol field exists only in the info tab.
                     */
                    if (target[0] == 'info'){
                        dialogDefinition.dialog.setValueOf('info','protocol', '');
                    }

                };
                browseButton.hidden = false;
                browseButton.onClick = function (dialog, i) {
                    //editor.execCommand ('image');
                    cx.variables.get('jquery','mediabrowser')('#ckeditor_image_button').trigger("click", {
                        callback: sitestructureCallback,
                        cxMbViews: 'uploader,filebrowser,sitestructure',
                        cxMbStartview: 'Sitestructure'
                    });
                };

            }
        }

    }
});
