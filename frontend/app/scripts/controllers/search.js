'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SearchCtrl
 * @description
 * # SearchCtrl
 * Controller of the frontendApp
 */

angular.module('frontendApp')

  // This Search factory is used to search
  // for items using the REST endpoint
  // it returns the $http promise
  .factory('Search', function(API_URL, $http) {
  	return function(string) {
  		return $http({
  			method: "POST",
  			url: API_URL + '/search',
  			data: {
  				query_string: string,
          lang: localStorage.getItem('lang')
  			}
  		});
  	}
  })

  // This controller is created anytime the search
  // input's content is changed
  .controller('SearchCtrl', function ($rootScope, $scope, $timeout, Search, $stateParams) {
    // will store the results
    $scope.results = [];
    // cancel the previous $timeout promise if there's any
    $timeout.cancel($rootScope.currentSearch);
    // delaying another request
    $rootScope.currentSearch = $timeout(function() {
        Search($stateParams.val)
          .success(function(data, status, headers, config) {
            // moving the results to the $scope
          	$scope.results = data;
          });
    }, 800);

    // method to sort by relevance
    $scope.sortByRelevance = function() {

    };
    // method to sort by popularity
    $scope.sortByPopularity = function() {

    };
  });
