'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:HeaderCtrl
 * @description
 * # HeaderCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('HeaderCtrl', function ($scope, $state) {
    $scope.search = '';

    $scope.$watch('search', function() {
    	if ($scope.search.length > 0) {
    		if (!$state.is('search')) {
    			$state.go('search');
    		}
    	}
    	else {
    		$state.go('home');
    	}
    });
  });
