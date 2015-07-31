'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PluginCtrl
 * @description
 * # PluginCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
.filter("nrFormat", function() {
  return function(number) {
    var abs;
    if (number !== void 0) {
      console.log(number);
      abs = Math.abs(number);
      if (abs >= Math.pow(10, 12)) {
        number = (number / Math.pow(10, 12)).toFixed(1) + "t";
      } else if (abs < Math.pow(10, 12) && abs >= Math.pow(10, 9)) {
        number = (number / Math.pow(10, 9)).toFixed(1) + "b";
      } else if (abs < Math.pow(10, 9) && abs >= Math.pow(10, 6)) {
        number = (number / Math.pow(10, 6)).toFixed(1) + "m";
      } else if (abs < Math.pow(10, 6) && abs >= Math.pow(10, 3)) {
        number = (number / Math.pow(10, 3)) + "k";
      }
      return number;
    }
  };
});