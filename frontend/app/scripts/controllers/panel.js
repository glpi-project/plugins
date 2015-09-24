'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PanelCtrl
 * @description
 * # PanelCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('PanelCtrl', function (API_URL, $http, $scope, $mdDialog, Auth, $state, Toaster) {
      var self = this;
      $scope.original_user = {};
      $scope.user = {};
      $scope.password = '';
      $scope.password_repêat = '';

      $scope.form = {
         username: {
           $error: {
              tooshort: false,
              toolong: false
           }
         },
         realname: {
            $error: {
               tooshort: false,
               toolong: false
            }
         },
         password: {
           $error: {
             tooshort: false,
             toolong: false,
             different: false
           }
         }
      };

      this.validateField = function(field) {
        if (field == 'password') {
          return function() {
            $scope.form.password.$error = {
              tooshort: false,
              toolong: false,
              different: false
            };
            if ($scope.password.length > 0) {
              if ($scope.password.length < 6) {
                $scope.form.password.$error.tooshort = true;
              }
              if ($scope.password.length > 26) {
                $scope.form.password.$error.toolong = true;
              }
              if ($scope.password != $scope.password_repeat) {
                $scope.form.password.$error.different = true;
              }
              for (var scope in $scope.form.password.$error) {
                if ($scope.form.password.$error[scope]) {
                  return false;
                }
              }
              return true;
            }
          };
        }

        else if (field == 'realname') {
          return function() {
            $scope.form.realname.$error = {
               tooshort: false,
               toolong: false
            };
            if ($scope.user.realname) {
               if ($scope.user.realname.length > 0) {
                  if ($scope.user.realname.length < 4) {
                     $scope.form.realname.$error.tooshort = true;
                  }
                  if ($scope.user.realname.length > 28) {
                     $scope.form.realname.$error.toolong = true;
                  }
               }
            }
            for (var scope in $scope.form.realname.$error) {
              if ($scope.form.realname.$error[scope]) {
                return false;
              }
            }
            return true;
          }
        }
      };

      $scope.$watch('password', this.validateField('password'));
      $scope.$watch('password_repeat', this.validateField('password'));
      $scope.$watch('user.realname', this.validateField('realname'));

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
         var go = false;

         if ($scope.user.realname.length > 0) {
          if (!self.validateField('realname')($scope.user.realname)){
             Toaster.make('You must verify the realname you entered, read the hints in red', 'profile-form');
             return;
          }
         }

         if ($scope.password.length > 0) {
          if (!self.validateField('password')($scope.password)){
            Toaster.make('You must verify the password you entered, read the hints in red', 'profile-form');
            return;
          }
          payload.password = $scope.password;
         }

         if ($scope.user.website != $scope.original_user.website) {
            payload.website = $scope.user.website;
         }

         if (payload.length > 0) {
           $http({
              method: 'PUT',
              url: API_URL + '/user',
              data: payload
           }).success(function(data) {
              $scope.password = '';
              $scope.password_repeat = '';
              if ($scope.user.website != $scope.original_user.website) {
                $scope.original_user.website = $scope.user.website;
              }
              if ($scope.user.realname != $scope.original_user.realname) {
                $scope.original_user.realname = $scope.user.realname;
              }
              Toaster.make('Your profile was correctly updated according your desires', 'profile-form');
           });
         } else {
          Toaster.make('Your profile was already saved with these settings', 'profile-form');
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
