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
.filter('unique', function() {
   return function(collection, keyname) {
      var output = [], 
          keys = [];

      angular.forEach(collection, function(item) {
          var key = item[keyname];
          if(keys.indexOf(key) === -1) {
              keys.push(key);
              output.push(item);
          }
      });

      return output;
   };
});