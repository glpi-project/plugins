'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SearchCtrl
 * @description
 * # SearchCtrl
 * Controller of the frontendApp
 */

angular.module('frontendApp')

  .factory('Search', function(API_URL, $http) {
  	return function(string) {
  		return $http({
  			method: "POST",
  			url: API_URL + '/search',
  			data: {
  				query_string: string
  			}
  		});
  	}
  })

  .controller('SearchCtrl', function ($rootScope, $timeout, Search) {
    $timeout.cancel($rootScope.currentSearch);
    $rootScope.currentSearch = $timeout(function() {
        Search("mana")
          .success(function(data, status, headers, config) {
          	console.log(data, status, headers, config);
          });
    }, 800);
  });
