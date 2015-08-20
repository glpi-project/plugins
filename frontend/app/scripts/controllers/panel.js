'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PanelCtrl
 * @description
 * # PanelCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('PanelCtrl', function () {
    this.awesomeThings = [
      'HTML5 Boilerplate',
      'AngularJS',
      'Karma'
    ];
    console.log('Hello, this is the panel');
  });
