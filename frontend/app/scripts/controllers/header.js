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
                $state.go('search', {
                    val: $scope.search
                });
        }
        else {
            $state.go('featured');
        }
    });
  });
