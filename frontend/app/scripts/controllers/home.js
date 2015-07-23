'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:HomeCtrl
 * @description
 * # HomeCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('HomeCtrl', ['API_URL', '$http', '$scope', function (apiUrl, http) {
  		$scope.trending = [];
  		$scope.new = [];
  		$scope.popular = [];
  		$scope.updated = [];
  		$scope.tags = [];
  		$scope.authors = [];

      // $http.get(apiUrl)
      //   .success(function(data, status, headers, config) {
  
      //   });
  		// $http({
  		// 	method: 'GET',
  		// 	url: apiUrl + '/plugin/last';
  		// })

    }]);
