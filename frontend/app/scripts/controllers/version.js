'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:VersionCtrl
 * @description
 * # VersionCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('VersionCtrl', function (API_URL, $http, $stateParams, $scope, PaginatedCollection, fixIndepnet) {
      $scope.results = PaginatedCollection.getInstance();
      $scope.results.setRequest(function(from,to) {
         var p = $http({
            method: "GET",
            url: API_URL + '/version/'+$stateParams.version+'/plugin',
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

      var fetch = function() {
         if ($stateParams.page) {
            $scope.results.setPage($stateParams.page - 1);
         } else {
            $scope.results.setPage(0);
         }
      };

      fetch();
      $scope.$on('languageChange', fetch);
  });
