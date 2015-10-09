'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PluginpanelCtrl
 * @description
 * # PluginpanelCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('PluginpanelCtrl', function (API_URL, $scope, $rootScope, $state, $stateParams, $http) {
      // Redirects to /featured if not authed
      if (!$rootScope.authed) {
         $state.go('featured');
      }

      // Note this is not the strict plugin object
      // but a special object which is only for
      // the author panel. the plugin will be at
      // $scope.plugin.card after $http has .then()'ed.
      $scope.plugin = {};

      $http({
         method: 'GET',
         url: API_URL + '/panel/plugin/'+$stateParams.key
      }).then(function(resp) {
         $scope.plugin = resp.data;
      });
  });
