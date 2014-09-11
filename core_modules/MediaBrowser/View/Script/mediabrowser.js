$J(document).ready(function () {
    // drag and drop overlay
    $J("html, .mediaBrowserMain").bind('dragover', dragover);
    $J('html, .mediaBrowserMain').bind('dragleave', dragleave);

    var tid;

    function dragover(event) {
        clearTimeout(tid);
        event.stopPropagation();
        event.preventDefault();
        $J('.modal-content').addClass('modal-drag-overlay');
    }

    function dragleave(event) {
        tid = setTimeout(function () {
            event.stopPropagation();
            $J('.modal-content').removeClass('modal-drag-overlay');

        }, 300);
    }

    //function filesDropped() { } -> in angular controller mainctrl
});


/* MEDIABROWSER ANGULARJS */
var mediaBrowserApp = angular.module('contrexxApp', ['ngRoute', 'plupload.module', 'ngAnimate']);

mediaBrowserApp.config(['$routeProvider', '$locationProvider', function ($routeProvider) {
    $routeProvider.
        when('/uploader', {templateUrl: '/core_modules/MediaBrowser/View/Template/_Uploader.html', controller: 'UploaderCtrl'}).// todo adapt path
        when('/sitestructure', {templateUrl: '/core_modules/MediaBrowser/View/Template/_Sitestructure.html', controller: 'SitestructureCtrl'}).
        when('/filebrowser', {templateUrl: '/core_modules/MediaBrowser/View/Template/_FileBrowser.html', controller: 'MediaBrowserListCtrl'}).
        otherwise({redirectTo: '/uploader'});
}]);

mediaBrowserApp.filter('translate', function () {
    return function (key) {
        return cx.variables.get(key, 'mediabrowser');
    }
});

/* CONTROLLERS */
mediaBrowserApp.controller('MainCtrl', ['$scope', '$rootScope', '$location', '$http', '$filter',
    function ($scope, $rootScope, $location, $http, $filter) {
        // configuration 
        $rootScope.configuration = {selectmultiple: false};
        $rootScope.sources = [];
        $rootScope.fileCallBack = '';
        $scope.predicate = 'datainfo.name';

//        var orderBy = $filter('orderBy');
//        $scope.order = function(predicate, reverse) {
//            $scope.files = orderBy($rootScope.files, predicate, reverse);
//        };
//        $scope.order('datainfo.size',true);


        // get files by json | todo: outsource in service & get everything in one json
        $http.get('index.php?cmd=jsondata&object=MediaBrowser&act=getFiles&csrf=' + cx.variables.get('csrf')).success(function (jsonadapter) {
            $J(".loadingPlatform").hide();
            $J(".filelist").show();
            $rootScope.path = [
                {name: 'Dateien', path: 'files', standard: true}
            ];
            $rootScope.dataFiles = jsonadapter.data;
            $rootScope.files = $rootScope.dataFiles;
        });

        // get sites by json
        $http.get('index.php?cmd=jsondata&object=MediaBrowser&act=getSites&csrf=' + cx.variables.get('csrf')).success(function (jsonadapter) {
            $rootScope.dataSites = jsonadapter.data;
            $rootScope.sites = $rootScope.dataSites;
        });

        // get sources by json
        $http.get('index.php?cmd=jsondata&object=MediaBrowser&act=getSources&csrf=' + cx.variables.get('csrf')).success(function (jsonadapter) {
            $rootScope.sources = [];

            $rootScope.dataSources = jsonadapter.data;
            $rootScope.sources = $rootScope.dataSources;

            $scope.selectedSource = $rootScope.sources[0];
        });

        $rootScope.dataTabs = [
            {link: '#/uploader', label: 'Hochladen', icon: 'icon-upload'},
            {link: '#/filebrowser', label: 'Ablage', icon: 'icon-folder'},
            {link: '#/sitestructure', label: 'Seitenstruktur', icon: 'icon-sitestructure'}
        ];

        $rootScope.tabs = $rootScope.dataTabs;

        $scope.selectedTab = $rootScope.tabs[0];
        $scope.setSelectedTab = function (tab) {
            $scope.selectedTab = tab;
            $rootScope.changeLocation(tab.link);
        };

        // return active if is selected
        $scope.tabClass = function (tab) {
            if ($scope.selectedTab === tab) {
                return "active-tab";
            } else {
                return "not-active-tab";
            }
        };

        $scope.updateSource = function () {
//            $rootScope.path = [
//                {name: "" + $scope.selectedSource.name, path: $scope.selectedSource.value, standard: true}
//            ];
            console.log($rootScope.path);
            $J(".loadingPlatform").show();
            $J(".filelist").hide();
            $http.get('index.php?cmd=jsondata&object=MediaBrowser&mediatype=' + $scope.selectedSource.value + '&act=getFiles&csrf=' + cx.variables.get('csrf')).success(function (jsonadapter) {
                $J(".loadingPlatform").hide();
                $J(".filelist").show();
                $rootScope.dataFiles = jsonadapter.data;
                $rootScope.files = $rootScope.dataFiles;

            });

        };


        $rootScope.updateSource = $scope.updateSource;

        $rootScope.changeLocation = function (url, forceReload) {
            $scope = $scope || angular.element(document).scope();
            if (forceReload || $scope.$$phase) {
                window.location = url;
            }
            else {
                $location.path(url);
                $scope.$apply();
            }
        };

        $rootScope.go = function (path) {
            $rootScope.changeLocation(path, true);

            $rootScope.tabs.forEach(function (tab) {
                if (tab.link === path) {
                    $scope.selectedTab = tab;
                }
            });
        };


        $rootScope.getPathAsString = function () {
            var pathstring = '';
            $rootScope.path.forEach(function (path) {
                pathstring += path.path + '/';
            });
            return pathstring;
        };


        $rootScope.createFolder = function () {
            bootbox.prompt("Verzeichnisname:", function (dirName) {
                if (dirName === null) {

                } else {
                    $http({
                        method: 'POST',
                        url: 'index.php?cmd=jsondata&object=Uploader&act=createDir&path=' + $rootScope.getPathAsString() + '&csrf=' + cx.variables.get('csrf'),
                        data: $.param({dir: dirName}),
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                    }).success(function (jsonadapter) {
                        $rootScope.updateSource();
                        bootbox.alert(jsonadapter.message);
                    });
                }
            });

        };

    }]
);


mediaBrowserApp.controller('UploaderCtrl', ['$scope', '$rootScope', '$http',
    function ($scope, $rootScope, $http) {

        // PLUPLOADER INTEGRATION 
        $scope.uploader = new plupload.Uploader({
            runtimes: 'html5,flash,silverlight,html4',
            browse_button: 'selectFileFromComputer',
            container: 'mediaBrowserMain',
            drop_element: "mediaBrowserMain",
            url: '?csrf=' + cx.variables.get('csrf') + '&cmd=jsondata&object=Uploader&act=upload',
            flash_swf_url: '/lib/plupload/js/Moxie.swf',
            silverlight_xap_url: '/lib/plupload/js/Moxie.xap',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            filters: {
                max_file_size: '50cxMb',
                mime_types: [
                    {title: "Image files", extensions: "jpg,gif,png"},
                    {title: "Zip files", extensions: "zip"},
                    {title: "PDF files", extensions: "pdf"}
                ]
            },
            multipart_params: {
                "path": ''
            },
            init: {
                PostInit: function () {
                    /*document.getElementById('uploadPlatform').innerHTML = '';

                     document.getElementById('uploadfiles').onclick = function() {
                     uploader.start();
                     return false;
                     };*/
                },
                FilesAdded: function (up, files) {

                    $scope.uploader.settings.multipart_params.path = $rootScope.getPathAsString();
                    //alert(JSON.stringify($rootScope.path));
                    setTimeout(function () {
                        up.start();
                    }, 100);
                    $J('.uploadStart').hide();
                    $J('.uploadFilesAdded').show();
                    $J('.modal-content').removeClass('modal-drag-overlay');
                    /*plupload.each(files, function(file) {
                     $J(".uploadFiles").append('<li id="' + file.id + '">' + file.name + '<b></b></li>');

                     });*/
                },
                UploadProgress: function (up, file) {
                    console.log(file);
                    $J(".uploadFiles b").html(file.percent);
                    var $bar = $('.progress-bar');
                    $bar.width(file.percent + '%');
                    $bar.text(file.percent + "%");
                },
                UploadComplete: function () {
                    $rootScope.afterUpload();
                },
                Error: function (up, err) {
                    console.log("nError #" + err.code + ": " + err.message)
                }
            }
        });
        $scope.uploader.init();
    }]);

mediaBrowserApp.controller('MediaBrowserListCtrl', ['$scope', '$rootScope', '$http',
    function ($scope, $rootScope, $http) {

        $rootScope.inRootDirectory = true;
        $scope.lastActiveFile = {};
        $scope.noFileSelected = true;
        $scope.selectedFiles = [];
        // __construct

        $scope.extendPath = function (dirName) {
            if (Array.isArray($rootScope.path)) {
                $rootScope.path.push({name: dirName, path: dirName, standard: false});
            }
            $rootScope.inRootDirectory = ($rootScope.path.length == 1);
            $scope.refreshBrowser();
        };

        $rootScope.afterUpload = function () {
            var tmpRootPath = $rootScope.path;
            $rootScope.go("#/filebrowser");

            for (var i = 1; i < tmpRootPath.length; i++) {
                $scope.extendPath(tmpRootPath[i].path);
            }

            $rootScope.updateSource();
            $scope.inRootDirectory = ($rootScope.path.length == 1);

            $scope.refreshBrowser();

        };

        $scope.shrinkPath = function (countDirs) {
            if (Array.isArray($rootScope.path)) {

                for (var i = 0; i < countDirs; i++) {
                    if ($rootScope.path[$rootScope.path.length - 1].standard === false)
                        $rootScope.path = $rootScope.path.slice(0, -1);
                }
            }
            $rootScope.inRootDirectory = ($rootScope.path.length == 1);
            $scope.refreshBrowser();
        };

        $scope.getPathString = function () {
            var returnValue = '/';
            $rootScope.path.forEach(function (pathpart) {
                returnValue += pathpart.path + '/';
            });
            return returnValue;
        };

        $scope.refreshBrowser = function () {
            $scope.selectedFiles = [];
            $rootScope.files = $rootScope.dataFiles;
            $rootScope.path.forEach(function (pathpart) {
                if (!pathpart.standard) {
                    $rootScope.files = $rootScope.files[pathpart.path];
                }
            });
            $rootScope.inRootDirectory = ($rootScope.path.length == 1);
        };

        $rootScope.refreshBrowser = $scope.refreshBrowser;
        /* CLICK EVENTS */
        $scope.clickFile = function (file, index, selectFile) {

            if (file.datainfo.extension == 'Dir') {
                $scope.extendPath(file.datainfo.name);
            }
            else {
                if (file.datainfo.active === true) {
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
                    if (!$scope.configuration.selectmultiple && selectFile) {
                        $scope.selectedFiles = [];
                        $J('.media-browser-modal').unbind('hidden.bs.modal', $rootScope.callbackWrapper);
                        var fn = window[$rootScope.callback];
                        $J('.media-browser-modal').modal('hide');
                        if (typeof fn === 'function') {
                            fn([file]);
                        }

                        if (!$J.isEmptyObject($scope.lastActiveFile)) {
                            $scope.lastActiveFile.datainfo.active = false;
                        }
                        $scope.lastActiveFile = file;
                    }
                }

                $scope.noFileSelected = $scope.selectedFiles.length == 0;
            }
        };


        $scope.renameFile = function (file, index) {
            var splittedFileName = [];

            splittedFileName[0] = file.datainfo.name;
            if (file.datainfo.extension !== 'Dir'){
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
                                        url: 'index.php?cmd=jsondata&object=Uploader&act=renameFile&path=' + $rootScope.getPathAsString() + '&csrf=' + cx.variables.get('csrf'),
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
                                        $rootScope.updateSource();
                                    });
                                }
                            }
                        }
                    }
            });
        };

        $scope.removeFile = function (file, index) {
            bootbox.confirm("Are you sure?", function (result) {
                if (result === null) {

                } else {
                    $http({
                        method: 'POST',
                        url: 'index.php?cmd=jsondata&object=Uploader&act=removeFile&path=' + $rootScope.getPathAsString() + '&csrf=' + cx.variables.get('csrf'),
                        data: $.param({
                            file: file
                        }),
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).success(function (jsonadapter) {
                        $rootScope.updateSource();
                        bootbox.alert(jsonadapter.message);
                    });

                    $scope.refreshBrowser();
                }
            });
        };

        $scope.clickPath = function (index) {
            var shrinkby = $rootScope.path.length - index - 1;
            if (shrinkby > 0) {
                $scope.shrinkPath(shrinkby);
            }
        };

        $scope.choosePictures = function () {
            var fn = window[$rootScope.callback];
            var data = 'modalclosed';
            $J('.media-browser-modal').modal('hide');
            if (typeof fn === 'function') {
                fn($scope.selectedFiles);
            }
        };
    }
]);

mediaBrowserApp.controller('SitestructureCtrl', ['$scope', '$rootScope',
    function ($scope, $rootScope) {
        $scope.clickPage = function (site) {
            $J('.media-browser-modal').unbind('hidden.bs.modal', $rootScope.callbackWrapper);
            var fn = window[$rootScope.callback];
            $J('.media-browser-modal').modal('hide');
            if (typeof fn === 'function') {
                fn([site]);
            }

            if (!$J.isEmptyObject($scope.lastActiveFile)) {
                $scope.lastActiveFile.datainfo.active = false;
            }
            $scope.lastActiveFile = site;
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
                $J(el).popover({
                    trigger: 'hover',
                    html: true,
                    content: '<img src="' + attrs.previewImage + '"  />',
                    placement: 'right'
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
                            fn(scope, { $event: event });
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

/* button to modal */
mediaBrowserApp.directive('cxMb', function ($rootScope) {
    return {
        restrict: 'A', // only work with elements including the attribute cxMb
        link: function (scope, el, attrs) {
            $J(el).click(function () {
                // sitestructure / filebrowser / uploader

                // cx-mb-views="sitestructure,uploader"
                // cx-mb-startmediatype="gallery"
                // cx-mb-startmediatype="gallery" cx-mb-mediatypes="files, gallery"
                // cx-mb-multipleselect="true" cx-mb-views="filebrowser"

                if (!attrs.cxMbStartview) {
                    attrs.$set('cxMbStartview', 'filebrowser');
                }

                if (!attrs.cxMbViews) {
                    attrs.$set('cxMbViews', 'all');
                }

                if (!attrs.cxMbStartmediatype) {
                    attrs.$set('cxMbStartmediatype', 'files');
                }

                if (!attrs.cxMbMediatypes) {
                    attrs.$set('cxMbMediatypes', 'all');
                }

                if (!attrs.cxMbMultipleselect) {
                    attrs.$set('cxMbMultipleselect', false);
                }

                // callbacks
                if (!attrs.cxMbCbJsModalopened) {
                    attrs.$set('cxMbCbJsModalopened', false);
                }
                if (!attrs.cxMbCbJsModalclosed) {
                    attrs.$set('cxMbCbJsModalclosed', false);
                }


                // cx-mb-mulipleselect
                scope.configuration.selectmultiple = attrs.cxMbMultipleselect;


                // cx-mb-startview | need to be placed before cx-mb-views!!
                scope.$apply("go('#/" + attrs.cxMbStartview + "')");

                // cx-mb-views
                if (attrs.cxMbViews !== 'all') {
                    var isStartviewInViews = false;
                    var views = attrs.cxMbViews;
                    views = views.trim();
                    var newTabNames = views.split(",");

                    var newTabs = [];

                    newTabNames.forEach(function (newTabName) {
                        scope.dataTabs.forEach(function (tab) {
                            if (tab.link === '#/' + newTabName) {
                                if (newTabName === attrs.cxMbStartview)
                                    isStartviewInViews = true;
                                newTabs.push(tab);
                            }
                        });
                    });
                    scope.tabs = newTabs;
                    if (!isStartviewInViews)
                        scope.$apply("go('" + scope.tabs[0].link + "')");

                    if (newTabs.length === 1) {
                        $J(".mediaBrowserMain").addClass('no-nav');
                    }
                } else {
                    $J(".mediaBrowserMain").removeClass('no-nav');
                    scope.tabs = scope.dataTabs;
                }


                $J(".media-browser-modal").modal("show");

//                $rootScope.updateSource();
                if (attrs.cxMbCbJsModalopened !== false) {
                    var fn = window[attrs.cxMbCbJsModalopened];
                    var data = 'modalopened';
                    if (typeof fn === 'function') {
                        fn(data);
                    }
                }

                if (attrs.cxMbCbJsModalclosed !== false) {

                    $rootScope.callback = attrs.cxMbCbJsModalclosed;
                }
                $rootScope.callbackWrapper = function (e) {
                    scope.tabs = scope.dataTabs;
                    scope.configuration.selectmultiple = false;

                    if (attrs.cxMbCbJsModalclosed !== false) {
                        var fn = window[attrs.cxMbCbJsModalclosed];
                        var data = 'modalclosed';
                        if (typeof fn === 'function') {
                            fn(data);
                        }
                    }
                };

                $J('.media-browser-modal').on('hidden.bs.modal', $rootScope.callbackWrapper);
            });
        }
    };
});
