'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:ApikeysCtrl
 * @description
 * # ApikeysCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('ApiKeysCtrl', function (API_URL, $rootScope, $state, $http, $scope, $mdDialog) {
      if (!$rootScope.authed) {
         return $state.go('featured');
      }

      $scope.appName = '';
      $scope.homepage = '';
      $scope.description = '';

      $http({
         method: 'GET',
         url: API_URL + '/user/apps'
      }).success(function(data) {
         $scope.apps = data;
      });

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
      function AppEditDialogController (API_URL, $scope, $http) {
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
