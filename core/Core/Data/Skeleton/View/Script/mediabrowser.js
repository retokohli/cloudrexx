jQuery(document).ready(function() {
    jQuery('.mediaBrowserNavigation ul').click(function(e) {
        e.preventDefault()
        jQuery(this).tab('show')
    });
    
    jQuery("[rel=tooltip]").tooltip({html:true});


    /* PLUPLOADER INTEGRATION */
      var uploader = new plupload.Uploader({
        runtimes: 'html5,flash,silverlight,html4',
        browse_button: 'selectFileFromComputer', // you can pass in id...
        container: 'mediaBrowserMain', // ... or DOM Element itself
        drop_element: "mediaBrowserMain",
        
        url: 'upload.php',
        flash_swf_url: 'lib/plupload/js/Moxie.swf',
        silverlight_xap_url: 'lib/plupload/js/Moxie.xap',
        filters: {
            max_file_size: '10mb',
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
                jQuery('.uploadStart').hide();
                jQuery('.uploadFilesAdded').show();
                filesCount = 1;
                plupload.each(files, function(file) {
                    jQuery(".uploadFiles").append('<li id="' + file.id + '">' + file.name + '<b></b></li>');
                    
                });
            },
            UploadProgress: function(up, file) {
                jQuery(".uploadFiles b").html(file.percent);
            },
            Error: function(up, err) {
                console.log("nError #" + err.code + ": " + err.message)
            }
        }
        
    });

    uploader.init();
});



/* MEDIABROWSER ANGULARJS */
var mediaBrowserApp = angular.module('mediaBrowserApp', []);

/* DIRECTIVES */
/* preview function */
mediaBrowserApp.directive('previewImage', function () {
    return {
        restrict: 'A',
        link: function (scope, el, attrs) {
            $(el).popover({
                trigger: 'hover',
                html: true,
                content: '<img src="'+attrs.previewImage+'" />',
                placement: attrs.previewPosition
            });
        }
    };
});




/* CONTROLLERS */
mediaBrowserApp.controller('MediaBrowserListCtrl', function($scope, $http) {

    // configuration 
    $scope.configuration = {selectmultiple: false};

    // tmp but necessary
    $scope.lastActiveFile = {};


    // __construct
    $http.get('index.php?cmd=jsondata&object=MediaBrowser&act=getFiles&csrf=' + cx.variables.get('csrf')).success(function(jsonadapter) {
        $scope.path = [{name: 'images', path: 'images', standard: true}, {name: 'content', path: 'content', standard: true}]

        $scope.dataFiles = jsonadapter.data;
        $scope.files = $scope.dataFiles;
        console.log(jsonadapter.data);
    });

    $scope.extendPath = function(dirName) {
        // $scope.files = $scope.dataFiles[$scope.path[0]];
        if (Array.isArray($scope.path)) {
            $scope.path.push({name: dirName, path: dirName, standard: false});
        }

        $scope.refreshBrowser();
    };

    $scope.shrinkPath = function(countDirs) {
        if (Array.isArray($scope.path)) {

            for (var i = 0; i < countDirs; i++) {
                if ($scope.path[$scope.path.length - 1].standard === false)
                    $scope.path = $scope.path.slice(0, -1);
            }
        }
        console.log('ShrinkPath:' + countDirs);
        $scope.refreshBrowser();
    };

    $scope.refreshBrowser = function() {
        $scope.files = $scope.dataFiles;
        $scope.path.forEach(function(pathpart) {
            if (!pathpart.standard) {
                $scope.files = $scope.files[pathpart.path];
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

        shrinkby = $scope.path.length - index - 1;
        if (shrinkby > 0)
            $scope.shrinkPath(shrinkby);
    };
});
