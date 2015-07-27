'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SearchCtrl
 * @description
 * # SearchCtrl
 * Controller of the frontendApp
 */

angular.module('frontendApp')
  .controller('SearchCtrl', function ($rootScope, $timeout) {
    $timeout.cancel($rootScope.currentSearch);
    $rootScope.currentSearch = $timeout(function() {
        console.log('Search initialized')
    }, 800);
  });
