'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:TagCtrl
 * @description
 * # TagCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('TagCtrl', function(API_URL, $scope, $http, $stateParams) {
      var grabTaggedPlugins = function() {
         $http({
            method: "GET",
            url: API_URL + '/tags/' + $stateParams.key
         })
         .success(function(data) {
            $scope.tag = data;
         });

         $http({
            method: "GET",
            url: API_URL + '/tags/' + $stateParams.key + '/plugin'
         })
         .success(function(data) {
            $scope.results = data;
         });
      };

      $scope.results = [];
      grabTaggedPlugins();
      $scope.$on('languageChange', grabTaggedPlugins);
   });