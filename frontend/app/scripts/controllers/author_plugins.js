'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:AuthorCtrl
 * @description
 * # AuthorCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('AuthorPluginsCtrl', function (API_URL, $scope, $http, $stateParams, inlineAuthors) {
    $scope.results = [];
    $scope.inlineAuthors = inlineAuthors;
    $http({
        method: "GET",
        url: API_URL + '/author/'+$stateParams.id
        })
        .success(function(data) {
            $scope.author = data;
        });
    $http({
            method: "GET",
            url: API_URL + '/author/'+$stateParams.id+'/plugin'
        })
        .success(function(data) {
            $scope.results = data;
        });
  });
