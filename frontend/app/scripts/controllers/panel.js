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
      /**
       * Query the user profile infos
       * via REST
       */
      $http({
         method: "GET",
         url: API_URL + '/user'
      }).success(function(data) {
         $scope.user = data;
         console.log($scope.user);
      });

      /**
       * scope method to update the profile
       * (i.e: change the user profile infos)
       */
      $scope.update = function() {
         console.log('update');
         console.log($scope.user, $scope.password, $scope.password_repeat);
      };

      /**
       * scope method to open the "link an account"
       * dialog
       */
      $scope.openLinkAccountDialog = function(ev) {
        $mdDialog.show({
          controller: LinkAccountDialogController,
          templateUrl: 'views/linkaccount.html',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutSideToClose: true
        });
      };

      /**
       * "Link an Account" controller
       */
      function LinkAccountDialogController (API_URL, $scope, $http) {
         /**
          * scope method to actually link the account
          */
         $scope.connect = function(provider) {
            if (provider == 'github') {
               Auth.linkAccount('github');
            }
            $mdDialog.hide();
         };

         /**
          * scope method to close the $mdDialog
          */
         $scope.close = function() {
            $mdDialog.hide();
         };

         /**
          * scope method to unlink an external account
          */
         $scope.unlinkAccount = function(external_account_id) {
            console.log(external_account_id);
         };

         /**
          * Fetch list of current external accounts
          * when controller loads
          */
         $http({
            method: "GET",
            url: API_URL + '/user/external_accounts'
         }).success(function(data) {
            $scope.external_accounts = data;
         });
      }
  });
