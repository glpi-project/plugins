'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PanelCtrl
 * @description
 * # PanelCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('PanelCtrl', function (API_URL, $http, $scope) {
      $http({
         method: "GET",
         url: API_URL + '/user'
      }).success(function(data) {
         $scope.user = data;
         console.log($scope.user);
      });

      $scope.update = function() {
         console.log('update');
         console.log($scope.user, $scope.password, $scope.password_repeat);
      };
  });
