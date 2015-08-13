'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:AuthorsCtrl
 * @description
 * # AuthorsCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('AuthorsCtrl', function(API_URL, $http, $scope) {
      $http({
         method: "GET",
         url: API_URL + '/author'
      })
      .success(function(data) {
         $scope.authors = data;
      });
      
      $scope.authors = [];
   });