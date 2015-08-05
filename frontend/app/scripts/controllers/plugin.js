'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PluginCtrl
 * @description
 * # PluginCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')

  .controller('PluginCtrl', function (API_URL, $scope, $http, $stateParams, $window, inlineAuthors) {
    $scope.plugin = {
      authors: {}
    };

    $scope.ratePlugin = function(note) {
      $http({
        method: 'POST',
        url: API_URL + '/plugin/star',
        data: {
          note: note,
          plugin_id: $scope.plugin.id
        }
      })
      .success(function(data) {
        $scope.plugin.note = data.new_average;
      });
    };

    $scope.inlineAuthors = inlineAuthors;

    $scope.download = function() {
      $window.location.href = API_URL + '/plugin/'+$scope.plugin.key+'/download';
    };

    $http({
      method: 'GET',
      url: API_URL + '/plugin/' + $stateParams.key
    })
    .success(function(data) {
      $scope.plugin = data;
    });
  });