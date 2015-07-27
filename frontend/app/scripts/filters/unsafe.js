'use strict';

/**
 * @ngdoc filter
 * @name frontendApp.filter:unsafe
 * @function
 * @description
 * # unsafe
 * Filter in the frontendApp.
 */
angular.module('frontendApp')
  .filter('unsafe', function ($sce) {
    return function (val) {
      return $sce.trustAsHtml(val);
    };
  });
