'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PanelCtrl
 * @description
 * # PanelCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('PanelCtrl', function (API_URL, $http, $scope, FormValidator,
                                     $mdDialog, Auth, $state, Toaster) {
      $scope.original_user = {};
      $scope.user = {};
      $scope.password = '';
      $scope.password_repeat = '';

      $scope.form_errors = {
         username: {
              tooshort: false,
              toolong: false
         },
         realname: {
               tooshort: false,
               toolong: false
         },
         password: {
             tooshort: false,
             toolong: false,
             different: false
         }
      };

      // Wrap testing of password and repetition in a single anonymous function
      var testPassword = function() {
         $scope.form_errors.password = ($scope.password.length > 0 ?
                                        FormValidator.getValidator('password')($scope.password, $scope.password_repeat) :
                                        {
                                           tooshort: false,
                                           toolong: false,
                                           different: false
                                        });
      };
      $scope.$watch('password', testPassword);
      $scope.$watch('password_repeat', testPassword);

      // Verifying user.realname on keydown
      $scope.$watch('user.realname', function() {
         var default_errors = {
            tooshort: false,
            toolong: false
         };
         $scope.form_errors.realname = (typeof $scope.user.realname === 'undefined' ? default_errors : ($scope.user.realname.length > 0 ?
                                        FormValidator.getValidator('realname')($scope.user.realname) :
                                        default_errors));
      });

      // Verifying user.realname on keydown
      $scope.$watch('user.website', function() {
         var default_errors = {
            invalid: false
         };
         var website = typeof $scope.user.website == 'string' ? $scope.user.website : '';
         $scope.form_errors.website = FormValidator.getValidator('website')(website);
      });

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
         var payload = {};

         // Verifying password if provided
         if ($scope.password.length > 0) {
            if (!FormValidator.noError(FormValidator.getValidator('password')($scope.password,
                                                                              $scope.password_repeat))) {
               return Toaster.make('You must verify the password you entered, read the hints in red', 'profile-form');
            } else {
               payload.password = $scope.password;
            }
         }

         // Verifying realname if provided
         if ($scope.user.realname != $scope.original_user.realname) {
            if (!FormValidator.noError(FormValidator.getValidator('realname')($scope.user.realname))) {
               return Toaster.make('You must verify the realname you entered, read the hints in red', 'profile-form');
            } else {
               payload.realname = $scope.user.realname;
            }
         }


         // Verifying website if provided
         if ($scope.user.website && $scope.user.website.length > 0 && $scope.user.website != $scope.original_user.website) {
            if (!FormValidator.noError(FormValidator.getValidator('website')($scope.user.website))) {
               return Toaster.make('You must verify the website you entered, read the hints in red', 'profile-form');
            } else {
               payload.website = $scope.user.website;
            }
         }

         if (!FormValidator.payloadEmpty(payload)) {
           $http({
              method: 'PUT',
              url: API_URL + '/user',
              data: payload
           }).success(function(data) {
              $scope.password = '';
              $scope.password_repeat = '';
              $scope.user = data;
              $scope.original_user = data;
              Toaster.make('Your profile was correctly updated according your desires', 'profile-form');
           });
         } else {
            Toaster.make('Your profile is already saved with those values', 'profile-form');
         }
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
       * scope method to open the "link an account"
       * dialog
       */
      $scope.openClaimAuthorshipDialog = function(ev) {
        $mdDialog.show({
          controller: ClaimAuthorshipDialogController,
          templateUrl: 'views/claimauthorship.html',
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

      function ClaimAuthorshipDialogController (API_URL, $scope, $http) {
         /**
          * scope method to actually link the account
          */
         $scope.claim = function() {
            console.log('claim');
            $http({
               method: 'POST',
               url: API_URL + '/claimauthorship',
               data: {
                  author: $scope.author
               }
            })
         };

         /**
          * scope method to close the $mdDialog
          */
         $scope.close = function() {
            $mdDialog.hide();
         };

         /**
          * Fetch list of current external accounts
          * when controller loads
          */
         // $http({
         //    method: "GET",
         //    url: API_URL + '/user/external_accounts'
         // }).success(function(data) {
         //    $scope.external_accounts = data;
         // });
      }
  });