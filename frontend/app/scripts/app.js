'use strict';

/**
 * @ngdoc overview
 * @name frontendApp
 * @description
 * # frontendApp
 *
 * Main module of the application.
 */
angular
  .module('frontendApp', [
    'ngAnimate',
    'ui.router',
    'ngMaterial'
  ])
  .constant('API_URL', 'http://glpiplugindirectory/api');
