'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:AuthorCtrl
 * @description
 * # AuthorCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('AuthorPluginsCtrl', function(API_URL, $scope, $http, $stateParams, PaginatedCollection, $state, Toaster) {
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
         loadPage();
      })
      .error(function(data) {
         if (data.error === 'RESOURCE_NOT_FOUND') {
            $state.go('featured');
            Toaster.make('This author does not exist', 'body');
         }
      });

      $scope.$on('languageChange', loadPage);
   });