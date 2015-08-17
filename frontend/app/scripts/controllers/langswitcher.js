'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:LangswitcherCtrl
 * @description
 * # LangswitcherCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .provider('UpdateHttpHeaderSettings', function($httpProvider) {
      var changeHttpHeaderSettings = function() {
         $httpProvider.defaults.headers.common['X-Lang'] = localStorage.getItem('lang');
      };

      this.$get = function() {
         return changeHttpHeaderSettings;
      };
   })

.controller('LangSwitcherCtrl', function($scope, $translate, $rootScope, UpdateHttpHeaderSettings) {
   $scope.lang = localStorage.getItem('lang');

   var setLanguage = function(lang) {
      $translate.use(lang);
      if (lang === 'en')
         moment.locale('en-gb');
      else
         moment.locale(lang);
      localStorage.setItem('lang', lang);

      UpdateHttpHeaderSettings();

      $rootScope.currentLang = lang;
      // Broadcasting an event to every scope
      $rootScope.$broadcast('languageChange', {
         newLang: lang
      });
   };

   $scope.$watch('lang', setLanguage);
});