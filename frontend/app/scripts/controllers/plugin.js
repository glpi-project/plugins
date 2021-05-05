'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PluginCtrl
 * @description
 * # PluginCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')

.controller('PluginCtrl', function(API_URL, $scope, $http, $stateParams,
                                   $window, $filter, $state, $mdToast,
                                   $timeout, fixIndepnet, Toaster, PluginVersions) {
   $scope.plugin = {
      authors: {},
      download_count: 0
   };
   $scope.rated = false;
   $scope.download_url = API_URL + '/plugin/'+$stateParams.key+'/download';

   $scope.ratePlugin = function(note) {
      if (!$scope.rated) {
         $http({
            method: 'POST',
            url: API_URL + '/plugin/star',
            data: {
               note: note,
               plugin_id: $scope.plugin.id
            }
         })
         .success(function(data) {
            $scope.plugin.note = data.new_average;
         });

         localStorage.setItem('rated_' + $scope.plugin.id, true);
         $scope.rated = true;
      }
   };

   $scope.watch = function() {
      if (!$scope.plugin.watched) {
         $http({
            method: 'POST',
            url: API_URL + '/user/watchs',
            data: {
               plugin_key: $scope.plugin.key
            }
         }).then(function(resp) {
            $scope.plugin.watched = true;
            Toaster.make($filter('translate')('YOURE_NOW_WATCHING')+' '+$scope.plugin.key+'');
         }, function(resp) {
            if (resp.data.error == 'ALREADY_WATCHED') {
               Toaster.make($filter('translate')('PLUGIN_ALREADY_WATCHED'));
            }
         });
      } else {
         $http({
            method: 'DELETE',
            url: API_URL + '/user/watchs/'+$scope.plugin.key
         }).then(function(resp) {
            $scope.plugin.watched = false;
            Toaster.make($filter('translate')('PLUGIN_UNWATCHED')+' '+$scope.plugin.key+'');
         });
      }
   }

   $scope.fromNow = function(date) {
      if (date === undefined ||
          date === null) {
         return $filter('translate')('NEVER_UPDATED');
      }
      date = moment(date);
      if (moment().diff(date, 'days') < 1) {
         return date.calendar().split(' ')[0];  // 'Today'
      }
      return date.fromNow();
   };

   // Enable retrieval of tab index according
   // to given language
   var getTabWithLang = function(lang, defaults) {
      if (!defaults)
         defaults = 'en';

      for (var index in $scope.plugin.descriptions) {
         if (lang === $scope.plugin.descriptions[index].lang) {
            return index;
         }
      }

      if (lang == defaults) {
         return 0;
      }

      return getTabWithLang(defaults, defaults);
   };

   $scope.$on('languageChange', function(evt, data) {
      $scope.selectedIndex = getTabWithLang(data.newLang);
   });

   // Fetch the Plugin resource
   $scope.$on('languageChange', function(event, data) {
      var found_index = null;
      for (var index in $scope.plugin.descriptions) {
         if (data.newLang === $scope.plugin.descriptions[index].lang) {
            found_index = index;
         }
      }
      if (found_index !== null) {
         $scope.selectedIndex = found_index;
      }
   });

   $scope.screenshots = [];
   $scope.tags = [];

   // This local-to-controller helper function
   // helps filtering the tags for the current
   // selected language
   var filterTags = function(lang) {
      var tags = [];
      for (var i in $scope.plugin.tags) {
         var tag = $scope.plugin.tags[i];
         if (tag.lang == lang) {
            tags.push(tag);
         }
      }
      if (tags.length == 0 && lang != 'en') {
         filterTags('en');
      } else {
         $scope.tags = tags;
      }
   };
   $scope.$on('languageChange', function(evt, data) {
      filterTags(data.newLang);
   });

   $scope.displayLatestCompatibleGlpiVersions = function(versions) {
      var versions = jQuery.extend([], versions); //copying object
      var topCompatibleGlpiVersions = [];
      if (versions.length) {
         var topVersion = versions.shift();
         var pluginTopVer = topVersion.num;
         topCompatibleGlpiVersions.push(topVersion.compatibility)
         for (var n in versions) {
            if (versions[n].num == pluginTopVer) {
               topCompatibleGlpiVersions.push(versions[n].compatibility);
            }
         }
      }
      return topCompatibleGlpiVersions;
   };

   $http({
      method: 'GET',
      url: API_URL + '/plugin/' + $stateParams.key
   })
   .success(function(data) {
      fixIndepnet.fix(data);
      $scope.plugin = data;
      $scope.plugin.versions = PluginVersions.sort(data.versions);
      $scope.rated = (localStorage.getItem('rated_' + $scope.plugin.id) == 'true') ? true : false;
      $scope.selectedIndex = getTabWithLang(localStorage.getItem('lang'));

      for (var index in $scope.plugin.screenshots) {
         var screenshot_url = $scope.plugin.screenshots[index].url;
         var thumb_url = screenshot_url;
         if (typeof $scope.plugin.screenshots[index].thumb_url !== 'undefined') {
            thumb_url = $scope.plugin.screenshots[index].thumb_url;
         }
         $scope.screenshots.push({
            'thumb': thumb_url,
            'img': screenshot_url,
            'description': ""
         });
      }

      filterTags(localStorage.getItem('lang'));
   })
   .error(function(data) {
      if (/^RESOURCE_NOT_FOUND/.exec(data.error)) {
         $state.go('featured');
         Toaster.make('404 ! This plugin doesn\'t exit', 'body');
      }
   });
});
