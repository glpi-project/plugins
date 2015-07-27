'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:HomeCtrl
 * @description
 * # HomeCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('HomeCtrl', ['API_URL', '$http', '$scope', '$mdSidenav',
    function (apiUrl, $http, $scope, $mdSidenav) {
  		$scope.trending = [];
  		$scope.new = [];
  		$scope.popular = [];
  		$scope.updated = [];
  		$scope.tags = [];
  		$scope.authors = [];

      $scope.search = '';

  		$http({
  			method: 'GET',
  			url: apiUrl + '/plugin/trending'
  		})
      .success(function(data, status, headers, config) {
        $scope.trending = data;
      });

      $http({
        method: 'GET',
        url: apiUrl + '/plugin/popular'
      })
      .success(function(data, status, headers, config) {
        $scope.popular = data;
      });

      $http({
        method: 'GET',
        url: apiUrl + '/plugin/updated'
      })
      .success(function(data, status, headers, config) {
        $scope.updated = data;
      });

      $http({
        method: 'GET',
        url: apiUrl + '/author'
      })
      .success(function(data, status, headers, config) {
        $scope.authors = data;
      });

      $scope.toggleSideNavTest = function() {
        $mdSidenav('side-menu').toggle();
      };
  }]);
