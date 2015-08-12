'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:TagsCtrl
 * @description
 * # TagsCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('TagsCtrl', function (API_URL, $http, $stateParams, $scope) {
	    $http({
	            method: "GET",
	            url: API_URL + '/tags'
	        })
	        .success(function(data) {
	            $scope.tags = data;
	        });
  });