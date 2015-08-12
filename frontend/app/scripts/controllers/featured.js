'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:HomeCtrl
 * @description
 * # HomeCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('FeaturedCtrl', ['API_URL', '$http', '$rootScope', '$scope', '$timeout',
    function (apiUrl, $http, $rootScope, $scope, $timeout) {
      if ($rootScope.currentSearch !== null) {
        $timeout.cancel($rootScope.currentSearch)
        delete $rootScope.currentSearch;
      }
      $rootScope.search = '';

      if ($rootScope.trending.length < 1) {
        $http({
          method: 'GET',
          url: apiUrl + '/plugin/trending'
        })
        .success(function(data, status, headers, config) {
          $rootScope.trending = data;
        });
      }

      if ($rootScope.popular.length < 1) {
        $http({
          method: 'GET',
          url: apiUrl + '/plugin/popular'
        })
        .success(function(data, status, headers, config) {
          $rootScope.popular = data;
        });
      }

      if ($rootScope.updated.length < 1) {      
        $http({
          method: 'GET',
          url: apiUrl + '/plugin/updated'
        })
        .success(function(data, status, headers, config) {
          $rootScope.updated = data;
        });
      }

      if ($rootScope.new.length < 1) {      
        $http({
          method: 'GET',
          url: apiUrl + '/plugin/new'
        })
        .success(function(data, status, headers, config) {
          $rootScope.new = data;
        });
      }

      if ($rootScope.tags.length < 1) {      
        $http({
          method: 'GET',
          url: apiUrl + '/tags/top'
        })
        .success(function(data, status, headers, config) {
          $rootScope.tags = data;
        });
      }

      if ($rootScope.authors.length < 1) {      
        $http({
          method: 'GET',
          url: apiUrl + '/author'
        })
        .success(function(data, status, headers, config) {
          $rootScope.authors = data;
        });
      }

      $scope.fromNow = function(date) {
        return moment(date).fromNow();
      };
  }]);
