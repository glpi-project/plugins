'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:NotificationsCtrl
 * @description
 * # NotificationsCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('NotificationsCtrl', function (API_URL, $http, $scope, Toaster, $filter) {
    $scope.plugins_watched = [];

    function loadWatchs() {
       $http({
         method: 'GET',
         url: API_URL + '/user/watchs'
       }).then(function(resp) {
         $scope.plugins_watched = resp.data;
       });
    }
    loadWatchs();

    $http({
      method: 'GET',
      url: API_URL + '/plugin/trending'
    }).then(function(resp) {
      $scope.trending = resp.data;
    });

    $scope.unwatch = function(key) {
      $http({
         method: 'DELETE',
         url: API_URL + '/user/watchs/'+key
      }).then(function() {
         loadWatchs();
         Toaster.make($filter('translate')('PLUGIN_UNWATCHED')+' '+ key);
      });
    };
  });
