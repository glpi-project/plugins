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

  // Determining current language
  .run(function() {
    var langs = ['en', 'fr'];

    if (localStorage.getItem('lang') == null) {
      var lang = navigator.language.split('-')[0];
      if (langs.indexOf(lang) > 0) {
        localStorage.setItem('lang', lang);
      } else {
        localStorage.setItem('lang', 'en');
      }
    }
  })

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
       // plugin State
       .state('plugin', {
         url: '/plugin/:id',
         templateUrl: 'views/plugin.html',
         controller: 'PluginCtrl'
       })
       // search State
       .state('search', {
         url: '/search/:val',
         templateUrl: "views/search.html",
         controller: "SearchCtrl"
       })
       // submit State
       .state('submit', {
       	 url: '/submit',
       	 templateUrl: "views/submit.html",
       	 controller: "SubmitCtrl"
       })
       // contact State
       .state('contact', {
       	 url: '/contact',
       	 templateUrl: 'views/contact.html',
       	 controller: 'ContactCtrl'
       });
   });
