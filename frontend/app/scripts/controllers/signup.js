'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SignupCtrl
 * @description
 * # SignupCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('SignupCtrl', function ($scope, $auth) {
      $scope.authenticate = function(provider) {
         $auth.authenticate(provider);
      };
  });
