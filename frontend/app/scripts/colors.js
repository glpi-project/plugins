'use strict';

angular.module('frontendApp')
   .config(function($mdThemingProvider) {
      // Define colors of application, from material angular
      $mdThemingProvider.theme('default')
         .primaryPalette('red', {
            'default': '200'
         })
         .accentPalette('blue');
   });