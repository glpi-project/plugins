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
         if (Auth.getToken() === null) {
            $http({
               method: "POST",
               url: API_URL + "/oauth/authorize",
               // Making use of jQuery.param
               // to serialize parameters
               data: jQuery.param({
                  grant_type: "password",
                  client_id: "webapp",
                  username: $scope.login,
                  password: $scope.password
               }),
               // Those parameters are passed via
               // an urlencoded string
               headers: {
                  'Content-Type': 'application/x-www-form-urlencoded'
               }
            }).success(function(data) {
               Auth.setToken(data.access_token, data.expires_in);
            }).error(function(data) {
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
