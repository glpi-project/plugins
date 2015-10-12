'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:TagCtrl
 * @description
 * # TagCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('TagCtrl', function(API_URL, $scope, $http, $stateParams, PaginatedCollection, fixIndepnet) {
      $scope.results = PaginatedCollection.getInstance();
      $scope.results.setRequest(function(from, to) {
         var p = $http({
            method: "GET",
            url: API_URL + '/tags/' + $stateParams.key + '/plugin',
            headers: {
               'X-Range': from+'-'+to
            }
         });
         p.then(function(resp) {
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