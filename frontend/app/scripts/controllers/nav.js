'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:NavCtrl
 * @description
 * # NavCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('NavCtrl', ['$scope', '$mdSidenav',
   function ($scope, $mdSidenav) {
      
      $scope.toggleNavBar = function() {
         $mdSidenav("side-menu").toggle();
      };

      $scope.closeNavBar = function() {
         $mdSidenav("side-menu").close();
      };
   
   }]);
