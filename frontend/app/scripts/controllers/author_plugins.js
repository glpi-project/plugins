'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:AuthorCtrl
 * @description
 * # AuthorCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('AuthorPluginsCtrl', function(API_URL, $scope, $http, $stateParams) {
      var grabAuthorPlugins = function() {
         $http({
            method: "GET",
            url: API_URL + '/author/' + $stateParams.id + '/plugin'
         })
         .success(function(data) {
            $scope.results = data;
         });
      };

      $http({
         method: "GET",
         url: API_URL + '/author/' + $stateParams.id
      })
      .success(function(data) {
         $scope.author = data;
      });

      $scope.results = [];

      grabAuthorPlugins();
      $scope.$on('languageChange', grabAuthorPlugins);
   });