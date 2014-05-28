jQuery(document).ready(function() {
    // drag modal window
    jQuery(".media-browser-modal").draggable({
        handle: ".modal-header"
    });


    // drag and drop overlay
    jQuery(".mediaBrowserMain").bind('dragover', dragover);
    jQuery(".mediaBrowserMain").bind('drop', drop);
    jQuery('.media-browser-drag-overlay').bind('dragleave', dragleave);
    var tid;
    function dragover(event) {
        clearTimeout(tid);
        event.stopPropagation();
        event.preventDefault();
        jQuery('.media-browser-drag-overlay').show();

    }

    function dragleave(event) {
        tid = setTimeout(function() {
            event.stopPropagation();
            jQuery('.media-browser-drag-overlay').hide();
        }, 300);
    }

    function drop(event) {
        readfiles(event.originalEvent.dataTransfer.files);
        event.stopPropagation();
        event.preventDefault();
        jQuery('.media-browser-drag-overlay').hide();
    }


});
/* MEDIABROWSER ANGULARJS */
var mediaBrowserApp = angular.module('contrexxApp', ['ngRoute', 'plupload.module']);


mediaBrowserApp.config(['$routeProvider', '$locationProvider', function($routeProvider) {
        $routeProvider.
                when('/uploader', {templateUrl: '/trunk/core_modules/MediaBrowser/View/Template/_Uploader.html', controller: 'UploaderCtrl'}).
                when('/sitestructure', {templateUrl: '/trunk/core_modules/MediaBrowser/View/Template/_Sitestructure.html', controller: 'SitestructureCtrl'}).
                when('/filebrowser', {templateUrl: '/trunk/core_modules/MediaBrowser/View/Template/_FileBrowser.html', controller: 'MediaBrowserListCtrl'}).
                /*when('/uploader', {templateUrl: '../Template/_Uploader.html', controller: 'UploaderCtrl'}).
                 when('/sitestructure', {templateUrl: '../Template/_Sitestructure.html', controller: 'SitestructureCtrl'}).
                 when('/filebrowser', {templateUrl: '../Template/_FileBrowser.html', controller: 'MediaBrowserListCtrl'}).*/
                otherwise({redirectTo: '/uploader'});
        //$locationProvider.html5Mode(false);
    }]);
/* CONTROLLERS */

mediaBrowserApp.controller('MainCtrl', ['$scope', '$rootScope', '$location', '$http',
    function($scope, $rootScope, $location, $http) {

        // configuration 
        $rootScope.configuration = {selectmultiple: false};


        // get files by json | todo: outsource in service & get everything in one json
        $http.get('index.php?cmd=jsondata&object=MediaBrowser&act=getFiles&csrf=' + cx.variables.get('csrf')).success(function(jsonadapter) {
            jQuery(".loadingPlatform").hide();
            jQuery(".filelist").show();
            //$rootScope.path = [{name: 'images', path: 'images', standard: true}, {name: 'content', path: 'content', standard: true}]
            $rootScope.path = [{name: 'Dateien', path: 'files', standard: true}]

            $rootScope.dataFiles = jsonadapter.data;
            $rootScope.files = $rootScope.dataFiles;
        });

        // get sites by json
        $http.get('index.php?cmd=jsondata&object=MediaBrowser&act=getSites&csrf=' + cx.variables.get('csrf')).success(function(jsonadapter) {
            $rootScope.dataSites = jsonadapter.data;
            $rootScope.sites = $rootScope.dataSites;
        });

        // get sources by json
        $http.get('index.php?cmd=jsondata&object=MediaBrowser&act=getSources&csrf=' + cx.variables.get('csrf')).success(function(jsonadapter) {

            $rootScope.dataSources = jsonadapter.data;
            $rootScope.sources = $rootScope.dataSources;

            $scope.selectedSource = $rootScope.sources[0];
        });

        $rootScope.dataTabs = [
            {link: '#/uploader', label: 'Hochladen', icon: 'icon-upload'},
            {link: '#/filebrowser', label: 'Datei wählen', icon: 'icon-folder'},
            {link: '#/sitestructure', label: 'Seitenstruktur', icon: 'icon-sitestructure'}
        ];

        $rootScope.tabs = $rootScope.dataTabs;

        $scope.selectedTab = $rootScope.tabs[0];
        $scope.setSelectedTab = function(tab) {
            $scope.selectedTab = tab;
            $rootScope.changeLocation(tab.link);
        }

        $scope.tabClass = function(tab) {
            if ($scope.selectedTab === tab) {
                return "active-tab";
            } else {
                return "not-active-tab";
            }
        }

        $scope.updateSource = function() {
            $rootScope.path = [{name: "" + $scope.selectedSource.name, path: $scope.selectedSource.value, standard: true}];
            jQuery(".loadingPlatform").show();
            jQuery(".filelist").hide();
            $http.get('index.php?cmd=jsondata&object=MediaBrowser&mediatype=' + $scope.selectedSource.value + '&act=getFiles&csrf=' + cx.variables.get('csrf')).success(function(jsonadapter) {
                jQuery(".loadingPlatform").hide();
                jQuery(".filelist").show();
                $rootScope.dataFiles = jsonadapter.data;
                $rootScope.files = $rootScope.dataFiles;
            });

        };

        $rootScope.changeLocation = function(url, forceReload) {
            $scope = $scope || angular.element(document).scope();
            if (forceReload || $scope.$$phase) {
                window.location = url;
            }
            else {
                $location.path(url);
                $scope.$apply();
            }
        };

        $rootScope.go = function(path) {
            $rootScope.changeLocation(path, true);

            $rootScope.tabs.forEach(function(tab) {
                if (tab.link === path) {
                    $scope.selectedTab = tab;
                }
            });
        };


    }]);


mediaBrowserApp.controller('UploaderCtrl', ['$scope', '$rootScope', '$http',
    function($scope, $rootScope, $http) {


        // PLUPLOADER INTEGRATION 
        $scope.uploader = new plupload.Uploader({
            runtimes: 'html5,flash,silverlight,html4',
            browse_button: 'selectFileFromComputer', // you can pass in id...
            container: 'mediaBrowserMain', // ... or DOM Element itself
            drop_element: "mediaBrowserMain",
            url: '../?csrf=' + cx.variables.get('csrf') + '&cmd=jsondata&object=Uploader&act=test',
            flash_swf_url: '/lib/plupload/js/Moxie.swf',
            silverlight_xap_url: '/lib/plupload/js/Moxie.xap',
            filters: {
                max_file_size: '10cxMb',
                mime_types: [
                    {title: "Image files", extensions: "jpg,gif,png"},
                    {title: "Zip files", extensions: "zip"}
                ]
            },
            init: {
                PostInit: function() {
                    /*document.getElementById('uploadPlatform').innerHTML = '';
                     
                     document.getElementById('uploadfiles').onclick = function() {
                     uploader.start();
                     return false;
                     };*/
                },
                FilesAdded: function(up, files) {
                    setTimeout(function() {
                        up.start();
                    }, 100);
                    jQuery('.uploadStart').hide();
                    jQuery('.uploadFilesAdded').show();
                    /*plupload.each(files, function(file) {
                     jQuery(".uploadFiles").append('<li id="' + file.id + '">' + file.name + '<b></b></li>');
                     
                     });*/
                },
                UploadProgress: function(up, file) {
                    jQuery(".uploadFiles b").html(file.percent);
                    console.log(file.percent);
                    var $bar = $('.progress-bar');
                    $bar.width(file.percent + '%');
                    $bar.text(file.percent + "%");
                },
                Error: function(up, err) {
                    console.log("nError #" + err.code + ": " + err.message)
                }
            }
        });
        $scope.uploader.init();
    }]);

mediaBrowserApp.controller('MediaBrowserListCtrl', ['$scope', '$rootScope', '$http',
    function($scope, $rootScope, $http) {

        // tmp but necessary
        $scope.lastActiveFile = {};
        // __construct

        $scope.extendPath = function(dirName) {
            // $rootScope.files = $rootScope.dataFiles[$rootScope.path[0]];
            if (Array.isArray($rootScope.path)) {
                $rootScope.path.push({name: dirName, path: dirName, standard: false});
            }

            $scope.refreshBrowser();
        };
        $scope.shrinkPath = function(countDirs) {
            if (Array.isArray($rootScope.path)) {

                for (var i = 0; i < countDirs; i++) {
                    if ($rootScope.path[$rootScope.path.length - 1].standard === false)
                        $rootScope.path = $rootScope.path.slice(0, -1);
                }
            }
            $scope.refreshBrowser();
        };
        $scope.getPathString = function() {
            var returnValue = '/';
            $rootScope.path.forEach(function(pathpart) {
                returnValue += pathpart.path + '/';
            });
            return returnValue;
        }


        $scope.refreshBrowser = function() {
            $rootScope.files = $rootScope.dataFiles;
            $rootScope.path.forEach(function(pathpart) {
                if (!pathpart.standard) {
                    $rootScope.files = $rootScope.files[pathpart.path];
                }
            });
        };
        /* CLICK EVENTS */
        $scope.clickFile = function(thisDir, index) {
            if (thisDir.datainfo.extension === 'Dir') {
                $scope.extendPath(thisDir.datainfo.name);
            }
            else {
                if (thisDir.datainfo.active === true) {
                    thisDir.datainfo.active = false;
                }
                else {
                    if (!$scope.configuration.selectmultiple) {
                        console.log('ok');
                        if (!jQuery.isEmptyObject($scope.lastActiveFile))
                            $scope.lastActiveFile.datainfo.active = false;
                        $scope.lastActiveFile = thisDir;
                    }
                    thisDir.datainfo.active = true;
                }
            }
        }

        $scope.clickPath = function(index) {

            shrinkby = $rootScope.path.length - index - 1;
            if (shrinkby > 0)
                $scope.shrinkPath(shrinkby);
        };
    }]);

mediaBrowserApp.controller('SitestructureCtrl', ['$scope', '$rootScope', '$http',
    function($scope, $rootScope, $http) {
        // todo
    }]);



/* DIRECTIVES */
/* preview function */
mediaBrowserApp.directive('previewImage', function() {
    return {
        restrict: 'A',
        link: function(scope, el, attrs) {
            if (attrs.previewImage !== 'none') {
                jQuery.ajax({
                    url: attrs.previewImage,
                    type: 'HEAD',
                    success: function()
                    {
                        $(el).popover({
                            trigger: 'hover',
                            html: true,
                            content: '<img src="' + attrs.previewImage + '" />',
                            placement: 'right'
                        });
                    }
                });
            }
        }
    };
});



/* button to modal */
mediaBrowserApp.directive('cxMb', function() {
    return {
        restrict: 'A', // only work with elements including the attribute cxMb
        link: function(scope, el, attrs) {
            jQuery(el).click(function() {
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

                    newTabs = [];

                    newTabNames.forEach(function(newTabName) {
                        scope.dataTabs.forEach(function(tab) {
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
                        jQuery(".mediaBrowserMain").addClass('no-nav');
                    }
                } else {
                    jQuery(".mediaBrowserMain").removeClass('no-nav');
                    scope.tabs = scope.dataTabs;
                }


                jQuery(".media-browser-modal").modal("show");

                if (attrs.cxMbCbJsModalopened !== false) {
                    var fn = window[attrs.cxMbCbJsModalopened];
                    var data = 'modalopened';
                    if (typeof fn === 'function')
                        fn(data);
                }


                jQuery('.media-browser-modal').on('hidden.bs.modal', function(e) {
                    scope.tabs = scope.dataTabs;
                    scope.configuration.selectmultiple = false;

                    if (attrs.cxMbCbJsModalclosed !== false) {
                        var fn = window[attrs.cxMbCbJsModalclosed];
                        var data = 'modalclosed';
                        if (typeof fn === 'function')
                            fn(data);
                    }
                });
            });
        }
    };
});
