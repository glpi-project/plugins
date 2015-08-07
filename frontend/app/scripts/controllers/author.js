'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:AuthorCtrl
 * @description
 * # AuthorCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('AuthorCtrl', function ($http, $stateParams, $scope) {
    $http({
            method: "GET",
            url: API_URL + '/author/'+$stateParams.id
        })
        .success(function(data) {
            $scope.author = data;
        });
  });
