'use strict';

/**
 * @ngdoc directive
 * @name frontendApp.directive:rateBox
 * @description
 * # rateBox
 */
angular.module('frontendApp')
// From http://stackoverflow.com/questions/14833326/how-to-set-focus-on-input-field
.directive('focusMe', function() {
   return {
      scope: {
         trigger: '=focusMe'
      },
      link: function(scope, element) {
         scope.$watch('trigger', function(value) {
            if (value === true) {
               element[0].focus();
               scope.trigger = false;
            }
         });
      }
   };
});