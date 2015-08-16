'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:AuthorCtrl
 * @description
 * # AuthorCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('AuthorPluginsCtrl', function(API_URL, $scope, $http, $stateParams, PaginatedCollection) {
      $scope.results = PaginatedCollection.getInstance();
      $scope.results.setRequest(function(from, to) {
         return $http({
                     method: "GET",
                     url: API_URL + '/author/' + $stateParams.id + '/plugin',
                     headers: {
                        'X-Range': from+'-'+to
                     }
                  });
      });

      var loadPage = function() {
         if ($stateParams.page) {
            $scope.results.setPage($stateParams.page - 1);
         } else {
            $scope.results.setPage(0);
         }
      };

      $http({
         method: "GET",
         url: API_URL + '/author/' + $stateParams.id
      })
      .success(function(data) {
         $scope.author = data;
      });

      loadPage();
      $scope.$on('languageChange', loadPage);
   });