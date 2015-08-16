'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SearchCtrl
 * @description
 * # SearchCtrl
 * Controller of the frontendApp
 */

angular.module('frontendApp')

// This controller is created anytime the search
// input's content is changed
.controller('SearchCtrl', function(API_URL, $rootScope, $scope, $timeout, $stateParams, PaginatedCollection, $http) {
   $scope.results = PaginatedCollection.getInstance();
   $scope.results.setRequest(function(from,to) {
      return $http({
                  method: "POST",
                  url: API_URL + '/search',
                  data: {
                     query_string: $stateParams.val
                  },
                  headers: {
                     'X-Range': from+'-'+to
                  }
               });
   });

   var doSearch = function() {
      if ($stateParams.page) {
         $scope.results.setPage($stateParams.page - 1);
      } else {
         $scope.results.setPage(0);
      }
   };

   // cancel the previous $timeout promise if there's any
   $timeout.cancel($rootScope.currentSearch);
   // delaying another request
   if ($stateParams.val.length >= 2) {
      $rootScope.currentSearch = $timeout(doSearch, 800);
   }

   // langswitcher.js will trigger a languageChange
   // event if a change occurs, HTTP headers are then
   // going to change, especially X-Lang, the Search
   // will then be sent again, and the short_description
   // will then be in the correct language
   $scope.$on('languageChange', doSearch);
});