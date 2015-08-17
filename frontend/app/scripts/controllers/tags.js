'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:TagsCtrl
 * @description
 * # TagsCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('TagsCtrl', function(API_URL, $http, $stateParams, $state, $scope, PaginatedCollection) {
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

      var fetch = function() {
         if ($stateParams.page) {
            $scope.tags.setPage($stateParams.page - 1);
         } else {
            $scope.tags.setPage(0);
         }
      };

      $scope.$on('languageChange', function() {
         delete $stateParams.page;
         if (/_page$/.exec($state.current.name)) {
            var state = $state.current.name.split('_page')[0];
         } else {
            var state = $state.current.name;
         }
         $state.go(state,$stateParams,{
            notify:false,
            reload:false,
            location:'replace',
            inherit:true
         });
         fetch();
      });
      fetch();
   });