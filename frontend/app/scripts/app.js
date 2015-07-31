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
    'hljs'
  ])

  // Constant to set API url
  .constant('API_URL', API_URL)

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

  // http://stackoverflow.com/questions/12111936/angularjs-performs-an-options-http-request-for-a-cross-origin-resource
  .config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.headers.common = {};
    $httpProvider.defaults.headers.post = {};
    $httpProvider.defaults.headers.put = {};
    $httpProvider.defaults.headers.patch = {};
  }])

  // Configuration of states in router
  .config(function($stateProvider, $urlRouterProvider, $mdThemingProvider) {

     // Define colors of application, from material angular
     $mdThemingProvider.theme('default')
       .primaryPalette('red', { 'default': '200' })
       .accentPalette('orange');

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
