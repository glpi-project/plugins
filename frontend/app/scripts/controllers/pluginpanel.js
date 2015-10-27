'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PluginpanelCtrl
 * @description
 * Note: the Pluginpanel controller exposes
 *       a feature named "Author Panel",
 *       please don't be confused.
 *
 */
angular.module('frontendApp')
  .controller('PluginpanelCtrl', function (API_URL, $scope, $rootScope, $state,
                                           $stateParams, $http, Toaster, $mdDialog) {
      // Redirects to /featured if not authed
      if (!$rootScope.authed) {
         $state.go('featured');
      }

      // Note this is not the strict plugin object
      // but a special object which is only for
      // the author panel. the plugin will be at
      // $scope.plugin.card after $http has .then()'ed.
      $scope.plugin = {};

      /**
       * Fetching card of current user
       */
      var getUser = $http({
         method: 'GET',
         url: API_URL + '/user'
      }).then(function(resp) {
         $scope.author_id = resp.data.author_id;
      });

      // Fetching current plugin infos for the
      // author panel
      var getPlugin = $http({
         method: 'GET',
         url: API_URL + '/panel/plugin/'+$stateParams.key
      }).then(function(resp) {
         $scope.plugin = resp.data;
      }, function(resp) {
         if (resp.data.error == 'LACK_AUTHORSHIP') {
            Toaster.make('You\'re not author/contributor of that plugin');
            $state.go('featured');
         }
      });

      /**
       * Update the plugin settings on user click
       */
      $scope.updateSettings = function() {
         $http({
            method: 'POST',
            url: API_URL + '/panel/plugin/'+$stateParams.key,
            data: {
               xml_url: $scope.plugin.card.xml_url
            }
         }).then(function(resp) {
            Toaster.make('You plugin has been updated.');
         }, function(resp) {
            Toaster.make(resp.data.error);
         });
      };

      var pluginPanelScope = $scope;
      function UserPermissionsDialogController($scope, Auth, $state) {
         $scope.plugin = pluginPanelScope.plugin.card;

         $scope.close = function() {
            $mdDialog.hide();
         }
      }
      $scope.showUserPermissionsDialog = function(ev) {
         if (!$scope.plugin.card) {
            return;
         }
         $mdDialog.show({
            controller: UserPermissionsDialogController,
            templateUrl: 'views/pluginuserpermissions.html',
            parent: angular.element(document.body),
            targetEvent: ev,
            clickOutsideToClose: true
         });
      };
  });
