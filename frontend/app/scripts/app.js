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
    'angular-loading-bar',
    'ngAnimate',
    'ui.router',
    'ngMaterial',
    'vcRecaptcha',
    'hljs',
    'ngSanitize'
  ])

  // Constant to set API url
  .constant('API_URL', API_URL)
  .constant('RECAPTCHA_PUBLIC_KEY', RECAPTCHA_PUBLIC_KEY)

  // Determining current language
  .run(function() {
    var langs = ['en', 'fr'];

    if (localStorage.getItem('lang') === null) {
      var lang = navigator.language.split('-')[0];
      if (langs.indexOf(lang) > 0) {
        localStorage.setItem('lang', lang);
      } else {
        localStorage.setItem('lang', 'en');
      }
    }
  })

  .config(function($httpProvider) {
    $httpProvider.defaults.headers.common['X-Lang'] = localStorage.getItem('lang');
  })

  // Associating empty arrays to
  // placeholders in $rootScope
  // that are going to be used
  // by home.js
  .run(function($rootScope) {
      $rootScope.trending = [];
      $rootScope.new = [];
      $rootScope.popular = [];
      $rootScope.updated = [];
      $rootScope.tags = [];
      $rootScope.authors = [];
  })

  // Configuration of states in router
  .config(function($stateProvider, $urlRouterProvider, $mdThemingProvider) {

     // Define colors of application, from material angular
     $mdThemingProvider.theme('default')
       .primaryPalette('red', { 'default': '200' })
       .accentPalette('orange');

     // For any unmatched url, redirect to /
     $urlRouterProvider.otherwise("/");

     $stateProvider
       .state('home', {
         url: "/",
         templateUrl: "views/home.html",
         controller: 'HomeCtrl'
       })
       .state('plugin', {
         url: '/plugin/:key',
         templateUrl: 'views/plugin.html',
         controller: 'PluginCtrl'
       })
       .state('search', {
         url: '/search/:val',
         templateUrl: "views/plugin_list.html",
         controller: "SearchCtrl"
       })
       .state('tag', {
        url: '/tag/:key',
        templateUrl: "views/plugin_list.html",
        controller: "TagCtrl"
       })
       .state('author', {
        url: '/author/:id',
        templateUrl: 'views/author.html',
        controller: 'AuthorCtrl'
       })
       .state('author_plugins', {
        url: '/author/:id/plugin',
        templateUrl: "views/plugin_list.html",
        controller: "AuthorPluginsCtrl"
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
