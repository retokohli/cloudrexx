// TODO: Properly inject bootbox, pluploader into angular
// TODO: Split single Controllers, Services, etc. into individual files

/* global cx, bootbox, plupload, mediaBrowserApp */
cx.ready(function() {
  // Note: Uses jQuery 2.0.3
  var jQuery = cx.variables.get('jquery', 'mediabrowser');
  angular.module('plupload.module', []).config(['$httpProvider', function($httpProvider) {
      $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
      $httpProvider.defaults.headers.common["Check-CSRF"] = 'false';
    }]);
  var unique_datatabs = 0; // Catch and avoid multiple calls
  angular.module('MediaBrowser')
    .config(['$provide', function($provide) {
        $provide.decorator('$browser', ['$delegate', function($delegate) {
            $delegate.onUrlChange = function(a) {
            };
            $delegate.history = false;
            $delegate.url = function() {
              return "";
            };
            return $delegate;
          }]);
      }])
    .config(['$httpProvider', function($httpProvider) {
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        $httpProvider.defaults.headers.common["Check-CSRF"] = 'false';
      }])
    .config(['$compileProvider', function($compileProvider) {
        $compileProvider.debugInfoEnabled(false);
      }])
    .config(["dataTabsProvider", function(dataTabsProvider) {
        // TODO: Why is this called four times!?
        if (unique_datatabs++) {
          return;
        }
        dataTabsProvider
          .add({
            label: cx.variables.get('TXT_FILEBROWSER_UPLOADER', 'mediabrowser'),
            icon: 'icon-upload',
            controller: 'UploaderCtrl',
            name: 'uploader',
            templateUrl: cx.variables.get('basePath', 'contrexx') + 'core_modules/MediaBrowser/View/Template/Uploader.html'
          })
          .add({
            label: cx.variables.get('TXT_FILEBROWSER_FILEBROWSER', 'mediabrowser'),
            icon: 'icon-folder',
            controller: 'MediaBrowserListCtrl',
            name: 'filebrowser',
            templateUrl: cx.variables.get('basePath', 'contrexx') + 'core_modules/MediaBrowser/View/Template/FileBrowser.html'
          })
          .add({
            label: cx.variables.get('TXT_FILEBROWSER_SITESTRUCTURE', 'mediabrowser'),
            icon: 'icon-sitestructure',
            controller: 'SitestructureCtrl',
            name: 'sitestructure',
            templateUrl: cx.variables.get('basePath', 'contrexx') + 'core_modules/MediaBrowser/View/Template/Sitestructure.html'
          });
      }])
    .controller('MainCtrl', [
      '$scope', '$modalInstance', '$timeout', '$q',
      'mediabrowserConfig', 'mediabrowserFiles', 'dataTabs',
      function($scope, $modalInstance, $timeout, $q,
        mediabrowserConfig, mediabrowserFiles, dataTabs) {
        $scope.sourcesLoaded = $q.defer();
        $scope.searchString = '';
        $scope.sorting = 'cleansize';
        $scope.reverse = false;
        $scope.path = [{
            name: cx.variables.get('TXT_FILEBROWSER_FILES', 'mediabrowser'),
            path: 'files',
            standard: true
          }];
        $scope.go = go;
        $scope.ok = ok;
        $scope.cancel = cancel;
        $scope.closeModal = closeModal;
        $scope.loadSources = loadSources;
        $scope.updateSource = updateSource;
        $scope.refreshBrowser = refreshBrowser;
        $scope.getPathAsString = getPathAsString;
        $scope.extendPath = extendPath;
        $scope.shrinkPath = shrinkPath;
        $scope.setFiles = setFiles;

// NOTE: To add a "tab" here, you must also add its name to at least one
// of the "cxMbViews" lists. See MediaBrowserCkeditorPlugin.js
// TODO: Find a better way of controlling the view than "cxMbViews".
        $scope.dataTabs = dataTabs.get();
        $scope.tabs = $scope.dataTabs;
        // cx-mb-views
// TODO: Clean this mess: Implement a single point of entry to add "tabs"
        if (mediabrowserConfig.get('views')
          && mediabrowserConfig.get('views') !== 'all') {
          var isStartviewInViews = false;
          var newTabNames = mediabrowserConfig.get('views');
          if (newTabNames.indexOf("filebrowser") !== -1
            && newTabNames.indexOf("uploader") === -1) {
            newTabNames.push("uploader");
          }
          if (newTabNames.indexOf("filebrowser") === -1
            && newTabNames.indexOf("uploader") !== -1) {
            newTabNames.push("filebrowser");
          }
          var newTabs = [];
          var tabStartViewName;
          var tabName;
          newTabNames.forEach(function(newTabName) {
// TODO: Flush historic names
            tabName = (newTabName === 'filebrowser')
              ? 'MediaBrowserList' : newTabName;
            tabStartViewName =
              tabName.charAt(0).toUpperCase() + tabName.slice(1) + 'Ctrl';
            $scope.dataTabs.forEach(function(tab) {
              if (tab.name === newTabName) {
                if (tabStartViewName === mediabrowserConfig.get('startView')) {
                  isStartviewInViews = true;
                }
                newTabs.push(tab);
                return false;
              }
            });
          });

// TODO: Obsolete and remove this: Allow tabs added by extensions to show up.
// If the mess above is cleaned up, and a single proper way for registering
// views is implemented, this should no longer be necessary:
          $scope.dataTabs.forEach(function(tab) {
            if (tab.name !== "uploader"
              && tab.name !== "filebrowser"
              && tab.name !== "sitestructure") {
              newTabs.push(tab);
            }
          });
          $scope.tabs = newTabs;
          if (isStartviewInViews) {
            $scope.go(mediabrowserConfig.get('startView'));
          } else {
            $scope.go($scope.tabs[0].name);
          }
        }
        loadSources();

        function go(controllerName) {
          $scope.activeController = controllerName;
        }
        function ok() {
          $modalInstance.close();
        }
        function cancel() {
          $scope.closeModal();
        }
        function closeModal() {
          $modalInstance.dismiss('cancel');
          var fn = mediabrowserConfig.get('callbackWrapper');
          if (typeof fn === 'function') {
            fn({type: 'close', data: []});
            $scope.selectedFiles = [];
          }
        }
        // Used by Upload, Filelist -> Move to a service?
        function getPathAsString() {
          var pathstring = '';
          $scope.path.forEach(function(path) {
            pathstring += path.path + '/';
          });
          return pathstring;
        }
        // This should be called from the FileBrowser Controller,
        // the model is part of the main window navigation, however.
        // -> Move to a Service or FileBrowser?
        function loadSources() {
          // Mind that get() returns a Promise
          return mediabrowserFiles.get('getSources').then(
            function(data) {
              if (mediabrowserConfig.get('startMedia')) {
                data.forEach(function(source) {
                  if (source.value === mediabrowserConfig.get('startMedia')) {
                    $scope.selectedSource = source;
                    return false;
                  }
                });
              } else {
                $scope.selectedSource = data[0];
              }
              $scope.path[0].path = $scope.selectedSource.value;
              if (mediabrowserConfig.get('mediatypes') !== 'all') {
                var i = data.length;
                while (i--) {
                  if (!(mediabrowserConfig.get('mediatypes').indexOf(data[i].value) > -1)) {
                    data.splice(i, 1);
                  }
                }
              }
              $scope.sources = data;
              $scope.sourcesLoaded.resolve();
            }, function(reason) {
            console.error(reason);
            bootbox.dialog({
              className: "media-browser-modal-window",
              title: cx.variables.get('TXT_FILEBROWSER_ERROR_HAS_HAPPEND', 'mediabrowser'),
              message: reason
            });
          }
          );
        }
        // Triggered by both main and FileBrowser
        // -> Move to a Service or FileBrowser?
        function updateSource() {
          var oldpath = $scope.path;
          if (mediabrowserConfig.isset('lastPath')) {
            oldpath = mediabrowserConfig.get('lastPath');
          } else {
            mediabrowserConfig.set('lastPath', oldpath);
          }
          var oldSource = $scope.path[0].path;
          $scope.sourcesLoaded.promise.then(function() {
            $scope.path = [
              {name: "" + $scope.selectedSource.name, path: $scope.selectedSource.value, standard: true}
            ];
            $scope.files = null;
            return mediabrowserFiles.getByMediaType($scope.selectedSource.value).then(
              function(data) {
                $scope.dataFiles = data;
                $scope.files = data;
                $timeout(function() {
                  if (oldSource === $scope.selectedSource.value) {
                    for (var i in oldpath) {
                      if (i > 0) {
                        $scope.extendPath(oldpath[i].path);
                      }
                    }
                  }
                  $scope.$apply();
                  jQuery(".filelist").fadeIn();
                });
              }
            );
          });
        }
        // Called by main and FileBrowser
        function refreshBrowser() {
          mediabrowserConfig.set('lastPath', $scope.path);
          var files = $scope.dataFiles;
          $scope.selectedFiles = [];
//          $scope.setFiles($scope.dataFiles);
          $scope.path.forEach(function(pathpart) {
            if (!pathpart.standard) {
              files = files[pathpart.path];
            }
          });
          $scope.setFiles(files);
        }
        /**
         * Move up in the directory structure to the specified directory
         *
         * Called in main, FileBrowser
         * @param dirName
         */
        function extendPath(dirName) {
          if (Array.isArray($scope.path)) {
            $scope.path.push({name: dirName, path: dirName, standard: false});
          }
          $scope.searchString = '';
          $scope.refreshBrowser();
        }
        /**
         * Move down in the directory structure
         *
         * Called by FileBrowser only -> Move there?
         * @param countDirs
         */
        function shrinkPath(countDirs) {
          if (Array.isArray($scope.path)) {
            for (var i = 0; i < countDirs; i++) {
              if ($scope.path[$scope.path.length - 1].standard === false)
                $scope.path = $scope.path.slice(0, -1);
            }
          }
          $scope.refreshBrowser();
        }
        // Called by main only
        function setFiles(files) {
          $scope.files = files;
        }
      }])
    .controller('UploaderCtrl', [
      '$scope',
      function($scope) {
        $scope.uploaderData = {
          filesToUpload: []
        };
        $scope.finishedUpload = false;
        $scope.uploadPending = false;
        $scope.showUploadedHint = false;
        $scope.uploader = new plupload.Uploader({
          runtimes: 'html5,flash,silverlight,html4',
          browse_button: 'selectFileFromComputer',
          container: 'uploader',
          drop_element: "uploader",
          url: '?csrf=' + cx.variables.get('csrf') + '&cmd=jsondata&object=Uploader&act=upload',
          flash_swf_url: cx.variables.get('basePath', 'contrexx') + 'lib/plupload/js/Moxie.swf',
          silverlight_xap_url: cx.variables.get('basePath', 'contrexx') + 'lib/plupload/js/Moxie.xap',
          chunk_size: cx.variables.get('chunk_size', 'mediabrowser'),
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Check-CSRF': 'false'
          },
          multipart_params: {
            "path": ''
          },
          init: {
            Init: function() {
              jQuery('#selectFileFromComputer').prop("disabled", false);
            },
            FilesAdded: function(up, files) {
              if ($scope.finishedUpload) {
                $scope.uploaderData.filesToUpload = [];
                $scope.finishedUpload = false;
              }
              for (var file in files) {
                if (files.hasOwnProperty(file)) {
                  $scope.uploaderData.filesToUpload.push(files[file]);
                }
              }
              $scope.uploader.settings.multipart_params.path = $scope.getPathAsString();
              $scope.$digest();
            },
            UploadProgress: function() {
              $scope.$digest();
            },
            UploadComplete: function() {
              $scope.finishedUpload = true;
              $scope.uploadPending = false;
              $scope.showUploadedHint = true;
              $scope.$digest();
              $scope.updateSource();
            },
            Error: function(up, err) {
              var mediaUploaderListCtrl = jQuery('.mediaUploaderListCtrl');
              mediaUploaderListCtrl.find('.uploadPlatform')
                .addClass('fileError');
              mediaUploaderListCtrl.find('.uploadPlatform .error')
                .html(cx.variables.get(
                  'TXT_CORE_MODULE_UPLOADER_ERROR_' + /[0-9]+/.exec(err.code),
                  'mediabrowser'));
              setTimeout(function() {
                mediaUploaderListCtrl.find(' .uploadPlatform')
                  .removeClass('fileError');
              }, 3000);
              up.refresh(); // Reposition Flash/Silverlight
            }
          }
        });
        $scope.uploader.init();
        jQuery(".uploadPlatform").mouseenter(function() {
          $scope.uploader.refresh();
        });
        $scope.startUpload = function() {
          $scope.uploader.start();
          $scope.uploadPending = true;
          jQuery('.uploadFilesAdded').show();
        };
        $scope.closeUploadedHint = function() {
          $scope.showUploadedHint = false;
        };
        $scope.removeFile = function(file) {
          jQuery.each($scope.uploaderData.filesToUpload, function(i) {
            if ($scope.uploaderData.filesToUpload[i] === file) {
              $scope.uploaderData.filesToUpload.splice(i, 1);
              $scope.uploader.removeFile(file);
              return false;
            }
          });
        };
      }])
    .controller('MediaBrowserListCtrl', [
      '$scope', '$http', 'mediabrowserConfig',
      function($scope, $http, mediabrowserConfig) {
        $scope.lastActiveFile = {};
        $scope.noFileSelected = true;
        $scope.selectedFiles = [];
        $scope.searchConfig = {
          isRegex: false,
          string: ""
        };
        $scope.length = length;
        $scope.changeSorting = changeSorting;
        $scope.getPathString = getPathString;
        $scope.escapeString = escapeString;
        $scope.renameFile = renameFile;
        $scope.removeFile = removeFile;
        $scope.clickPath = clickPath;
        $scope.createFolder = createFolder;
        $scope.choosePictures = choosePictures;
        $scope.clickFile = clickFile;

        $scope.sourcesLoaded.promise.then(function() {
          if (!$scope.files) {
            $scope.updateSource();
          }
        });

        function length(obj) {
// TODO: Fix length calculation
// For directories, the size calculated is plain wrong,
// as the "datainfo" property is counted as well.
// Why not just subtract 1 (or rather 2) from the total object length?
          var size = 0, key;
          for (key in obj) {
            // Angularjs
            if (key !== '$$hashKey') {
              if (obj.hasOwnProperty(key)) size++;
            }
          }
          return size;
        }
        function changeSorting(newSorting) {
          if (newSorting === $scope.sorting) {
            $scope.reverse = !$scope.reverse;
          } else {
            $scope.sorting = newSorting;
            $scope.reverse = false;
          }
        }
        /**
         * Get the full path string
         * @returns {string}
         */
        function getPathString() {
          var returnValue = '/';
          $scope.path.forEach(function(pathpart) {
            returnValue += pathpart.path + '/';
          });
          return returnValue;
        }
        function escapeString(string) {
// TODO: What about characters 0x00 thru 0x1f, 0x7f thru 0x9f,
// and 0x999a thru 0xffff?
          return string.replace(/[\u00A0-\u9999<>\&]/gi, function(i) {
            return '&#' + i.charCodeAt(0) + ';';
          });
        }
        function renameFile(file) {
          var splitFileName = [];
          splitFileName[0] = file.datainfo.name;
          if (file.datainfo.extension !== 'Dir') {
            splitFileName = file.datainfo.name.split('.');
          }
          var fileExtension = '';
          var fileName = file.datainfo.name;
          if (splitFileName.length !== 1) {
            fileExtension = splitFileName.pop();
            fileName = splitFileName.join('.');
          }
          var renameForm = '<div id="mediabrowser-renamefile">' +
            '<div class="file-name"><input type="text" class="form-control" value="' + $scope.escapeString(fileName) + '"/></div>' +
            '<div class="file-dot">.</div>' +
            '<div class="file-extension"><input type="text" class="form-control" value="' + fileExtension + '" disabled/></div>  </div>';
          var renameDialog = bootbox.dialog({
            className: "media-browser-modal-window",
            title: cx.variables.get('TXT_FILEBROWSER_FILE_RENAME', 'mediabrowser'),
            message: renameForm,
            buttons: {
              danger: {
                label: cx.variables.get('TXT_FILEBROWSER_CANCEL', 'mediabrowser'),
                className: "btn-danger",
                callback: function() {
                }
              },
              success: {
                label: cx.variables.get('TXT_FILEBROWSER_FILE_RENAME', 'mediabrowser'),
                className: "btn-success",
                callback: function() {
                  var newName = jQuery('#mediabrowser-renamefile').find('.file-name input').val();
                  if (newName === null) {
                  } else {
                    $http({
                      method: 'POST',
                      url: 'index.php?cmd=jsondata&object=MediaBrowser&act=renameFile&path=' + encodeURI($scope.getPathAsString()) + '&csrf=' + cx.variables.get('csrf'),
                      data: jQuery.param({
                        oldName: file.datainfo.name,
                        newName: newName
                      }),
                      headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                      }
                    }).success(function(jsonadapter) {
                      if (!jsonadapter.message) {
                        bootbox.alert({
                          className: "media-browser-modal-window",
                          title: cx.variables.get('TXT_FILEBROWSER_ERROR_HAS_HAPPEND', 'mediabrowser')
                        });
                      }
                      $scope.updateSource();
                    }).error(function() {
                      bootbox.alert({
                        className: "media-browser-modal-window",
                        title: cx.variables.get('TXT_FILEBROWSER_ERROR_HAS_HAPPEND', 'mediabrowser')
                      });
                    });
                  }
                }
              }
            }
          });
          renameDialog.bind('shown.bs.modal', function() {
            renameDialog.find("input").select().keypress(function(e) {
              if (e.which === 13) {
                e.preventDefault();
                jQuery(this).blur();
                jQuery(renameDialog).find('.btn-success').focus().click();
              }
            });
          });
        }
        function removeFile(file) {
          var removeDialog = bootbox.dialog({
            className: "media-browser-modal-window",
            title: cx.variables.get('TXT_FILEBROWSER_FILE_REMOVE_FILE', 'mediabrowser'),
            message: cx.variables.get('TXT_FILEBROWSER_ARE_YOU_SURE', 'mediabrowser'),
            buttons: {
              danger: {
                label: cx.variables.get('TXT_FILEBROWSER_CANCEL', 'mediabrowser'),
                className: "btn-default",
                callback: function() {
                }
              },
              success: {
                label: cx.variables.get('TXT_FILEBROWSER_FILE_REMOVE', 'mediabrowser'),
                className: "btn-danger",
                callback: function() {
                  $http({
                    method: 'POST',
                    url: 'index.php?cmd=jsondata&object=MediaBrowser&act=removeFile&path=' + encodeURI($scope.getPathAsString()) + '&csrf=' + cx.variables.get('csrf'),
                    data: jQuery.param({
                      file: file
                    }),
                    headers: {
                      'Content-Type': 'application/x-www-form-urlencoded',
                      'X-Requested-With': 'XMLHttpRequest'
                    }
                  }).success(function() {
                    $scope.updateSource();
                  }).error(function() {
                    bootbox.alert({
                      className: "media-browser-modal-window",
                      title: cx.variables.get('TXT_FILEBROWSER_ERROR_HAS_HAPPEND', 'mediabrowser')
                    });
                  });
                  $scope.refreshBrowser();
                }
              }
            }
          });
          removeDialog.bind('shown.bs.modal', function() {
            removeDialog.find(".btn-danger").focus();
          });
        }
        function clickPath(index) {
          var shrinkby = $scope.path.length - index - 1;
          if (shrinkby > 0) {
            $scope.shrinkPath(shrinkby);
          }
        }
        function createFolder() {
          bootbox.prompt(
            {
              className: "media-browser-modal-window",
              title: cx.variables.get('TXT_FILEBROWSER_DIRECTORY_NAME', 'mediabrowser'),
              callback: function(dirName) {
                if (dirName === null) {
                } else {
                  $http({
                    method: 'POST',
                    url: 'cadmin/index.php?cmd=jsondata&object=MediaBrowser&act=createDir&path=' + encodeURI($scope.getPathAsString()) + '&csrf=' + cx.variables.get('csrf'),
                    data: jQuery.param({dir: dirName}),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                  }).success(function() {
                    $scope.updateSource();
                  }).error(function() {
                    bootbox.alert({
                      className: "media-browser-modal-window",
                      title: cx.variables.get('TXT_FILEBROWSER_ERROR_HAS_HAPPEND', 'mediabrowser')
                    });
                  });
                }
              }
            }
          );
        }
        function choosePictures() {
          var fn = mediabrowserConfig.get('callbackWrapper');
          $scope.closeModal();
          for (var i = 0; i < $scope.selectedFiles.length; i++) {
            var item = $scope.selectedFiles[i];
            item.datainfo.active = false;
          }
          if (typeof fn === 'function') {
            fn({type: 'file', data: $scope.selectedFiles});
            $scope.selectedFiles = [];
          }
        }
        /**
         * Handle click and dblclick events on file entries
         * @param   {array}     file        The clicked file
         * @param   {boolean}   selectFile  True on dblclick
         */
        function clickFile(file, selectFile) {
          if (file.datainfo.extension === 'Dir') {
            $scope.extendPath(file.datainfo.name);
          } else {
            if (file.datainfo.active === true && !selectFile) {
              for (var i = 0; i < $scope.selectedFiles.length; i++) {
                if ($scope.selectedFiles[i].datainfo.filepath === file.datainfo.filepath) {
                  $scope.selectedFiles.splice(i, 1);
                }
              }
              file.datainfo.active = false;
            } else {
              file.datainfo.active = true;
              if (selectFile) {
                $scope.selectedFiles.push(file);
                file.datainfo.active = false;
                var fn = mediabrowserConfig.get('callbackWrapper');
                if (typeof fn === 'function') {
                  fn({type: 'file', data: [file]});
                }
                if (!jQuery.isEmptyObject($scope.lastActiveFile)) {
                  $scope.lastActiveFile.datainfo.active = false;
                }
                $scope.closeModal();
              } else if (!mediabrowserConfig.get('multipleSelect')) {
                $scope.selectedFiles = [];
                if (!jQuery.isEmptyObject($scope.lastActiveFile)) {
                  $scope.lastActiveFile.datainfo.active = false;
                }
                $scope.lastActiveFile = file;
                $scope.selectedFiles.push(file);
              } else if (mediabrowserConfig.get('multipleSelect')) {
                $scope.selectedFiles.push(file);
              }
            }
            $scope.noFileSelected = $scope.selectedFiles.length === 0;
          }
        }
      }])
    .controller('SitestructureCtrl', [
      '$scope', 'mediabrowserConfig', 'mediabrowserFiles',
      function($scope, mediabrowserConfig, mediabrowserFiles) {
        $scope.activeLanguage = cx.variables.get('language', 'mediabrowser');
        $scope.activeLanguages = cx.variables.get('languages', 'mediabrowser');
        $scope.clickPage = clickPage;
        $scope.isActive = isActive;
        $scope.setLang = setLang;
        if (!$scope.pagesLoaded) {
          loadPages();
        }

        function loadPages() {
          // Mind that get() returns a Promise
          return mediabrowserFiles.get('getSites').then(
            function(data) {
              $scope.sites = data;
              $scope.pagesLoaded = true;
            }
          );
        }
        function clickPage(site) {
          var fn = mediabrowserConfig.get('callbackWrapper');
          if (typeof fn === 'function') {
            fn({type: 'page', data: [site]});
          }
          $scope.closeModal();
        }
        function isActive(lang) {
          return $scope.activeLanguage === lang;
        }
        function setLang(lang) {
          $scope.activeLanguage = lang;
        }
      }
    ])
    .directive('previewImage', ['$http', function($http) {
        return {
          restrict: 'A',
          link: function(scope, el, attrs) {
            if (attrs.previewImage !== 'none') {
              if (attrs.hasPreviewImage === 'true') {
                jQuery(el).popover({
                  trigger: 'hover',
                  html: true,
                  content: '<img src="' + attrs.previewImage.replace(/<[^>]*>/g, '') + '"  />',
                  placement: 'right'
                });
              } else {
                $http.get('index.php?cmd=jsondata&object=MediaBrowser&act=createThumbnails&file=' + attrs.previewImage).
                  success(function(data, status, headers, config) {
                    jQuery(el).popover({
                      trigger: 'hover',
                      html: true,
                      content: '<img src="' + attrs.previewImage.replace(/<[^>]*>/g, '') + '"  />',
                      placement: 'right'
                    });
                  });
              }
            }
          }
        };
      }])
    .directive('sglclick', ['$parse', function($parse) {
        return {
          restrict: 'A',
          link: function(scope, element, attr) {
            var fn = $parse(attr['sglclick']);
            var delay = 300, clicks = 0, timer = null;
            element.on('click', function(event) {
              if (++clicks === 1) {
                timer = setTimeout(function() {
                  scope.$apply(function() {
                    fn(scope, {$event: event});
                  });
                  clicks = 0; //after action performed, reset counter
                }, delay);
              } else {
                clearTimeout(timer); //prevent single-click action
                clicks = 0; //after action performed, reset counter
              }
            });
          }
        };
      }])
    .factory('Thumbnail', function($q) {
      return {
        isImage: isImage
      };
      function isImage(src) {
        var deferred = $q.defer();
        var image = new Image();
        image.onerror = function() {
          deferred.resolve(false);
        };
        image.onload = function() {
          deferred.resolve(true);
        };
        image.src = src;
        return deferred.promise;
      }
    })
    .factory('mediabrowserFiles', function($http, $q) {
      return {
        get: get,
        getByMediaType: getByMediaType
      };
      function get(type) {
        var deferred = $q.defer();
        $http.get(cx.variables.get("cadminPath", "contrexx") +
          'index.php?cmd=jsondata&object=MediaBrowser&act=' + type +
          '&csrf=' + cx.variables.get('csrf'))
          .success(function(jsonadapter, status, headers) {
            if (jsonadapter.data instanceof Object) {
              deferred.resolve(jsonadapter.data);
            } else if (jsonadapter.match(/login_form/)) {
              deferred.reject(cx.variables.get('TXT_FILEBROWSER_LOGGED_OUT', 'mediabrowser'));
            } else {
              deferred.reject("An error occured while fetching items for " + type);
            }
          }).error(function() {
          deferred.reject("An error occured while fetching items for " + type);
        });
        return deferred.promise;
      }
      function getByMediaType(mediatype) {
        return this.get('getFiles&mediatype=' + mediatype);
      }
    })
    .factory('mediabrowserConfig', function() {
      var config = {};
      return {
        set: set,
        get: get,
        isset: isset
      };
      function set(key, value) {
        config[key] = value;
      }
      function get(key) {
        return config[key];
      }
      function isset(key) {
        return key in config;
      }
    })
    .filter('findPage', function() {
      return function(items, search, activeLanguage) {
        if (!items) {
          return [];
        }
        var filtered = [];
        var letterMatch = new RegExp(search, 'i');
        for (var i = 0; i < items.length; i++) {
          var item = items[i];
          if (letterMatch.test(item.name[activeLanguage])) {
            filtered.push(item);
          }
        }
        return filtered;
      };
    })
    .filter('orderAndSearchFiles', function() {
      return function(input, attribute, reverse, searchFile, isRegex) {
        if (!angular.isObject(input)) return input;
        var array = [];
        for (var objectKey in input) {
          if (!input[objectKey].datainfo) {
            continue;
          }
          array.push(input[objectKey]);
        }
        array.sort(function(a, b) {
          if (a.datainfo && b.datainfo) {
            a = (a['datainfo'][attribute]);
            b = (b['datainfo'][attribute]);
            if (reverse) {
              if (a < b) return -1;
              if (a > b) return 1;
            } else {
              if (a < b) return 1;
              if (a > b) return -1;
            }
          }
          return 0;
        });
        if (searchFile) {
          var searchObject;
          if (isRegex) {
            try {
              var regex = new RegExp(searchFile, 'i');
              searchObject = function(string) {
                return regex.test(string);
              };
            } catch (e) {
              return [];
            if (searchArray[key] instanceof Object) {
                resultArray = resultArray.concat(recursiveSearch(searchObject, searchArray[key], level + 1));
            }
          } else {
            var fileSearchWords = searchFile.toLowerCase().split(" ");
            searchObject = function(string) {
              for (var i = 0; i < fileSearchWords.length; i++) {
                if (string.toLowerCase().indexOf(fileSearchWords[i]) === -1) {
                  return false;
                }
              }
              return true;
            };
          }
          return recursiveSearch(searchObject, array, 0);
        }
        return array;
      };
      function recursiveSearch(searchObject, searchArray, level) {
        var resultArray = [];
        for (var key in searchArray) {
          if (key === 'datainfo') {
            continue;
          }
// TODO: Rethink the JSON data structure.
// Seeing this condition, I can't help but wonder...
          if (typeof searchArray[key]['datainfo'] !== "undefined"
            && typeof searchArray[key]['datainfo']['name'] !== "undefined"
            && searchArray[key]['datainfo']['extension'] !== 'Dir'
            && searchObject(searchArray[key]['datainfo']['name'])) {
            resultArray.push(searchArray[key]);
          }
          if (searchArray[key] instanceof Object) {
            resultArray = resultArray.concat(
              recursiveSearch(searchObject, searchArray[key], level + 1));
          }
        }
        return resultArray;
      }
    })
    .filter('translate', function() {
      return function(key) {
        return cx.variables.get(key, 'mediabrowser');
      };
    });
  jQuery('button.mediabrowser-button').each(function() {
    angular.bootstrap(jQuery(this).next('.mediaBrowserScope')[0], ['MediaBrowser']);
    var scope = angular.element(jQuery(this).next()[0]).injector();
    if (!scope) {
      console.warn('.mediaBrowserScope Element is missing, please generate the button only with the mediabrowser class and not by yourself!');
      return;
    }
    jQuery(this).on('click', function(event, config) {
      var mediabrowserConfig = scope.get('mediabrowserConfig');
      if (mediabrowserConfig.get('isOpen')) {
        return;
      }
      var $modal = scope.get('$modal');
      var attrs = jQuery(this).data();
      for (var i in config) {
        attrs[i] = config[i];
      }
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
      if (attrs.startPath) {
        mediabrowserConfig.set('lastPath', attrs.startPath);
      }
      if (typeof (config) !== 'undefined' && typeof (config.callback) !== 'undefined') {
        mediabrowserConfig.set('modalClosed', config.callback);
      } else {
        mediabrowserConfig.set('modalClosed', false);
        if (attrs.cxMbCbJsModalclosed) {
          mediabrowserConfig.set('modalClosed', attrs.cxMbCbJsModalclosed);
        }
      }
      mediabrowserConfig.set('isOpen', true);
      $modal.open({
        templateUrl: cx.variables.get('basePath', 'contrexx') + 'core_modules/MediaBrowser/View/Template/MediaBrowserModal.html',
        controller: 'MainCtrl',
        dialogClass: 'media-browser-modal',
        size: 'lg',
        backdrop: 'static',
        backdropClass: 'media-browser-modal-backdrop',
        windowClass: 'media-browser-modal-window'
      }).result.finally(function() {
        mediabrowserConfig.set('isOpen', false);
      });
      // Configuring Callbacks
      if (mediabrowserConfig.get('modalOpened') !== false) {
        var fn = window[mediabrowserConfig.get('modalOpened')];
        var data = {type: 'modalopened', data: []};
        if (typeof fn === 'function') {
          fn(data);
        }
      }
      mediabrowserConfig.set('callbackWrapper', function(e) {
        scope.tabs = scope.dataTabs;
        if (mediabrowserConfig.get('modalClosed') !== false) {
          if (typeof mediabrowserConfig.get('modalClosed') === 'function') {
            mediabrowserConfig.get('modalClosed')(e);
          } else {
            var windowScope = window;
            var scopeSplit = mediabrowserConfig.get('modalClosed').split('.');
            for (var i = 0; i < scopeSplit.length - 1; i++) {
              windowScope = windowScope[scopeSplit[i]];
              if (typeof scope === "undefined") return;
            }
            var fn = windowScope[scopeSplit[scopeSplit.length - 1]];
            if (typeof fn === 'function') {
              fn(e);
            }
          }
        }
      });
    });
    jQuery(this).removeAttr('disabled');
  });
});
