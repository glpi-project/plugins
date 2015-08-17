'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PluginCtrl
 * @description
 * # PluginCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')

.controller('PluginCtrl', function(API_URL, $scope, $http, $stateParams, $window) {
   $scope.plugin = {
      authors: {},
      downloaded: 0
   };
   $scope.rated = false;

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

   $scope.download = function() {
      $window.location.href = API_URL + '/plugin/' + $scope.plugin.key + '/download';
   };

   $scope.fromNow = function(date) {
      return moment(date).fromNow();
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

   $http({
      method: 'GET',
      url: API_URL + '/plugin/' + $stateParams.key
   })
   .success(function(data) {
      $scope.plugin = data;
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
   });
});