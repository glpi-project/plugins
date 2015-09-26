'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:LinkfreshaccountCtrl
 * @description
 * # LinkfreshaccountCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('FinishActivateAccountCtrl', function (API_URL, $http, $scope, $mdToast, $timeout, $state) {
      $http({
         method: "GET",
         url: API_URL + '/user'
      }).success(function(data) {
         if (data) {
            $scope.user = data;
            $scope.original_username = $scope.user.username;

            if ($scope.user.email === null) {
               $http({
                  method: "GET",
                  url: API_URL + '/oauth/available_emails'
               }).success(function(data) {
                  $scope.available_emails = data;
               });
            } else {
               $state.go('panel');
            }
         }
      });

      $scope.confirm = function() {
         var payload = {};
         if ($scope.original_username != $scope.user.username) {
            payload.username = $scope.user.username;
         }
         if (typeof($scope.email) === 'undefined') {
            var toast = $mdToast.simple()
                      .capsule(true)
                      .content('You need to choose an email now')
                      .position('top');
               toast._options.parent = angular.element(document.getElementById('finish-activate-account'));
               $mdToast.show(toast);
            return;
         } else {
            payload.email = $scope.email;
         }

         $http({
            method: "PUT",
            url: API_URL + '/user',
            data: payload
         }).success(function() {
            var toast = $mdToast.simple()
                      .capsule(true)
                      .content('Your account is now activated ! Welcome on GLPI Plugins.')
                      .position('top');
               toast._options.parent = angular.element(document.getElementById('finish-activate-account'));
               $mdToast.show(toast);
            $timeout(function() {
               $state.go('panel');
            }, 1500);
         });
      };

  });
