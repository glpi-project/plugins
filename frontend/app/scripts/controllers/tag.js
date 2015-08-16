'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:TagCtrl
 * @description
 * # TagCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('TagCtrl', function(API_URL, $scope, $http, $stateParams, PaginatedCollection) {
      $scope.results = PaginatedCollection.getInstance();
      $scope.results.setRequest(function(from, to) {
         return $http({
            method: "GET",
            url: API_URL + '/tags/' + $stateParams.key + '/plugin',
            headers: {
               'X-Range': from+'-'+to
            }
         });
      });
      $http({
         method: "GET",
         url: API_URL + '/tags/' + $stateParams.key
      })
      .success(function(data) {
         $scope.tag = data;
      });

      var loadPage = function() {
         if ($stateParams.page) {
            $scope.results.setPage($stateParams.page);
         } else {
            $scope.results.setPage(0);
         }
      };

      loadPage();
      $scope.$on('languageChange', loadPage);
   });