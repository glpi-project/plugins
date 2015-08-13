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
      'ngSanitize',
      'pascalprecht.translate',
      'jkuri.gallery',
      'btford.markdown'
   ])

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

   .config(function($translateProvider) {
      $translateProvider.useSanitizeValueStrategy('escape');
      $translateProvider.determinePreferredLanguage(function() {
         return localStorage.getItem('lang');
      });
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
   });