'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:TagCtrl
 * @description
 * # TagCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('TagCtrl', function (API_URL, $scope, $http, $stateParams, inlineAuthors) {
    $scope.results = [];
    $scope.inlineAuthors = inlineAuthors;
    $http({
        method: "GET",
        url: API_URL + '/tags/'+$stateParams.id
        })
        .success(function(data) {
            $scope.listName = "Tagged with \""+data.tag+"\”";
        });
    $http({
            method: "GET",
            url: API_URL + '/tags/'+$stateParams.id+'/plugin'
        })
        .success(function(data) {
            $scope.results = data;
        });
  });
