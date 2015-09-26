'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SignupCtrl
 * @description
 * # SignupCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('SignupCtrl', function (API_URL, $scope, $http, $window, Auth, FormValidator, Toaster, $state) {
      $scope.user = {};
      $scope.password = '';
      $scope.password_repeat = '';

      $scope.form_errors = {
         username: {
              tooshort: false,
              toolong: false,
              badcharacters: false,
              required: false
         },
         realname: {
               tooshort: false,
               toolong: false
         },
         website: {
            required: false,
            invalid: false
         },
         email: {
            tooshort: false,
            toolong: false,
            badcharacters: false,
            required: false
         },
         password: {
             required: false,
             tooshort: false,
             toolong: false,
             different: false
         }
      };

      // Wrap testing of password and repetition in a single anonymous function
      var testPassword = function() {
         $scope.form_errors.password = FormValidator.getValidator('password')($scope.password, $scope.password_repeat, true);
      };
      $scope.$watch('password', testPassword);
      $scope.$watch('password_repeat', testPassword);

      // Verifying user.realname on keydown
      $scope.$watch('user.username', function() {
         var default_errors = {
            tooshort: false,
            toolong: false,
            badcharacters: false,
            required: false
         };
         var username = typeof $scope.user.username == 'string' ? $scope.user.username : '';
         $scope.form_errors.username = FormValidator.getValidator('username')(username, true);
      });

      // Verifying user.realname on keydown
      $scope.$watch('user.website', function() {
         var default_errors = {
            invalid: false
         };
         var website = typeof $scope.user.website == 'string' ? $scope.user.website : '';
         $scope.form_errors.website = FormValidator.getValidator('website')(website);
      });

      // Verifying user.realname on keydown
      $scope.$watch('user.realname', function() {
         var default_errors = {
            tooshort: false,
            toolong: false
         };
         $scope.form_errors.realname = (typeof $scope.user.realname === 'undefined' ?
                                        default_errors :
                                          ($scope.user.realname.length > 0 ?
                                          FormValidator.getValidator('realname')($scope.user.realname) :
                                          default_errors)
                                        );
      });

      // Verifying user.realname on keydown
      $scope.$watch('user.email', function() {
         var default_errors = {
            tooshort: false,
            toolong: false,
            badcharacters: false,
            required: false
         };
         var email = typeof $scope.user.email == 'string' ? $scope.user.email : '';
         $scope.form_errors.email = FormValidator.getValidator('email')(email, true);
      });


      $scope.authenticate = function(provider) {
         Auth.linkAccount(provider);
      };

      $scope.signUp = function() {
         var payload = {};

         // Verifying username
         if ($scope.user.username && $scope.user.username.length > 0) {
            if (!FormValidator.noError(FormValidator.getValidator('username')($scope.user.username))) {
               return Toaster.make('You must verify the username you entered, read the hints in red', 'signup-form');
            } else {
               payload.username = $scope.user.username;
            }
         } else {
          return Toaster.make('You must provide at least a username, an email and a password', 'signup-form');
         }

         // Verifying password
         if (!FormValidator.noError(FormValidator.getValidator('password')($scope.password,
                                                                           $scope.password_repeat,
                                                                           true))) {
            return Toaster.make('You must verify the password you entered, read the hints in red', 'signup-form');
         } else {
            payload.password = $scope.password;
         }

         // Verifying email
         if ($scope.user.email && $scope.user.email.length > 0) {
            if (!FormValidator.noError(FormValidator.getValidator('email')($scope.user.email))) {
               return Toaster.make('You must verify the email you entered, read the hints in red', 'signup-form');
            } else {
               payload.email = $scope.user.email;
            }
         } else {
          return Toaster.make('You must provide at least a username, an email and a password', 'signup-form');
         }

         // Verifying realname if provided
         if ($scope.user.realname && $scope.user.realname.length > 0) {
            if (!FormValidator.noError(FormValidator.getValidator('realname')($scope.user.realname))) {
               return Toaster.make('You must verify the realname you entered, read the hints in red', 'profile-form');
            } else {
               payload.realname = $scope.user.realname;
            }
         }

         // Verifying website if provided
         if ($scope.user.website && $scope.user.website.length > 0) {
            if (!FormValidator.noError(FormValidator.getValidator('website')($scope.user.website))) {
               return Toaster.make('You must verify the website you entered, read the hints in red', 'profile-form');
            } else {
               payload.website = $scope.user.website;
            }
         }

         if (!FormValidator.payloadEmpty(payload)) {
            if (payload.email &&
                payload.password &&
                payload.username) {
              $http({
                 method: 'POST',
                 url: API_URL + '/user',
                 data: payload
              }).success(function(data) {
                 $state.go('featured');
                 Toaster.make('You now need to check your mailbox in order to validate your email');
              }).error(function(data) {
                 if (/^UNAVAILABLE_NAME/.exec(data.error)) {
                    Toaster.make('This username is already taken', 'signup-form', 'top');
                 }
              });
            } else {
                Toaster.make('You need at least a username, an email and a password', 'signup-form');
            }

         } else {
             Toaster.make('You need at least a username, an email and a password', 'signup-form');
         }
      };
  });
