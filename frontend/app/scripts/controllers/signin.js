'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SigninCtrl
 * @description
 * # SigninCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('SigninCtrl', function (API_URL, Auth, $scope, $http,
                                      $rootScope, $state, $mdToast,
                                      $window, $mdDialog) {
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
            });
         }
      };

      this.linkAccount = function(provider) {
         Auth.linkAccount(provider);
      };

      var IForgotMyPasswordDialog = function($scope, $mdDialog) {
         $scope.close = function() {
            $mdDialog.hide();
         };
      };

      this.iForgotMyPassword = function(ev) {
         $mdDialog.show({
            controller: IForgotMyPasswordDialog,
            templateUrl: 'views/iforgotmypassword.html',
            parent: angular.element(document.body),
            clickOutSideToClose: false,
            targetEvent: ev
         });
      };
  });
