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

      $scope.openDeleteAccountDialog = function(ev) {
         $mdDialog.show({
            controller: DeleteAccountDialogController,
            templateUrl: 'views/deleteaccount.html',
            parent: angular.element(document.body),
            targetEvent: ev,
            clickOutsideToClose: true
         });
      };

      function DeleteAccountDialogController($scope, Auth, $state, $rootScope) {
         $scope.password_confirmation = '';

         $scope.confirmDeletion = function() {
            $http({
               method: 'POST',
               url: API_URL+'/user/delete',
               data: {
                  password: $scope.password_confirmation
               }
            }).then(function() {
               Auth.loginAttempt({
                  anonymous: true
               });
               Toaster.make('You got rid of your GLPi Plugins Account. Hope to see you back one day !');
               $rootScope.$watch('authed', function(a) {
                  $mdDialog.hide();
                  if (!a) {
                     $state.go('featured');
                  }
               });
            }, function(resp) {
               if (resp.data.error === 'INVALID_CREDENTIALS' ||
                   resp.data.error === 'INVALID_FIELD(field=password)') {
                  Toaster.make('The password you entered is not correct, please try again.');
                  $mdDialog.hide();
               }
            });
         };

         $scope.close = function() {
            $mdDialog.hide();
         };
      }

      /**
       * "Link an Account" controller
       */
      function LinkAccountDialogController (API_URL, $scope, $http) {
         $scope.external_accounts = {};

         /**
          * Fetch list of current external accounts
          * when controller loads
          */
          var grabAccounts = function() {
            $http({
               method: "GET",
               url: API_URL + '/user/external_accounts'
            }).success(function(data) {
               $scope.external_accounts = data;
            });
          };
          grabAccounts();

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
         $scope.unlinkAccount = function(ev, external_account) {
            var confirm = $mdDialog.confirm()
                                 .title('Deletion of your '+external_account.service+' account')
                                 .content('You decided to unlink your '+external_account.service+' external social account #'+external_account.external_user_id+' from your glpi plugins account. Are you certain ?')
                                 .ariaLabel('external account unlinking')
                                 .targetEvent(ev)
                                 .ok('Please')
                                 .cancel('I changed my mind');
            $mdDialog.show(confirm).then(function() {
               $http({
                  method: 'DELETE',
                  url: API_URL + '/user/external_accounts/'+external_account.id
               }).then(function() {
                  Toaster.make('You unlinked your external account');
               }, function(resp) {
                  switch (resp.data.error) {
                     case 'NO_CREDENTIALS_LEFT':
                        $mdDialog.show(
                           $mdDialog.alert()
                             .parent(angular.element(document.querySelector('body')))
                             .clickOutsideToClose(true)
                             .title('You cannot do that')
                             .content('It appears that you never decided to have a password on GLPi Plugins. '+
                                      'This external account is the only way for you to login on GLPi Plugins, as it is the only one left. '+
                                      'If you really want to unlink that account, your must set a password first in your panel')
                             .ariaLabel('cannot unlink because no credentials left')
                             .ok('Got it!')
                        );
                        break;
                  }
               });
            });
         };
      }

      /**
       * "Claim an Authorship" controller
       */
      function ClaimAuthorshipDialogController (API_URL, $scope, $http, RECAPTCHA_PUBLIC_KEY, vcRecaptchaService, Toaster, $mdDialog) {
         $scope.recaptcha_key = RECAPTCHA_PUBLIC_KEY;
         $scope.recaptcha_response = null;
         $scope.recaptacha_widgetId = null;

         $scope.setResponse = function(response) {
            $scope.recaptcha_response = response;
         };
         $scope.setWidgetId = function(widgetId) {
            $scope.recaptcha_widgetId = widgetId;
         };
         $scope.cbExpiration = function() {
            $mdToast.show($mdToast.simple()
               .capsule(true)
               .content('Captcha expired, please select "I\'m not a robot" again')
               .position('top'));
            $scope.recaptcha_response = null;
         };

         /**
          * scope method to actually link the account
          */
         $scope.claim = function() {
            $http({
               method: 'POST',
               url: API_URL + '/claimauthorship',
               data: {
                  author: $scope.author,
                  recaptcha_response: $scope.recaptcha_response
               }
            }).then(function(resp) {
               $mdDialog.hide();
               Toaster.make('We ackownledged your request', 'body');
            }, function(resp) {
               vcRecaptchaService.reload($scope.widgetId);
               if (/^RESOURCE_NOT_FOUND\(type=Author/.exec(resp.data.error)) {
                  // @todo @refactor_wished
                  // should have a angular component to parse the error
                  // codes returned by the
                  // server, but it is actually out of specification
                  // and we need to release now. this is an idea for
                  // upcoming refactorings.
                  $mdDialog.show(
                     $mdDialog.alert()
                        .parent(angular.element(document.querySelector('body')))
                        .clickOutsideToClose(true)
                        .title('Wrong spelling, Author not found')
                        .content('We never heard of '+$scope.author+', please verify the exact spelling between <author> and </author> in the XML file, and try again.')
                        .ariaLabel('author not found while authorship claiming')
                        .ok('I\'ll sure do.')
                  );
               }
            });
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