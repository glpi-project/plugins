'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PanelCtrl
 * @description
 * # PanelCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('PanelCtrl', function (API_URL, $http, $scope, $mdDialog, Auth) {
      $http({
         method: "GET",
         url: API_URL + '/user'
      }).success(function(data) {
         $scope.user = data;
         console.log($scope.user);
      });

      $scope.update = function() {
         console.log('update');
         console.log($scope.user, $scope.password, $scope.password_repeat);
      };

      function LinkAccountDialogController ($scope) {
        $scope.connect = function(provider) {
          if (provider == 'github') {
            Auth.linkAccount('github');
          }
          $mdDialog.hide();
        };
        $scope.close = function() {
          $mdDialog.hide();
        }
      }
      $scope.linkAccount = function(ev) {
        $mdDialog.show({
          controller: LinkAccountDialogController,
          templateUrl: 'views/linkaccount.html',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutSideToClose: true
        });
      };
  });
