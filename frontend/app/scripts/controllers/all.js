'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:AllCtrl
 * @description
 * # AllCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('AllCtrl', function (API_URL, $http, $scope) {
    var grabAllPlugins = function() {
        $http({
            method: "GET",
            url: API_URL + '/plugin'
            })
            .success(function(data) {
                $scope.results = data;
            });
    };

    $scope.results = [];

    grabAllPlugins();
    $scope.$on('languageChange', grabAllPlugins);
  });
