'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:TagCtrl
 * @description
 * # TagCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('TagCtrl', function(API_URL, $scope, $http, $stateParams, PaginatedCollection, $state, Toaster, fixIndepnet) {
      $scope.results = PaginatedCollection.getInstance();
      $scope.loading = true;
      $scope.results.setRequest(function(from, to) {
         $scope.loading = true;
         var p = $http({
            method: "GET",
            url: API_URL + '/tags/' + $stateParams.key + '/plugin',
            headers: {
               'X-Range': from+'-'+to
            }
         });
         p.then(function(resp) {
            $scope.loading = false;
            for (var n in resp.data) {
               fixIndepnet.fix(resp.data[n]);
            }
            return resp;
         });
         return p;
      });
      $http({
         method: "GET",
         url: API_URL + '/tags/' + $stateParams.key
      })
      .success(function(data) {
         $scope.tag = data;
      })
      .error(function(data) {
         if (/^RESOURCE_NOT_FOUND/.exec(data.error)) {
            $state.go('featured');
            Toaster.make('404 ! This tag doesn\'t exit', 'body');
         }
      });

      var loadPage = function() {
         if ($stateParams.page) {
            $scope.results.setPage($stateParams.page - 1);
         } else {
            $scope.results.setPage(0);
         }
      };

      loadPage();
      $scope.$on('languageChange', loadPage);
   });
