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

  // Constant to set API url
  .constant('API_URL', 'http://glpiplugindirectory/api')

  // Configuration of states in router
  .config(function($stateProvider, $urlRouterProvider) {
     // For any unmatched url, redirect to /
     $urlRouterProvider.otherwise("/");

     $stateProvider
       // home State
       .state('home', {
         url: "/",
         templateUrl: "views/home.html",
         controller: 'HomeCtrl'
       })
       // search State
       .state('search', {
         url: '/search/:val',
         templateUrl: "views/search.html",
         controller: "SearchCtrl"
       })
       .state('submit', {
       	 url: '/submit',
       	 templateUrl: "views/submit.html",
       	 controller: "SubmitCtrl"
       })
       .state('contact', {
       	 url: '/contact',
       	 templateUrl: 'views/contact.html',
       	 controller: 'ContactCtrl'
       });
   });
