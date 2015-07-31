'use strict';

/**
 * @ngdoc filter
 * @name frontendApp.filter:subpart
 * @function
 * @description
 * # subpart
 * Filter in the frontendApp.
 */
angular.module('frontendApp')
  .filter('subpart', function () {
    return function (input, many) {
      if (input instanceof Array)
      	return input.slice(0,many);
      else return [];
    };
  });
