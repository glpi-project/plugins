'use strict';

/**
 * @ngdoc directive
 * @name frontendApp.directive:eatClickIf
 * @description
 * # eatClickIf
 * Thanks a lot to people at
 * http://stackoverflow.com/questions/25600071/how-to-achieve-that-ui-sref-be-conditionally-executed
 * for this directive which emerged in this StackOverflow post
 */
angular.module('frontendApp')
  .directive('eatClickIf', function($parse, $rootScope) {
    return {
      // this ensure eatClickIf be compiled before ngClick
      priority: 100,
      restrict: 'A',
      compile: function($element, attr) {
        var fn = $parse(attr.eatClickIf);
        return {
          pre: function link(scope, element) {
            var eventName = 'click';
            element.on(eventName, function(event) {
              var callback = function() {
                if (fn(scope, {$event: event})) {
                  // prevents ng-click to be executed
                  event.stopImmediatePropagation();
                  // prevents href 
                  event.preventDefault();
                  return false;
                }
              };
              if ($rootScope.$$phase) {
                scope.$evalAsync(callback);
              } else {
                scope.$apply(callback);
              }
            });
          },
          post: function() {}
        }
      }
    }
  });
