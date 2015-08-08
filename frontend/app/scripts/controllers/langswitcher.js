'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:LangswitcherCtrl
 * @description
 * # LangswitcherCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('LangSwitcherCtrl', function ($scope, $translate) {
    $scope.setLanguage = function(lang) {
        $translate.use(lang);
        moment.locale(lang);
        localStorage.setItem('lang', lang);
    };
  });
