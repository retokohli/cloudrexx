var dataImagePattern = /<img\s+[^>]*src=([\'\"])(data\:(\s|)image\/(\w{3,4})\;base64\,(\s|)([^\'\"]*)\s*)\1[^>]*>/g;
for(var instanceName in CKEDITOR.instances) {
    var editor = CKEDITOR.instances[instanceName];
    editor.on('paste', function (event) {
        var data = event.data.dataValue, match, files = [], dataSrc = [];
        while (match = dataImagePattern.exec(data)) {
            var file = dataURItoFile(match[2]);
            dataSrc[file.name] = match[2];
            files.push(file);
        }

        if (!files || files.length === 0) {
            return;
        }

        //If user has no permission to access its component/default mediaSource
        //then replace pasted image with no-access image
        var targetPath = cx.variables.get('ckeditorUploaderPath', 'wysiwyg');
        if (!targetPath) {
            event.data.dataValue = data.replace(
                dataImagePattern,
                cx.jQuery('<div />').html(
                    cx.jQuery('<img />')
                        .attr('src', '../core/Core/View/Media/no_access.png')
                        .attr('title', 'Image paste denied')
                        .attr('alt', 'Image paste denied')
                ).html()
            );
            showMessage('Image paste denied', 'error', false);
            return;
        }

        //Upload process for pasted images
        doUpload(event, files, targetPath, dataSrc);
    });
}

/**
 * Do the upload process
 *
 * @param object event      event object
 * @param array  files      array of upload files
 * @param string targetPath target path
 * @param array  dataSrc    array of data URI with file name
 */
function doUpload(event, files, targetPath, dataSrc) {
    var uploaderId = cx.variables.get('ckeditorUploaderId', 'wysiwyg'),
        uploadedFilesCount = 0;
    cx.jQuery('<a/>')
        .attr('id', 'wysiwygPasteUploadButton_' + uploaderId)
        .attr('style', 'display:none')
        .appendTo(document.body);
    var options = {
        runtimes: 'html5,flash,silverlight,html4',
        multi_selection: true,
        max_file_size: '500mb',
        browse_button: 'wysiwygPasteUploadButton_' + uploaderId,
        url: cx.variables.get('cadminPath','contrexx') + '?cmd=JsonData&object=Uploader&act=upload&csrf=' + cx.variables.get('csrf'),
        flash_swf_url: cx.variables.get('basePath','contrexx') + 'lib/plupload/js/Moxie.swf',
        silverlight_xap_url: cx.variables.get('basePath','contrexx') + 'lib/plupload/js/Moxie.xap',
        prevent_duplicates: true,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Check-CSRF': 'false'
        },
        chunk_size: cx.variables.get('chunk_size','uploader'),
        max_retries: 3,
        multipart_params: {
            "path": targetPath
        }
    };

    var uploader = new plupload.Uploader(options);
    uploader.bind('BeforeUpload', function (up, files) {
        //Show loading message
        showMessage(
            cx.jQuery('<div />').html(
                cx.jQuery('<div/>')
                .attr('id', 'loading')
                .html(
                    cx.jQuery('<img/>')
                    .attr('src', '/lib/javascript/jquery/jstree/themes/default/throbber.gif')
                    .attr('alt', 'Loading')
                ).append(
                    cx.jQuery('<span />')
                    .text('Loading...')
                )
            ).html(),
            null,
            true
        );
    });

    uploader.bind('FilesAdded', function (up, files) {
        up.start();
    });

    uploader.bind('PostInit', function (up) {
        up.addFile(files);
    });

    uploader.bind('FileUploaded', function (up, file, res) {
        try {
            var response = cx.jQuery.parseJSON(res.response);
            if (response.status != 'error') {
                var editorContent = event.editor.getData();
                event.editor.setData(
                    editorContent.replace(
                        dataSrc[file.name],
                        targetPath + file.name
                    )
                );
                uploadedFilesCount++;
                if (uploadedFilesCount == files.length) {
                    showMessage('Files Uploaded Successfully', null, false);
                }
            } else {
                showMessage(
                    cx.variables.get(
                        'TXT_CORE_MODULE_UPLOADER_ERROR_' + /[0-9]+/.exec(response.message),
                        'mediabrowser'
                    ),
                    'error',
                    false
                );
            }
        } catch (ex) {
            showMessage(
                cx.variables.get(
                    'TXT_CORE_MODULE_UPLOADER_ERROR_200',
                    'mediabrowser'
                ),
                'error',
                false
            );
        }
    });
    uploader.init();
}

/**
 * Show success/error message
 *
 * @param string  message message content
 * @param string  status  upload status
 * @param boolean lock    lock
 */
function showMessage(message, status, lock) {
    var showTime;
    if (lock) {
        showTime = null;
        if (cx.jQuery('#content #load-lock').length > 0) {
            cx.jQuery('#content #load-lock').show();
        } else {
            cx.jQuery('<div />')
                .attr('id', 'load-lock')
                .appendTo('#content')
                .css({
                    'position': 'absolute',
                    'opacity': '0.5',
                    'background-color': 'white',
                    'z-index': '500',
                    'height': '100%',
                    'width': '100%',
                    'top': '0',
                    'left': '0'
                });
        }
    } else {
        showTime = 10000;
        cx.jQuery('#load-lock').hide();
    }
    cx.tools.StatusMessage.showMessage(message, status, showTime);
}

/**
 * Create file by using data URI
 *
 * @param string dataURI data URI
 *
 * @returns {mOxie.File}
 */
function dataURItoFile(dataURI) {
    /* convert base64/URLEncoded data component to raw binary data held in a string */
    var byteString;
    if (dataURI.split(',')[0].indexOf('base64') >= 0) {
        byteString = atob(dataURI.split(',')[1]);
    } else {
        byteString = unescape(dataURI.split(',')[1]);
    }

    /* separate out the mime component*/
    var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];

    /* write the bytes of the string to a typed array */
    var typedArray = new Uint8Array(byteString.length);
    for (var i = 0; i < byteString.length; i++) {
        typedArray[i] = byteString.charCodeAt(i);
    }

    var resultingBlob =  new Blob([typedArray], {type:mimeString});
    return new mOxie.File(null, resultingBlob);
}