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
                                      $window, $mdDialog, RECAPTCHA_PUBLIC_KEY) {
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
         $scope.key = RECAPTCHA_PUBLIC_KEY;
         $scope.response = null;
         $scope.widgetId = null;

         $scope.setResponse = function(response) {
            $scope.response = response;
         };
         $scope.setWidgetId = function(widgetId) {
            $scope.widgetId = widgetId;
         };
         $scope.cbExpiration = function() {
            $scope.response = null;
         };


         $scope.userWhoLostPassword = {
            email: ""
         };

         $scope.sendPasswordResetRequest = function() {
             if (!$scope.response) {
                return console.log("You haven't checked the \"I'm not a robot\" checkbox");
             }
             $mdDialog.hide().then(function() {
                $http({
                   method: 'POST',
                   url: API_URL + '/user/sendpasswordresetlink',
                   data: {
                      email: $scope.userWhoLostPassword.email,
                      recaptcha_response: $scope.response
                   }
                });
             });
         };

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
