'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:NavCtrl
 * @description
 * # NavCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('NavCtrl', ['$scope', '$mdSidenav', '$rootScope',
   function ($scope, $mdSidenav, $rootScope) {
      
      $scope.toggleNavBar = function() {
         $mdSidenav("side-menu").toggle();
      };

      $scope.closeNavBar = function() {
         $mdSidenav("side-menu").close();
      };

      $rootScope.$on('$stateChangeStart', function(){ 
          $scope.closeNavBar();
      });   
   }]);
