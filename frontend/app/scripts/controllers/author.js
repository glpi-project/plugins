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
         if (data.error === 'RESOURCE_NOT_FOUND') {
            $state.go('featured');
            Toaster.make('This author does not exists');
         }
      });
   });