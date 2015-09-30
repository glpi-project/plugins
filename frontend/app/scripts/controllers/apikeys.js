'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:ApikeysCtrl
 * @description
 * # ApikeysCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('ApiKeysCtrl', function (API_URL, $rootScope, $state, $http, $scope, $mdDialog, Toaster) {
      if (!$rootScope.authed) {
         return $state.go('featured');
      }

      $scope.appName = '';
      $scope.homepage = '';
      $scope.description = '';

      var grabApps = function() {
         $http({
            method: 'GET',
            url: API_URL + '/user/apps'
         }).success(function(data) {
            $scope.apps = data;
         });
      };
      grabApps();

      /**
       * scope method to open the "link an account"
       * dialog
       */
      var currentAppEdited = null;
      $scope.openAppEditDialog = function(ev, id) {
        currentAppEdited = id;
        $mdDialog.show({
          controller: AppEditDialogController,
          templateUrl: 'views/appedit.html',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutSideToClose: true
        });
      };

      $scope.openApiKeysDialog = function(ev, id) {
        currentAppEdited = id;
        $mdDialog.show({
          controller: AppEditDialogController,
          templateUrl: 'views/viewapikey.html',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutSideToClose: true
        });
      };

      /**
       * "Link an Account" controller
       */
      function AppEditDialogController (API_URL, $scope, $http,
                                        vcRecaptchaService,
                                        RECAPTCHA_PUBLIC_KEY,
                                        FormValidator) {
         // $scope.recaptcha_key = RECAPTCHA_PUBLIC_KEY;
         // $scope.recaptcha_response = null;
         // $scope.recaptcha_widgetId = null;

         // $scope.setResponse = function(response) {
         //    $scope.recaptcha_response = response;
         // };
         // $scope.setWidgetId = function(widgetId) {
         //    $scope.recaptcha_widgetId = widgetId;
         // };
         // $scope.cbExpiration = function() {
         //    Toaster.make('Captcha expired, please select "I\'m not a robot" again');
         //    $scope.recaptcha_response = null;
         // };

         $scope.app = {};
         $scope.form_errors = {
            name: {
               tooshort: false,
               toolong: false
            },
            homepage_url: {
               invalid: false
            },
            description: {
               toolong: false
            }
         };

         $scope.$watch('app.name', function() {
            if (!$scope.app.name) return;
            $scope.form_errors.name = FormValidator.getValidator('appname')($scope.app.name);
         });
         $scope.$watch('app.homepage_url', function() {
            if (!$scope.app.homepage_url) return;
            $scope.form_errors.homepage_url = FormValidator.getValidator('website')($scope.app.homepage_url);
         });
         $scope.$watch('app.description', function() {
            if (!$scope.app.description) return;
            $scope.form_errors.description = FormValidator.getValidator('appdescription')($scope.app.description);
         });

         $scope.save = function() {
            var payload = {};

            if ($scope.original_app.name != $scope.app.name) {
               payload.name = $scope.app.name;
            }

            if ($scope.original_app.homepage_url != $scope.app.homepage_url) {
               payload.homepage_url = $scope.app.homepage_url;
            }

            if ($scope.original_app.description != $scope.app.description) {
               payload.description = $scope.app.description;
            }

            for (var field in $scope.form_errors) {
               for (var err_type in $scope.form_errors[field]) {
                  if ($scope.form_errors[field][err_type]) {
                     return Toaster.make('You have an error, please read the hints');
                  }
               }
            }

            if (FormValidator.payloadEmpty(payload)) {
               return $mdDialog.hide();
            }

            $http({
               method: 'PUT',
               url: API_URL + '/user/apps/'+$scope.app.id,
               data: payload
            }).then(function() {
               Toaster.make('You modified your app settings !');
               $mdDialog.hide();
               grabApps();
            });
         };

         $scope.delete = function(ev) {
            var confirm = $mdDialog.confirm()
                                 .title('Deletion of "'+$scope.app.name+'"')
                                 .content('Are you certain you want to delete this API Key ?')
                                 .ariaLabel('api key deletion')
                                 .targetEvent(ev)
                                 .ok('Please')
                                 .cancel('I changed my mind');
            $mdDialog.show(confirm).then(function() {
               $http({
                  method: 'DELETE',
                  url: API_URL+'/user/apps/'+$scope.app.id
               }).then(function() {
                  Toaster.make('You deleted the concerned app');
                  grabApps();
               });
            });
         };

         /**
          * scope method to close the $mdDialog
          */
         $scope.close = function() {
            $mdDialog.hide();
         };

         $http({
            method: 'GET',
            url: API_URL + '/user/apps/' + currentAppEdited
         }).success(function(data) {
            $scope.app = data;
            $scope.original_app = jQuery.extend({}, data);
         });
      }

      $scope.newApp = function() {
         $http({
            method: 'POST',
            url: API_URL + '/user/apps',
            data: {
               name: $scope.appName,
               homepage: $scope.homepage,
               description: $scope.description
            }
         }).success(function() {
            $scope.appName = '';
            $scope.homepage = '';
            $scope.description = '';
            $http({
               method: 'GET',
               url: API_URL + '/user/apps'
            }).success(function(data) {
               $scope.apps = data;
               Toaster.make('Your app was successfully created');
            });
         });
      };
  });
