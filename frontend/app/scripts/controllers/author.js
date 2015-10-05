'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:AuthorCtrl
 * @description
 * # AuthorCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('AuthorCtrl', function(API_URL, $http, $stateParams, $scope, $state, Toaster) {
      $http({
         method: "GET",
         url: API_URL + '/author/' + $stateParams.id
      })
      .success(function(data) {
         $scope.author = data;
      })
      .error(function(data) {
         if (/^RESOURCE_NOT_FOUND/.exec(data.error)) {
            $state.go('featured');
            Toaster.make('This author does not exists');
         }
      });
   });