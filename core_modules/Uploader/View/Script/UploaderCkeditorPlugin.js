var dataImagePattern = /<img\s+[^>]*src=([\'\"])(data\:(\s|)image\/(\w{3,4})\;base64\,(\s|)([^\'\"]*)\s*)\1[^>]*>/g;
for(var instanceName in CKEDITOR.instances) {
    var editor = CKEDITOR.instances[instanceName], match;
    editor.on('paste', function (event) {
        var data = event.data.dataValue, files = [], dataSrc = [];
        while (match = dataImagePattern.exec(data)) {
            var file = dataURItoBlob(match[2]);
            files.push(file);
        }

        if (!files || files.length === 0) {
            return;
        }
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
            return;
        }

        var uploaderId = cx.variables.get('ckeditorUploaderId', 'wysiwyg');
        jQuery('<a/>')
            .attr('id', 'wysiwygPasteUploadButton_' + uploaderId)
            .attr('style', 'display:none')
            .appendTo(document.body);

        var options = {
            runtimes: 'html5,flash,silverlight,html4',
            multi_selection: true,
            max_file_size: '500mb',
            browse_button: 'wysiwygPasteUploadButton_' + uploaderId,
            url: cx.variables.get('cadminPath','contrexx')+'?cmd=JsonData&object=Uploader&act=upload&csrf=' + cx.variables.get('csrf'),
            flash_swf_url: cx.variables.get('basePath','contrexx')+'lib/plupload/js/Moxie.swf',
            silverlight_xap_url: cx.variables.get('basePath','contrexx')+'lib/plupload/js/Moxie.xap',
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
        uploader.bind('FilesAdded', function (up, files) {
            up.start();
        });
        uploader.bind('PostInit', function (up) {
            up.addFile(files);
        });
        uploader.bind('FileUploaded', function (up, file, res) {
            //TODO: replace inline-images by their new uploaded file-path in content
        });
        uploader.init();
    });
}

function dataURItoBlob(dataURI) {
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