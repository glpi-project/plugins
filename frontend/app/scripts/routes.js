'use strict';

angular
   .module('frontendApp')
   // Configuration of states in router
   .config(function($stateProvider, $urlRouterProvider) {

      // For any unmatched url, redirect to /
      $urlRouterProvider.otherwise("/");
      $stateProvider
         .state('featured', {
            url: "/",
            templateUrl: "views/featured.html",
            controller: 'FeaturedCtrl'
         })
         .state('all', {
            url: "/plugins",
            templateUrl: 'views/plugin_list.html',
            controller: 'AllCtrl'
         })
         .state('all_page', {
            url: "/plugins/:page",
            templateUrl: 'views/plugin_list.html',
            controller: 'AllCtrl'
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
         .state('tags', {
            url: '/tags',
            templateUrl: "views/tags.html",
            controller: "TagsCtrl"
         })
         .state('tag', {
            url: '/tag/:key',
            templateUrl: "views/plugin_list.html",
            controller: "TagCtrl"
         })
         .state('authors', {
            url: '/authors',
            templateUrl: 'views/authors.html',
            controller: 'AuthorsCtrl'
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