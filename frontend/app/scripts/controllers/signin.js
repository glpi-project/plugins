'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SigninCtrl
 * @description
 * # SigninCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('SigninCtrl', function (API_URL, Auth, $scope, $http, $rootScope, $state, $mdToast) {
      if ($rootScope.authed) {
         $state.go('featured');
      }

      $scope.login = '';
      $scope.password = '';

      this.loginAttempt = function() {
         if (localStorage.getItem('authed') === null) {
            Auth.loginAttempt({
               anonymous: false,
               login: $scope.login,
               password: $scope.password
            })
            .error(function(data) {
               var toast = $mdToast.simple()
                  .capsule(true)
                  .content(data.error)
                  .position('top');
               toast._options.parent =  angular.element(document.getElementById('signin'));
               $mdToast.show(toast);
            });
         }
      };
  });
