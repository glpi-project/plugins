'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:AuthorsCtrl
 * @description
 * # AuthorsCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('AuthorsCtrl', function(API_URL, $http, $scope, PaginatedCollection, $stateParams) {
      $scope.authors = PaginatedCollection.getInstance();
      $scope.authors.setRequest(function(from,to) {
         return $http({
            method: "GET",
            url: API_URL + '/author',
            headers: {
            'X-Range': from+'-'+to
         }
         });
      });

      if ($stateParams.page) {
         $scope.authors.setPage($stateParams.page - 1);
      } else {
         $scope.authors.setPage(0);
      }
   });