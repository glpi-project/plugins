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
  .controller('PluginpanelCtrl', function (API_URL, $scope, $rootScope, $state, $stateParams, $http, Toaster) {
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

      $scope.updateSettings = function() {
         $http({
            method: 'POST',
            url: API_URL + '/panel/plugin/'+$stateParams.key,
            data: {
               xml_url: $scope.plugin.card.xml_url
            }
         });
      };
  });
