'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:HomeCtrl
 * @description
 * # HomeCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('HomeCtrl', ['API_URL', '$http', '$rootScope', '$scope', '$timeout',
      function(apiUrl, $http, $rootScope, $scope, $timeout) {
         if ($rootScope.currentSearch !== null) {
            $timeout.cancel($rootScope.currentSearch)
            delete $rootScope.currentSearch;
         }
         $rootScope.search = '';

         $http({
            method: 'GET',
            url: apiUrl + '/plugin/trending',
            headers: {
               'X-Range': '0-9'
            }
         })
         .success(function(data, status, headers, config) {
            $scope.trending = data;
         });

         $http({
            method: 'GET',
            url: apiUrl + '/plugin/featured',
            headers: {
               'X-Range': '0-9'
            }
         })
         .success(function(data, status, headers, config) {
            $scope.featured = data;
         });

         $http({
            method: 'GET',
            url: apiUrl + '/plugin/popular',
            headers: {
               'X-Range': '0-9'
            }
         })
         .success(function(data, status, headers, config) {
            $scope.popular = data;
         });

         $http({
            method: 'GET',
            url: apiUrl + '/plugin/updated',
            headers: {
               'X-Range': '0-9'
            }
         })
         .success(function(data, status, headers, config) {
            $scope.updated = data;
         });

         $http({
            method: 'GET',
            url: apiUrl + '/plugin/new',
            headers: {
               'X-Range': '0-9'
            }
         })
         .success(function(data, status, headers, config) {
            $scope.new = data;
         });

         var getTags = function() {
            $http({
               method: 'GET',
               url: apiUrl + '/tags/top',
               headers: {
                  'X-Range': '0-9'
               }
            })
            .success(function(data, status, headers, config) {
               $scope.tags = data;
            });
         };
         getTags();
         $scope.$on('languageChange', function() {
            getTags();
         });

         $scope.fromNow = function(date) {
            date = moment(date);
            if (moment().diff(date, 'days') < 1) {
               return date.calendar().split(' ')[0];  // 'Today'
            }
            return date.fromNow();
         };
      }
   ]);