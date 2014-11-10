!function (jQuery) {
    var $ = jQuery;

    var mediaBrowserApp = angular.module('contrexxApp', ['plupload.module', 'ngAnimate', 'ui.bootstrap', 'ui.bootstrap.tpls']);

    mediaBrowserApp.config(['$provide', function ($provide) {
        $provide.decorator('$browser', ['$delegate', function ($delegate) {
            $delegate.onUrlChange = function (a) {
            };
            $delegate.history = false;
            $delegate.url = function () {
                return "";
            };
            return $delegate;
        }]);
    }]);

    mediaBrowserApp.filter('translate', function () {
        return function (key) {
            return cx.variables.get(key, 'mediabrowser');
        }
    });

    mediaBrowserApp.factory('mediabrowserFiles', function ($http, $q) {
        return {
            get: function (type) {
                var deferred = $q.defer();
                $http.get('cadmin/index.php?cmd=jsondata&object=MediaBrowser&act=' + type + '&csrf=' + cx.variables.get('csrf')).success(function (jsonadapter) {
                    if (jsonadapter.data instanceof Object) {
                        deferred.resolve(jsonadapter.data);
                    }
                    else {
                        deferred.reject("An error occured while fetching items for " + type);
                    }
                }).error(function () {
                    deferred.reject("An error occured while fetching items for " + type);
                });
                return deferred.promise;
            },
            getByMediaType: function (mediatype) {
                return this.get('getFiles&mediatype=' + mediatype)
            }
        }
    });

    mediaBrowserApp.factory('mediabrowserConfig', function () {
        var config = {};
        return {
            set: function (key, value) {
                config[key] = value;
            },
            get: function (key) {
                return config[key];
            }
        };
    });

    /* CONTROLLERS */
    mediaBrowserApp.controller('MainCtrl', ['$scope', '$modalInstance', '$modal', '$location', '$http', 'mediabrowserConfig', 'mediabrowserFiles',
        function ($scope, $modalInstance, $modal, $location, $http, mediabrowserConfig, mediabrowserFiles) {
            /**
             * Sorting and searching
             */
            $scope.sorting = 'cleansize';
            $scope.searchFile = '';
            $scope.isRegex = false;
            $scope.reverse = false;

            $scope.sources = [];
            $scope.fileCallBack = '';
            $scope.files = [];
            $scope.dataFiles = [];
            $scope.sites = [];


            $scope.path = [
                {name: 'Dateien', path: 'files', standard: true}
            ];

            $scope.activeController = mediabrowserConfig.get('startView');

            mediabrowserFiles.get('getSites').then(
                function getSites(data) {
                    $scope.sites = data;
                }
            );

            mediabrowserFiles.get('getSources').then(
                function getSources(data) {
                    $scope.sources = data;

                    if (mediabrowserConfig.get('startMedia')) {
                        data.forEach(function (source) {
                            if (source.value == mediabrowserConfig.get('startMedia')) {
                                $scope.selectedSource = source;
                                return false;
                            }
                        });
                    }
                    else {
                        $scope.selectedSource = data[0];
                    }
                    $scope.path[0].path = $scope.selectedSource.value;
                    if (mediabrowserConfig.get('mediatypes') != 'all') {
                        var i = data.length;
                        while (i--) {
                            if (!(mediabrowserConfig.get('mediatypes').indexOf(data[i].value) > -1)) {
                                data.splice(i, 1);
                            }
                        }
                    }

                    mediabrowserFiles.getByMediaType($scope.selectedSource.value).then(
                        function getFiles(data) {
                            $scope.dataFiles = data;
                            $scope.files = $scope.dataFiles;
                        }
                    );
                }, function (reason) {
                    bootbox.dialog({
                        title: "An error has occurred.",
                        message: reason
                    });
                }
            );

            $scope.ok = function () {
                $modalInstance.close();
            };

            $scope.cancel = function () {
                $scope.closeModal();
            };

            $scope.changeSorting = function (newSorting) {
                if (newSorting == $scope.sorting) {
                    $scope.reverse = !$scope.reverse;
                }
                else {
                    $scope.sorting = newSorting;
                    $scope.reverse = false;
                }
            };

            $scope.closeModal = function () {


                $modalInstance.dismiss('cancel');
                var fn = mediabrowserConfig.get('callbackWrapper');
                if (typeof fn === 'function') {
                    fn({type: 'close', data: []});
                    $scope.selectedFiles = [];
                }
            };


            $scope.dataTabs = [
                {
                    label: 'Hochladen',
                    icon: 'icon-upload',
                    controller: 'UploaderCtrl',
                    name: 'uploader'
                },
                {
                    label: 'Ablage',
                    icon: 'icon-folder',
                    controller: 'MediaBrowserListCtrl',
                    name: 'filebrowser'
                },
                {
                    label: 'Seitenstruktur',
                    icon: 'icon-sitestructure',
                    controller: 'SitestructureCtrl',
                    name: 'sitestructure'
                }
            ];
            $scope.tabs = $scope.dataTabs;

            $scope.go = function (path) {
                $scope.activeController = path;
            };

            $scope.getPathAsString = function () {
                var pathstring = '';
                $scope.path.forEach(function (path) {
                    pathstring += path.path + '/';
                });
                return pathstring;
            };

            //// cx-mb-views
            if (mediabrowserConfig.get('views') != 'all') {
                var isStartviewInViews = false;
                var newTabNames = mediabrowserConfig.get('views');

                var newTabs = [];

                newTabNames.forEach(function (newTabName) {
                    $scope.dataTabs.forEach(function (tab) {
                        if (tab.name === newTabName) {
                            if (newTabName === mediabrowserConfig.get('startView'))
                                isStartviewInViews = true;
                            newTabs.push(tab);
                        }
                    });
                });
                $scope.tabs = newTabs;
                if (!isStartviewInViews) {
                    $scope.go($scope.tabs[0].controller);
                }

                if (newTabs.length === 1) {
                    //jQuery(".mediaBrowserMain").addClass('no-nav');
                }
            }

            $scope.selectedTab = $scope.tabs[0];
            $scope.setSelectedTab = function (tab) {
                $scope.activeController = tab.controller;
            };

            $scope.updateSource = function () {
                $scope.path = [
                    {name: "" + $scope.selectedSource.name, path: $scope.selectedSource.value, standard: true}
                ];
                jQuery(".loadingPlatform").show();
                jQuery(".filelist").hide();
                mediabrowserFiles.getByMediaType($scope.selectedSource.value).then(
                    function getFiles(data) {
                        jQuery(".loadingPlatform").hide();
                        jQuery(".filelist").show();
                        $scope.dataFiles = data;
                        $scope.files = $scope.dataFiles;
                    }
                );
            };

            $scope.changeLocation = function (url, forceReload) {
                $scope = $scope || angular.element(document).scope();
                if (forceReload || $scope.$$phase) {
                    window.location = url;
                }
                else {
                    $location.path(url);
                    $scope.$apply();
                }
            };

            $scope.createFolder = function () {
                bootbox.prompt("Verzeichnisname:", function (dirName) {
                    if (dirName === null) {

                    } else {
                        $http({
                            method: 'POST',
                            url: 'cadmin/index.php?cmd=jsondata&object=Uploader&act=createDir&path=' + $scope.getPathAsString() + '&csrf=' + cx.variables.get('csrf'),
                            data: $.param({dir: dirName}),
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                        }).success(function (jsonadapter) {
                            $scope.updateSource();
                            bootbox.alert(jsonadapter.message);
                        });
                    }
                });
            };

            $scope.afterUpload = function () {
                $scope.updateSource();
            };

        }]);


    mediaBrowserApp.controller('UploaderCtrl', ['$scope', '$http',
        function ($scope, $http) {

            $scope.uploaderData = {
                filesToUpload: []
            };

            $scope.progress = 0;
            $scope.progressMessage = '';
            $scope.finishedUpload = false;




            $scope.template = {
                url: '../core_modules/MediaBrowser/View/Template/_Uploader.html'
            };

            $scope.loadedTemplate = function () {
                // PLUPLOADER INTEGRATION
                $scope.uploader = new plupload.Uploader({
                    runtimes: 'html5,flash,silverlight,html4',
                    browse_button: 'selectFileFromComputer',
                    container: 'uploader',
                    drop_element: "uploader",
                    url: '?csrf=' + cx.variables.get('csrf') + '&cmd=jsondata&object=Uploader&act=upload',
                    flash_swf_url: '/lib/plupload/js/Moxie.swf',
                    silverlight_xap_url: '/lib/plupload/js/Moxie.xap',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    filters: {
                        max_file_size: '50cxMb',
                        mime_types: [
                            {title: "Allowed files", extensions: "jpg,gif,png,bmp,jpeg,tif,tiff"},
                            {title: "Compressed files", extensions: "zip,tar,gz"},
                            {title: "PDF files", extensions: "pdf"},
                            {title: "Words files", extensions: "doc,docx"}
                        ]
                    },
                    multipart_params: {
                        "path": ''
                    },
                    init: {
                        FilesAdded: function (up, files) {
                            if ($scope.finishedUpload) {
                                $scope.uploaderData.filesToUpload = [];
                                $scope.finishedUpload = false;
                            }
                            for (var file in files) {
                                $scope.uploaderData.filesToUpload.push(files[file]);
                            }
                            $scope.uploader.settings.multipart_params.path = $scope.getPathAsString();

                            $scope.$digest();
                        },
                        UploadProgress: function (up, file) {
                            $scope.$digest();
                        },
                        UploadComplete: function () {
                            $scope.finishedUpload = true;
                            jQuery('.uploadFilesAdded').hide();
                            $scope.afterUpload();
                        },
                        Error: function (up, err) {
                            console.log("nError #" + err.code + ": " + err.message)
                        }
                    }
                });
                $scope.uploader.init();
            };

            $scope.startUpload = function () {
                $scope.uploader.start();
                jQuery('.uploadFilesAdded').show();
            };

            $scope.removeFile = function (file) {
                jQuery.each($scope.uploaderData.filesToUpload, function (i) {
                    if ($scope.uploaderData.filesToUpload[i] === file) {
                        $scope.uploaderData.filesToUpload.splice(i, 1);
                        $scope.uploader.removeFile(file);
                        return false;
                    }
                });
            };

        }]);

    mediaBrowserApp.controller('MediaBrowserListCtrl', ['$scope', '$http', 'mediabrowserConfig',
        function ($scope, $http, mediabrowserConfig) {

            $scope.inRootDirectory = true;
            $scope.lastActiveFile = {};
            $scope.noFileSelected = true;
            $scope.selectedFiles = [];

            $scope.template = {
                url: '../core_modules/MediaBrowser/View/Template/_FileBrowser.html'
            };
            // __construct

            $scope.extendPath = function (dirName) {
                if (Array.isArray($scope.path)) {
                    $scope.path.push({name: dirName, path: dirName, standard: false});
                }
                $scope.inRootDirectory = ($scope.path.length == 1);
                $scope.searchFile = '';
                $scope.refreshBrowser();
            };


            $scope.shrinkPath = function (countDirs) {
                if (Array.isArray($scope.path)) {
                    for (var i = 0; i < countDirs; i++) {
                        if ($scope.path[$scope.path.length - 1].standard === false)
                            $scope.path = $scope.path.slice(0, -1);
                    }
                }
                $scope.inRootDirectory = ($scope.path.length == 1);
                $scope.refreshBrowser();
            };

            $scope.getPathString = function () {
                var returnValue = '/';
                $scope.path.forEach(function (pathpart) {
                    returnValue += pathpart.path + '/';
                });
                return returnValue;
            };

            $scope.refreshBrowser = function () {
                $scope.selectedFiles = [];
                $scope.files = $scope.dataFiles;
                $scope.path.forEach(function (pathpart) {
                    if (!pathpart.standard) {
                        $scope.files = $scope.dataFiles[pathpart.path];
                    }
                });
                $scope.inRootDirectory = ($scope.path.length == 1);
            };

            /**
             *
             * @param file String The clicked file.
             * @param index Integer Index of the file
             * @param selectFile Boolean If the file has been double clicked, this is true.
             */
            $scope.clickFile = function (file, index, selectFile) {
                if (file.datainfo.extension == 'Dir') {
                    $scope.extendPath(file.datainfo.name);
                }
                else {
                    if (file.datainfo.active === true && !selectFile) {
                        for (var i in $scope.selectedFiles) {
                            if ($scope.selectedFiles[i].datainfo.id == file.datainfo.id) {
                                $scope.selectedFiles.splice(i, 1);
                            }
                        }
                        file.datainfo.active = false;
                    }
                    else {
                        file.datainfo.active = true;
                        $scope.selectedFiles.push(file);
                        if (!mediabrowserConfig.get('multipleSelect') && selectFile) {
                            $scope.selectedFiles = [];
                            file.datainfo.active = false;

                            var fn = window[mediabrowserConfig.get('modalClosed')];

                            if (typeof fn === 'function') {
                                fn({type: 'file', data: [file]});
                            }

                            if (!jQuery.isEmptyObject($scope.lastActiveFile)) {
                                $scope.lastActiveFile.datainfo.active = false;
                            }
                            $scope.lastActiveFile = file;
                            $scope.closeModal();
                        }
                    }

                    $scope.noFileSelected = $scope.selectedFiles.length == 0;
                }
            };


            $scope.renameFile = function (file, index) {
                var splittedFileName = [];

                splittedFileName[0] = file.datainfo.name;
                if (file.datainfo.extension !== 'Dir') {
                    splittedFileName = file.datainfo.name.split('.');
                }

                var fileExtension = '';
                var fileName = file.datainfo.name;
                if (splittedFileName.length != 1) {
                    fileExtension = splittedFileName.pop();
                    fileName = splittedFileName.join('.');
                }


                var renameForm = '<div id="mediabrowser-renamefile">' +
                    '<div class="file-name"><input type="text" class="form-control" value="' + fileName + '"/></div>' +
                    '<div class="file-dot">.</div>' +
                    '<div class="file-extension"><input type="text" class="form-control" value="' + fileExtension + '" disabled/></div>  </div>';
                bootbox.dialog({
                    title: "Datei umbennenen",
                    message: renameForm,
                    buttons: {

                        danger: {
                            label: "Abbrechen",
                            className: "btn-danger",
                            callback: function () {

                            }
                        },
                        success: {
                            label: "Umbenennen",
                            className: "btn-success",
                            callback: function () {
                                var newName = $('#mediabrowser-renamefile .file-name input').val();
                                if (newName === null) {

                                } else {
                                    $http({
                                        method: 'POST',
                                        url: 'index.php?cmd=jsondata&object=Uploader&act=renameFile&path=' + $scope.getPathAsString() + '&csrf=' + cx.variables.get('csrf'),
                                        data: $.param({
                                            oldName: file.datainfo.name,
                                            newName: newName
                                        }),
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }

                                    }).success(function (jsonadapter) {
                                        if (jsonadapter.message) {
                                            bootbox.alert(jsonadapter.message);
                                        }
                                        else {
                                            bootbox.alert('Ein Fehler ist aufgetreten');
                                        }
                                        $scope.updateSource();
                                    });
                                }
                            }
                        }
                    }
                });
            };


            $scope.removeFile = function (file, index) {
                bootbox.confirm(cx.variables.get('TXT_FILEBROWSER_ARE_YOU_SURE', 'mediabrowser'), function (result) {
                    if (result === null) {

                    } else {
                        $http({
                            method: 'POST',
                            url: 'index.php?cmd=jsondata&object=Uploader&act=removeFile&path=' + $scope.getPathAsString() + '&csrf=' + cx.variables.get('csrf'),
                            data: $.param({
                                file: file
                            }),
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).success(function (jsonadapter) {
                            $scope.updateSource();
                        });

                        $scope.refreshBrowser();
                    }
                });
            };

            $scope.clickPath = function (index) {
                var shrinkby = $scope.path.length - index - 1;
                if (shrinkby > 0) {
                    $scope.shrinkPath(shrinkby);
                }
            };

            $scope.choosePictures = function () {
                console.log(mediabrowserConfig.get('callbackWrapper'));
                var fn = mediabrowserConfig.get('callbackWrapper');
                $scope.closeModal();
                for (var i = 0; i < $scope.selectedFiles.length; i++) {
                    var item = $scope.selectedFiles[i];
                    item.datainfo.active = false;
                }
                console.log(typeof fn === 'function');
                console.log(fn);
                if (typeof fn === 'function') {
                    fn({type: 'file', data: $scope.selectedFiles});
                    $scope.selectedFiles = [];
                }
            };
        }
    ]);

    mediaBrowserApp.controller('SitestructureCtrl', ['$scope', 'mediabrowserConfig',
        function ($scope, mediabrowserConfig) {

            $scope.template = {
                url: '../core_modules/MediaBrowser/View/Template/_Sitestructure.html'
            };

            $scope.clickPage = function (site) {
                var fn = window[mediabrowserConfig.get('modalClosed')];
                if (typeof fn === 'function') {
                    fn({type: 'page', data: [site]});
                }
                $scope.closeModal();
            }

        }
    ]);


    /* DIRECTIVES */
    /* preview function */
    mediaBrowserApp.directive('previewImage', function () {
        return {
            restrict: 'A',
            link: function (scope, el, attrs) {
                if (attrs.previewImage !== 'none') {
                    $J.ajax({
                        type: "GET",
                        url: "index.php?cmd=jsondata&object=MediaBrowser&act=createThumbnails&file=" + attrs.previewImage
                    }).done(function (msg) {
                        jQuery(el).popover({
                            trigger: 'hover',
                            html: true,
                            content: '<img src="' + attrs.previewImage + '"  />',
                            placement: 'right'
                        });
                    });

                }
            }
        };
    });

    mediaBrowserApp.directive('sglclick', ['$parse', function ($parse) {
        return {
            restrict: 'A',
            link: function (scope, element, attr) {
                var fn = $parse(attr['sglclick']);
                var delay = 300, clicks = 0, timer = null;
                element.on('click', function (event) {
                    clicks++;  //count clicks
                    if (clicks === 1) {
                        timer = setTimeout(function () {
                            scope.$apply(function () {
                                fn(scope, {$event: event});
                            });
                            clicks = 0;             //after action performed, reset counter
                        }, delay);
                    } else {
                        clearTimeout(timer);    //prevent single-click action
                        clicks = 0;             //after action performed, reset counter
                    }
                });
            }
        };
    }]);

    mediaBrowserApp.filter('findPage', function () {
        return function (items, search) {
            if (!items) {
                return [];
            }
            var filtered = [];
            var letterMatch = new RegExp(search, 'i');
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                if (letterMatch.test(item.name)) {
                    filtered.push(item);
                }
            }
            return filtered;
        };
    });

    mediaBrowserApp.filter('orderAndSearchFiles', function () {
        return function (input, attribute, reverse, searchFile, isRegex) {
            if (!angular.isObject(input)) return input;
            var array = [];
            for (var objectKey in input) {
                array.push(input[objectKey]);
            }
            array.sort(function (a, b) {
                if (a.datainfo && b.datainfo) {
                    a = (a['datainfo'][attribute]);
                    b = (b['datainfo'][attribute]);
                    if (reverse) {
                        if (a < b) return -1;
                        if (a > b) return 1;
                    }
                    else {
                        if (a < b) return 1;
                        if (a > b) return -1;
                    }
                }
                return 0;
            });
            if (searchFile != '') {
                var searchObject;
                if (isRegex) {
                    searchObject = function () {
                        var regex = new RegExp(searchFile, 'i');
                        return function (string) {
                            return regex.test(string);
                        }
                    }
                }
                else {
                    searchObject = function () {
                        var fileSearchWords = searchFile.split(" ");
                        return function (string) {
                            for (var key in fileSearchWords) {
                                if (!(string.toLowerCase().indexOf(fileSearchWords[key].toLowerCase()) >= 0)) {
                                    return false;
                                }
                            }
                            return true;
                        }
                    }
                }
                return recursiveSearch(searchObject, array, 0);
            }
            return array;
        }

    });


    function recursiveSearch(searchObject, searchArray, level) {
        if (level > 6) { //TODO Is this really necessary?
            return [];
        }
        var resultArray = [];
        for (var key in searchArray) {
            if (key == 'datainfo') {
                continue;
            }
            if (searchArray[key]['datainfo'] != undefined) {
                if (searchArray[key]['datainfo']['name'] != undefined) {
                    if (searchObject()(searchArray[key]['datainfo']['name'])) {
                        resultArray.push(searchArray[key]);
                    }
                }
            }
            if (searchArray[key] instanceof Object) {
                resultArray = resultArray.concat(recursiveSearch(searchObject, searchArray[key], level++));
            }
        }
        return resultArray;
    }

    /* button to modal */
    mediaBrowserApp.directive('cxMb', ['$modal', 'mediabrowserConfig', function ($modal, mediabrowserConfig) {
        return {
            restrict: 'A', // only work with elements including the attribute cxMb
            link: function (scope, el, attrs) {
                jQuery(el).click(function (event) {
                    /**
                     * Set all options and default values
                     */
                    mediabrowserConfig.set('startView', 'MediaBrowserListCtrl');
                    if (attrs.cxMbStartview) {
                        mediabrowserConfig.set('startView', attrs.cxMbStartview.charAt(0).toUpperCase() + attrs.cxMbStartview.slice(1) + "Ctrl");
                    }

                    mediabrowserConfig.set('views', 'all');
                    if (attrs.cxMbViews) {
                        mediabrowserConfig.set('views', attrs.cxMbViews.trim().split(","));
                    }

                    mediabrowserConfig.set('startMedia', 'files');
                    if (attrs.cxMbStartmediatype) {
                        mediabrowserConfig.set('startMedia', attrs.cxMbStartmediatype);
                    }

                    mediabrowserConfig.set('mediatypes', 'all');
                    if (attrs.cxMbMediatypes) {
                        mediabrowserConfig.set('mediatypes', attrs.cxMbMediatypes.split(/[\s,]+/));
                    }

                    mediabrowserConfig.set('multipleSelect', false);
                    if (attrs.cxMbMultipleselect) {
                        mediabrowserConfig.set('multipleSelect', attrs.cxMbMultipleselect);
                    }

                    mediabrowserConfig.set('modalOpened', false);
                    if (attrs.cxMbCbJsModalopened) {
                        mediabrowserConfig.set('modalOpened', attrs.cxMbCbJsModalopened);
                    }

                    mediabrowserConfig.set('modalClosed', false);
                    console.log(attrs.cxMbCbJsModalclosed);
                    if (attrs.cxMbCbJsModalclosed) {
                        mediabrowserConfig.set('modalClosed', attrs.cxMbCbJsModalclosed);
                    }

                    $modal.open({
                        templateUrl: '../core_modules/MediaBrowser/View/Template/MediaBrowserModal.html',
                        controller: 'MainCtrl',
                        dialogClass: 'media-browser-modal',
                        size: 'lg',
                        backdrop: 'static'
                    });

                    /**
                     * Configuring Callbacks
                     */
                    if (mediabrowserConfig.get('modalOpened') !== false) {
                        var fn = window[mediabrowserConfig.get('modalOpened')];
                        var data = {type: 'modalopened', data: []};
                        if (typeof fn === 'function') {
                            fn(data);
                        }
                    }

                    mediabrowserConfig.set('callbackWrapper', function (e) {
                        scope.tabs = scope.dataTabs;

                        if (mediabrowserConfig.get('modalClosed') !== false) {
                            var fn = window[mediabrowserConfig.get('modalClosed')];
                            if (typeof fn === 'function') {
                                fn(e);
                            }
                        }
                    });

                });
            }
        };
    }]);
}(window.MediaBrowserjQuery);