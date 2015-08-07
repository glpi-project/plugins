'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:AuthorCtrl
 * @description
 * # AuthorCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('AuthorCtrl', function (API_URL, $scope, $http, $stateParams, inlineAuthors) {
    $scope.results = [];
    $scope.inlineAuthors = inlineAuthors;
    $http({
        method: "GET",
        url: API_URL + '/author/'+$stateParams.id
        })
        .success(function(data) {
            $scope.listName = "Plugins <span>" +data.name+ "</span> worked on";
        });
    $http({
            method: "GET",
            url: API_URL + '/author/'+$stateParams.id+'/plugin'
        })
        .success(function(data) {
            $scope.results = data;
        });
  });
