'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:AllCtrl
 * @description
 * # AllCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('AllCtrl', function(API_URL, $http, $scope) {
      var grabAllPlugins = function() {
         $http({
            method: "GET",
            url: API_URL + '/plugin',
            headers: {
               'X-Range': $scope.from+'-'+$scope.to
            }
         })
         .success(function(data, status, headers) {
            $scope.results = data;
            var contentRange = /([0-9]+)-([0-9]+)\/([0-9]+)/.exec(headers()['content-range']);
            var startIndex = parseInt(contentRange[1]);
            var endIndex = parseInt(contentRange[2]);
            var maxIndex = parseInt(contentRange[3]);
            
            var pageCount = Math.ceil(maxIndex / (endIndex - startIndex));
            $scope.pages = [];
            for (var n = 0, i = 0 ; n < pageCount ; n++) {
               $scope.pages.push({
                  startIndex: i,
                  n: n
               });
               i += (endIndex - startIndex + 1);
            }
         });
      };

      $scope.openPage = function(i) {
         console.log(i);
      };

      $scope.page = 0;
      $scope.results = [];
      $scope.from = 0;
      $scope.to = 14;

      grabAllPlugins();
      $scope.$on('languageChange', grabAllPlugins);
   });