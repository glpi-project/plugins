'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SignupCtrl
 * @description
 * # SignupCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('SignupCtrl', function (API_URL, $scope, $http, $window, Auth) {
      $scope.user = {};

      $scope.authenticate = function(provider) {
         Auth.linkAccount(provider);
      };

      $scope.signUp = function() {
         // $http({
         //    method: "POST",
         //    url: API_URL + "/user",
         //    data: $scope.user
         // });
         console.log('sign up', $scope.user);
      };
  });
