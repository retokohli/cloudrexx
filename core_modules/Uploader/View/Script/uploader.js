// from: http://plupload-angular-directive.sahusoft.info/#/home

!function (jQuery) {
    'use strict';
    var $J = jQuery;

    var uploaderModule = angular.module('Uploader', []);

    uploaderModule.filter('translate', function () {
        return function (key) {
            return cx.variables.get(key, 'mediabrowser');
        }
    });

    angular.module('plupload.module', []).config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
    }]);

    jQuery(function () {
        jQuery('button.uploader-button').each(function () {
            angular.bootstrap(jQuery(this).next()[0], ['Uploader']);
            var scope = angular.element(jQuery(this).next()[0]).scope();
            var iAttrs = jQuery(this).data();

            if (!iAttrs.id) {
                jQuery(this).data('id', iAttrs.uploaderId);
            }
            if (!iAttrs.plAutoUpload) {
                jQuery(this).data('plAutoUpload', 'true');
            }
            if (!iAttrs.plMaxFileSize) {
                jQuery(this).data('plMaxFileSize', '500mb');
            }
            if (!iAttrs.plUrl) {
                jQuery(this).data('plUrl', cx.variables.get('cadminPath','contrexx')+'?cmd=jsondata&object=Uploader&act=upload&id=' + iAttrs.uploaderId + '&csrf=' + cx.variables.get('csrf'));
            }
            if (!iAttrs.plFlashSwfUrl) {
                jQuery(this).data('plFlashSwfUrl', 'lib/plupload/plupload.flash.swf');
            }
            if (!iAttrs.plSilverlightXapUrl) {
                jQuery(this).data('plSilverlightXapUrl', 'lib/plupload/plupload.flash.silverlight.xap');
            }
            if (!iAttrs.uploadLimit) {
                jQuery(this).data('uploadLimit', "0");
            }
            if (iAttrs.uploaderType == 'Inline'){
                jQuery('.close-upload-modal').hide();
            }
            if (!iAttrs.allowedExtensions) {
                iAttrs.allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'gif', 'mkv', 'zip', 'tar', 'gz', 'docx',
                    'doc','mp3','wav','act','aiff','aac','amr','ape','au','awb','dct','dss','flac','gsm','m4a','m4p',
                    'mp3','mpc','ogg','oga','opus','ra','rm','raw','sln','tta','vox','wav','wma','wv','webm'];
            }

            if (typeof scope.plFiltersModel == "undefined") {
                scope.filters = [
                    {title: "Allowed files", extensions: iAttrs.allowedExtensions.join(',')}
                ];
            } else {
                scope.filters = scope.plFiltersModel;
            }

            $J('#uploader-modal-' + iAttrs.uploaderId).find(' .drop-target').attr('id', 'drop-target-' + iAttrs.id);
            $J('#uploader-modal-' + iAttrs.uploaderId).find('.upload-limit-tooltip .btn').attr('id', 'drop-target-btn-' + iAttrs.id);

            if (iAttrs.uploadLimit > 0) {
                $J('#uploader-modal-' + iAttrs.uploaderId)
                    .find('.notify-UploadLimit')
                    .html(cx.variables.get('TXT_CORE_MODULE_UPLOADER_MAX_LIMIT', 'mediabrowser') + iAttrs.uploadLimit)
                    .show();
            }

            var uploaderData = {
                filesToUpload: [],
                uploaded_file_count: 0,
                uploadOverwiteOnLimit: false,
                updateTooltip: function (elem, option, operation, force) {
                    switch (operation) {
                        case 'add':
                            if ($J(elem).data("bs.tooltip")) {
                                $J(elem).tooltip('destroy');
                            }
                            if (force) {
                                $J(elem).tooltip({
                                    title: option.title
                                });
                            }
                            break;
                        case 'remove':
                            if (force && $J(elem).data("bs.tooltip")) {
                                $J(elem).tooltip('destroy');
                            }
                            break;
                        default:
                            break;
                    }
                },
                overWriteFile :function(files) {
                    var removedItems = uploaderData.filesToUpload.splice(0, files.length);
                    angular.forEach(removedItems, function (file) {
                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .file-' + file.id).remove();
                        uploader.removeFile(file);
                    });
                }
            };

            var options = {
                runtimes: 'html5,flash,silverlight',
                multi_selection: (iAttrs.uploadLimit !== 1) ? true : false,
                drop_element: 'drop-target-' + iAttrs.id,
                browse_button: 'drop-target-btn-' + iAttrs.id,
                max_file_count: iAttrs.uploadLimit,
                max_file_size: iAttrs.plMaxFileSize,
                url: iAttrs.plUrl,
                flash_swf_url: iAttrs.plFlashSwfUrl,
                silverlight_xap_url: iAttrs.plSilverlightXapUrl,
                filters: scope.filters,
                prevent_duplicates: true,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                chunk_size: '500kb'
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

            $J(this).bind('click', function () {
                $J('#uploader-modal-' + iAttrs.uploaderId).modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $J('#uploader-modal-' + iAttrs.uploaderId).modal('show');
            });
            $J(this).removeAttr('disabled');

            $J('#uploader-modal-' + iAttrs.uploaderId).find(' .close-upload-modal').bind('click', function () {
                $J('#uploader-modal-' + iAttrs.uploaderId).modal('hide');
            });


            var uploadFinished = function () {
                $J('#uploader-modal-' + iAttrs.uploaderId).find(' .start-upload-button').removeClass('disabled');
                var callback = iAttrs.onFileUploaded;
                if (callback){
                    var windowScope = window;
                    var scopeSplit = callback.split('.');
                    for (var i = 0; i < scopeSplit.length - 1; i++)
                    {
                        windowScope = windowScope[scopeSplit[i]];
                        if (scope == undefined) return;
                    }
                    var fn = windowScope[scopeSplit[scopeSplit.length - 1]];
                    if (typeof fn === 'function') {
                        fn(files);
                    }
                }

                uploader.splice();
                files = [];
                $J('.upload-file').remove();
                $J('#uploader-modal-' + iAttrs.uploaderId).find(' .close-upload-modal').addClass('not-finished');
                $J('#uploader-modal-' + iAttrs.uploaderId).find(' .uploadControl').slideUp();

                //Reset uploader settings
                if (uploader.settings.max_file_count <= uploaderData.filesToUpload.length) {
                    uploaderData.filesToUpload = [];
                    if (uploaderData.uploaded_file_count !== 0) {
                        uploaderData.uploadOverwiteOnLimit = true;
                        uploaderData.uploaded_file_count = 0;
                        uploaderData.updateTooltip('#uploader-modal-' + iAttrs.uploaderId + ' .upload-limit-tooltip.file_upload', {title: cx.variables.get('TXT_CORE_MODULE_UPLOADER_MAX_LIMIT_OVERWRITE', 'mediabrowser')}, 'add', uploaderData.uploadOverwiteOnLimit);
                    }

                    if (!uploaderData.uploadOverwiteOnLimit && uploaderData.uploaded_file_count == 0) {
                        uploaderData.updateTooltip('#uploader-modal-' + iAttrs.uploaderId + ' .upload-limit-tooltip.file_upload', {title: cx.variables.get('TXT_CORE_MODULE_UPLOADER_MAX_LIMIT_OVERWRITE', 'mediabrowser')}, 'add', !uploaderData.uploadOverwiteOnLimit);
                        uploaderData.updateTooltip('#uploader-modal-' + iAttrs.uploaderId + ' .upload-limit-tooltip.file_choose', '', 'remove', !uploaderData.uploadOverwiteOnLimit);
                    }

                    uploader.settings.url = iAttrs.plUrl + '&csrf=' + cx.variables.get('csrf');
                }

            };

            $J('#uploader-modal-' + iAttrs.uploaderId).on('hidden.bs.modal',uploadFinished);

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
                if (up.settings.max_file_count > 0 && uploaderData.filesToUpload.length !== '' && uploaderData.filesToUpload.length >= up.settings.max_file_count) {
                    uploaderData.overWriteFile(files);
                }

                for (var file in files) {
                    uploaderData.filesToUpload.push(files[file]);
                }

                uploaderData.updateTooltip('#uploader-modal-' + iAttrs.uploaderId + ' .upload-limit-tooltip.file_upload', '', 'remove', !uploaderData.uploadOverwiteOnLimit);

                if ((up.settings.max_file_count > 0) && uploaderData.filesToUpload.length >= up.settings.max_file_count) {
                    uploaderData.updateTooltip('#uploader-modal-' + iAttrs.uploaderId + ' .upload-limit-tooltip.file_choose', {title: cx.variables.get('TXT_CORE_MODULE_UPLOADER_MAX_LIMIT_OVERWRITE', 'mediabrowser')}, 'add', true);
                    if (uploaderData.filesToUpload.length > up.settings.max_file_count) {
                      uploaderData.updateTooltip('#uploader-modal-' + iAttrs.uploaderId + ' .upload-limit-tooltip.file_upload', {title: cx.variables.get('TXT_CORE_MODULE_UPLOADER_MAX_LIMIT', 'mediabrowser') + up.settings.max_file_count}, 'add', true);
                      $J('#uploader-modal-' + iAttrs.uploaderId).find(' .start-upload-button').addClass('disabled');
                    }
                }

                angular.forEach(files, function (file) {

                    $J('#uploader-modal-' + iAttrs.uploaderId).find(' .fileList tr:last').after('<tr style="display:none;" class="upload-file file-' + file.id + '"><td> <div class="previewImage"></div></td><td><div class="fileInfos">    ' + file.name + ' <span class="errorMessage"></span> <div class="progress"> <div class="progress-bar upload-progress" role="progressbar"style="width: 0%"></div></div></div></td><td class="text-right">' + readablizeBytes(file.size) + ' <br/> <a class="remove-file">' + cx.variables.get('TXT_CORE_MODULE_UPLOADER_REMOVE_FILE', 'mediabrowser') + '</a> </td>  </tr>');
                    $J('.file-' + file.id).fadeIn();
                    var removeFile = function () {
                        $J.each(uploaderData.filesToUpload, function (i) {
                            $J('.file-' + file.id).fadeOut(function () {
                                if (uploaderData.filesToUpload[i] === file) {
                                    uploaderData.filesToUpload.splice(i, 1);
                                    $J('.file-' + file.id).remove();
                                    uploader.removeFile(file);
                                    if (up.settings.max_file_count > 0 && uploaderData.filesToUpload.length <= up.settings.max_file_count) {
                                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .start-upload-button').removeClass('disabled');
                                        uploaderData.updateTooltip('#uploader-modal-' + iAttrs.uploaderId + ' .upload-limit-tooltip.file_upload', {title: cx.variables.get('TXT_CORE_MODULE_UPLOADER_MAX_LIMIT_OVERWRITE', 'mediabrowser')}, 'add', uploaderData.uploadOverwiteOnLimit);
                                        if (uploaderData.filesToUpload.length < up.settings.max_file_count) {
                                          uploaderData.updateTooltip('#uploader-modal-' + iAttrs.uploaderId + ' .upload-limit-tooltip.file_choose', '', 'remove', true);
                                        }
                                    }
                                    if (uploaderData.filesToUpload.length == 0) {
                                        $J('#uploader-modal-' + iAttrs.uploaderId).find(' .uploadControl').slideUp();
                                    }
                                }
                            });
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
                        if (up.settings.max_file_count > 0) {
                            ++uploaderData.uploaded_file_count;
                            uploader.settings.url =  iAttrs.plUrl + '&csrf=' + cx.variables.get('csrf') + '&uploadedFileCount=' + uploaderData.uploaded_file_count + '&csrf=' + cx.variables.get('csrf');
                        }
                        if ((response.data.status == 'error')) {
                            parseStatusMessage(this, file, 'danger', response.data.message, true, 200);
                        } else {
                            files.push(response.data.file[1]);
                        }
                        if (typeof response.data.response != 'undefined') {
                            var displayStatus = 'success';
                            var html = '<ul>';
                            var progress = false;
                            var errorCode = false;
                            $J(response.data.response).each(function (key, values) {
                                html += '<li class=' + values.status + '>' + values.message + '</li>';
                                if (values.status == 'error') {
                                    progress = true;
                                    displayStatus = 'danger';
                                    errorCode = 200;
                                }
                            });
                            html += '</ul>';
                            parseStatusMessage(this, file, displayStatus, html, progress, errorCode);
                        }
                    } else {
                        parseStatusMessage(this, file, 'danger', cx.variables.get('TXT_CORE_MODULE_UPLOADER_ERROR_' + /[0-9]+/.exec(response.message), 'mediabrowser'), true, response.message);
                    }
                } catch (ex) {
                    parseStatusMessage(this, file, 'danger', cx.variables.get('TXT_CORE_MODULE_UPLOADER_ERROR_200', 'mediabrowser'), true, 200);
                }

            });

            uploader.bind('UploadProgress', function (up, file) {
                $J('#uploader-modal-' + iAttrs.uploaderId).find(' .file-' + file.id).find('.upload-progress').css({width: file.percent + '%'});
            });

            uploader.bind('Init', function (upload) {
                $J('.uploader-modal .file_choose .btn').prop("disabled", false);
            });

            uploader.bind('UploadComplete', function () {
                $J('#uploader-modal-' + iAttrs.uploaderId).find(' .start-upload-button').removeClass('disabled');
                $J('#uploader-modal-' + iAttrs.uploaderId).find(' .close-upload-modal').show();
                $J('#uploader-modal-' + iAttrs.uploaderId).find(' .close-upload-modal').removeClass('not-finished');
                uploaderData.updateTooltip('#uploader-modal-' + iAttrs.uploaderId + ' .upload-limit-tooltip.file_upload', '', 'remove', true);
                if (iAttrs.uploaderType == 'Inline'){
                    uploadFinished();
                }
            });

            // workaround to make the upload-button work in Chromium
            $J('#drop-target-btn-' + iAttrs.uploaderId).mouseenter(function() {
                uploader.refresh();
            });

            uploader.init();

            function parseStatusMessage(objElement, file, status, message, progress, code) {
                $J('.file-' + file.id).addClass(status);
                if (progress) {
                    $J('.file-' + file.id).find('.upload-progress').addClass('progress-bar-danger');
                }
                $J('.file-' + file.id).find('.errorMessage').html(message);
                if (code) {
                    objElement.trigger('Error', {
                        file: file,
                        code: code
                    });
                }
            }

            if (iAttrs.plInstance) {
                scope.plInstance = uploader;
            }
        });
    });


}(cx.variables.get('jquery', 'mediabrowser'));
