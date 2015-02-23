// from: http://plupload-angular-directive.sahusoft.info/#/home

!function (jQuery) {
    'use strict';
    var $J = jQuery;

    angular.module('plupload.module', []).config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
    }])
        .directive('plUpload', ['$parse', function ($parse) {
            return {
                restrict: 'A',
                scope: {
                    'plProgressModel': '=',
                    'plFilesModel': '=',
                    'plFiltersModel': '=',
                    'plMultiParamsModel': '=',
                    'plInstance': '='
                },
                link: function (scope, iElement, iAttrs) {

                    scope.randomString = function (len, charSet) {
                        charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                        var randomString = '';
                        var randomPoz;
                        for (var i = 0; i < len; i++) {
                            randomPoz = Math.floor(Math.random() * charSet.length);
                            randomString += charSet.substring(randomPoz, randomPoz + 1);
                        }
                        return randomString;
                    };

                    if (!iAttrs.id) {
                        iAttrs.$set('id', scope.randomString(10, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'));
                    }
                    if (!iAttrs.plAutoUpload) {
                        iAttrs.$set('plAutoUpload', 'true');
                    }
                    if (!iAttrs.plMaxFileSize) {
                        iAttrs.$set('plMaxFileSize', '500mb');
                    }
                    if (!iAttrs.plUrl) {
                        iAttrs.$set('plUrl', '?cmd=jsondata&object=Uploader&act=upload&id=' + iAttrs.uploaderId + '&csrf=' + cx.variables.get('csrf'));
                    }
                    if (!iAttrs.plFlashSwfUrl) {
                        iAttrs.$set('plFlashSwfUrl', 'lib/plupload/plupload.flash.swf');
                    }
                    if (!iAttrs.plSilverlightXapUrl) {
                        iAttrs.$set('plSilverlightXapUrl', 'lib/plupload/plupload.flash.silverlight.xap');
                    }
                    if (typeof scope.plFiltersModel == "undefined") {
                        scope.filters = [
                            {title: "Allowed files", extensions: "jpg,gif,png,bmp,jpeg,tif,tiff"},
                            {title: "Compressed files", extensions: "zip,tar,gz"},
                            {title: "PDF files", extensions: "pdf"},
                            {title: "Words files", extensions: "doc,docx"}
                        ];
                    } else {
                        scope.filters = scope.plFiltersModel;
                    }

                    $J('#uploader-modal-' + iAttrs.uploaderId).find(' .drop-target').attr('id', 'drop-target-' + iAttrs.id);


                    var options = {
                        runtimes: 'html5,flash,silverlight',
                        multi_selection: true,
                        drop_element: 'drop-target-' + iAttrs.id,
                        browse_button: 'drop-target-' + iAttrs.id,
                        max_file_size: iAttrs.plMaxFileSize,
                        url: iAttrs.plUrl,
                        flash_swf_url: iAttrs.plFlashSwfUrl,
                        silverlight_xap_url: iAttrs.plSilverlightXapUrl,
                        filters: scope.filters,
                        prevent_duplicates: true,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    };


                    if (scope.plMultiParamsModel) {
                        options.multipart_params = scope.plMultiParamsModel;
                    }


                    $J('#drop-target-' + iAttrs.id).bind('dragover', dragover);
                    $J('#drop-target-' + iAttrs.id).bind('dragleave', dragleave);
                    $J('#drop-target-' + iAttrs.id).bind('drop', dragleave);

                    function readablizeBytes(bytes) {
                        var s = ['bytes', 'kB', 'MB', 'GB', 'TB', 'PB'];
                        var e = Math.floor(Math.log(bytes) / Math.log(1024));
                        return (bytes / Math.pow(1024, e)).toFixed(2) + " " + s[e];
                    }

                    function dragover() {
                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .drag-zone').removeClass('fileError');
                        $J('#drop-target-' + iAttrs.id).addClass("dragover");
                    }

                    function dragleave() {
                        $J('#drop-target-' + iAttrs.id).removeClass("dragover");
                    }

                    var uploader = new plupload.Uploader(options);


                    var files = [];

                    $J('#uploader-modal-' + iAttrs.uploaderId).find(' .start-upload-button').bind('click', function () {
                        $J(this).addClass('disabled');
                        uploader.start();
                    });

                    $J(iElement).bind('click', function () {
                        $J('#uploader-modal-' + iAttrs.uploaderId).modal({
                            backdrop: 'static',
                            keyboard: false
                        });
                        $J('#uploader-modal-' + iAttrs.uploaderId).modal('show');
                    });

                    $J('#uploader-modal-' + iAttrs.uploaderId).find(' .close-upload-modal').bind('click', function () {
                        $J('#uploader-modal-' + iAttrs.uploaderId).modal('hide');
                    });

                    $J('#uploader-modal-' + iAttrs.uploaderId).on('hidden.bs.modal', function () {
                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .start-upload-button').removeClass('disabled');
                        var callback = iAttrs.onFileUploaded;
                        var fn = window[callback];
                        if (typeof fn === 'function') {
                            fn(files);
                        }
                        uploader.splice();
                        files = [];
                        $J('.upload-file').remove();
                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .close-upload-modal').addClass('not-finished');
                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .uploadControl').slideUp();
                    });

                    uploader.bind('Error', function (up, err) {
                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .drag-zone').addClass('fileError');
                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .drag-zone .error').html(cx.variables.get('TXT_CORE_MODULE_UPLOADER_ERROR_' + /[0-9]+/.exec(err.code), 'mediabrowser'));

                        setTimeout(function () {
                            $J('#uploader-modal-' + iAttrs.uploaderId).find(' .drag-zone').removeClass('fileError');
                        }, 3000);

                        if (iAttrs.onFileError) {
                            scope.$parent.$apply(onFileError);
                        }
                        up.refresh(); // Reposition Flash/Silverlight
                    });

                    uploader.bind('FilesAdded', function (up, files) {
                        angular.forEach(files, function (file) {

                            $J('#uploader-modal-' + iAttrs.uploaderId).find(' .fileList tr:last').after('<tr style="display:none;" class="upload-file file-' + file.id + '"><td> <div class="previewImage"></div></td><td><div class="fileInfos">    ' + file.name + ' <span class="errorMessage"></span> <div class="progress"> <div class="progress-bar upload-progress" role="progressbar"style="width: 0%"></div></div></div></td><td class="text-right">' + readablizeBytes(file.size) + ' <br/> <a class="remove-file">' + cx.variables.get('TXT_CORE_MODULE_UPLOADER_REMOVE_FILE', 'mediabrowser') + '</a> </td>  </tr>');
                            $J('.file-' + file.id).fadeIn();
                            var removeFile = function () {
                                $J('.file-' + file.id).fadeOut(function () {
                                    $J('.file-' + file.id).remove();
                                    uploader.removeFile(file);
                                    if (uploader.files.length == 0) {
                                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .uploadControl').slideUp();
                                    }
                                });
                            };
                            $J('#uploader-modal-' + iAttrs.uploaderId).find(' .file-' + file.id + ' .remove-file').bind('click', removeFile);

                            var image = $J(new Image()).appendTo('.file-' + file.id + ' .previewImage');
                            var preloader = new mOxie.Image();
                            preloader.onload = function () {
                                preloader.downsize(120, 120);
                                image.attr("src", preloader.getAsDataURL());

                            };
                            preloader.load(file.getSource());
                        });

                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .uploadControl').slideDown();

                    });

                    uploader.bind('FileUploaded', function (up, file, res) {
                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .file-' + file.id + ' .remove-file').remove();
                        try {
                            var response = jQuery.parseJSON(res.response);
                            if (response.status != 'error') {
                                $J('.file-' + file.id).find('.upload-progress').addClass('progress-bar-success');
                                $J('.file-' + file.id).addClass('success');
                                files.push(response.data.file[1]);
                            } else {
                                $J('.file-' + file.id).addClass('danger');
                                $J('.file-' + file.id).find('.upload-progress').addClass('progress-bar-danger');
                                $J('.file-' + file.id).find('.errorMessage').html(cx.variables.get('TXT_CORE_MODULE_UPLOADER_ERROR_' + /[0-9]+/.exec(response.message), 'mediabrowser'));
                                this.trigger('Error', {
                                    code: response.message,
                                    file: file
                                });
                            }
                        } catch (ex) {
                            $J('.file-' + file.id).addClass('danger');
                            $J('.file-' + file.id).find('.upload-progress').addClass('progress-bar-danger');
                            $J('.file-' + file.id).find('.errorMessage').html(cx.variables.get('TXT_CORE_MODULE_UPLOADER_ERROR_200', 'mediabrowser'));
                            this.trigger('Error', {
                                code: 200,
                                file: file
                            });
                        }

                    });

                    uploader.bind('UploadProgress', function (up, file) {
                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .file-' + file.id).find('.upload-progress').css({width: file.percent + '%'});
                    });

                    uploader.bind('UploadComplete', function () {
                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .start-upload-button').removeClass('disabled');
                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .close-upload-modal').removeClass('not-finished');
                    });

                    uploader.init();

                    if (iAttrs.plInstance) {
                        scope.plInstance = uploader;
                    }

                }
            }
                ;
        }])
    ;


}(cx.variables.get('jquery','mediabrowser'));
