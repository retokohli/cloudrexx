/**
 * Topics extension for the MediaBrowser
 *
 * Add a MediaBrowser tab for the Topics, and provide an Entry service
 */
/* global cx */
cx.ready(function() {
  var unique_datatabs = 0; // Catch and avoid multiple calls
  angular.module('MediaBrowser')
    .config(["dataTabsProvider", function(dataTabsProvider) {
        // YTF is this called four times!?
        if (unique_datatabs++) {
          return;
        }
        dataTabsProvider
          .add({
            // The scope used by the translate filter
            label: cx.variables.get(
              'TXT_MODULE_TOPICS_FILEBROWSER', 'mediabrowser'),
            icon: 'icon-topics',
            controller: 'TopicsEntryController',
            name: 'topics',
            templateUrl: cx.variables.get('basePath', 'contrexx') +
              'modules/Topics/View/Template/TopicsBrowser.html'
          });
      }])
    .service('TopicsEntryService', ['$http', '$q',
      /**
       * Retrieve Topics Entries
       * @param {Object} $http
       * @param {Object} $q
       * @returns {Promise}
       */
      function($http, $q) {
        return {
          refresh: refresh
        };
        function refresh() {
          var deferred = $q.defer();
          $http.get(cx.variables.get("cadminPath", "contrexx") +
            'index.php?cmd=jsondata&object=Topics&act=getEntries' +
            '&csrf=' + cx.variables.get('csrf'))
            .success(function(response) {
              if (response.data instanceof Object) {
                deferred.resolve(response.data);
              } else if (response.match(/login_form/)) {
                deferred.reject(cx.variables.get(
                  'TXT_FILEBROWSER_LOGGED_OUT', 'mediabrowser'));
              } else {
                deferred.reject("An error occured while fetching Topics Entries");
              }
            }).error(function() {
            deferred.reject("An error occured while fetching Topics Entries");
          });
          return deferred.promise;
        }
      }])
    .controller('TopicsEntryController', [
      '$scope', 'mediabrowserConfig', 'TopicsEntryService',
      function(
        $scope, mediabrowserConfig, TopicsEntryService
        ) {
        // Where is that variable set?
        // I couldn't find it anywhere.
        $scope.activeLanguages = cx.variables.get('languages', 'mediabrowser');
        $scope.searchterm = "";
        $scope.dblclick = dblclick;
        $scope.isLocaleActive = isLocaleActive;
        $scope.setLocale = setLocale;
        if (!$scope.entriesLoaded) {
          TopicsEntryService.refresh().then(function(entities) {
            $scope.entriesLoaded = entities;
            setLocale(cx.variables.get('language', 'mediabrowser'));
          });
        }
        setLocale(cx.variables.get('language', 'mediabrowser'));
        function dblclick(entry) {
          var fn = mediabrowserConfig.get('callbackWrapper');
          if (typeof fn === 'function') {
            fn({
              type: 'TopicsEntry',
              data: [{datainfo: {filepath: entry.slug}}]
            });
          }
          $scope.closeModal();
        }
        function isLocaleActive(lang) {
          return $scope.activeLocale === lang;
        }
        function setLocale(locale) {
          $scope.activeLocale = locale;
          if ($scope.entriesLoaded) {
            $scope.entries = $scope.entriesLoaded[locale];
          }
        }
      }
    ]);
});
