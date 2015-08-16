'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:TagsCtrl
 * @description
 * # TagsCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('TagsCtrl', function(API_URL, $http, $stateParams, $scope, PaginatedCollection) {
      $scope.tags = PaginatedCollection.getInstance();
      $scope.tags.setRequest(function(from, to) {
         return $http({
            method: "GET",
            url: API_URL + '/tags',
            headers: {
               'X-Range': from+'-'+to
            }
         });
      });

      if ($stateParams.page) {
         $scope.tags.setPage($stateParams.page);
      } else {
         $scope.tags.setPage(0);
      }
   });