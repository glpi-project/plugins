'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PanelCtrl
 * @description
 * # PanelCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('PanelCtrl', function (API_URL, $http, $scope, $mdDialog, Auth, $state) {
      $scope.original_user = {};
      $scope.user = {};

      /**
       * Query the user profile infos
       * via REST
       */
      $http({
         method: "GET",
         url: API_URL + '/user'
      }).success(function(data) {
         if (!data.active) {
          return $state.go('finishactivateaccount');
         }
         $scope.user = data;
         $scope.original_user = jQuery.extend({}, $scope.user);
      });

      $http({
         method: "GET",
         url: API_URL + '/user/plugins'
      }).success(function(data) {
         $scope.plugins = data;
      });


      /**
       * scope method to update the profile
       * (i.e: change the user profile infos)
       */
      $scope.update = function() {
         console.log('update');

         var payload = {};

         if (typeof($scope.password) === 'string') {
            payload.password = $scope.password;
         }

         if ($scope.user.website != $scope.original_user.website) {
            payload.website = $scope.user.website;
         }

         $http({
            type: 'PUT',
            url: API_URL + '/user',
            data: payload
         }).success(function(data) {
            console.log(data);
         });
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
