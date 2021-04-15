'use strict';

angular
   .module('frontendApp')
   // Configuration of states in router
   .config(function($stateProvider, $urlRouterProvider) {

      // For any unmatched url, redirect to /
      $urlRouterProvider.otherwise("/");
      $stateProvider
         .state('panel', {
            url: "/panel",
            templateUrl: "views/panel.html",
            controller: "PanelCtrl"
         })
         .state('pluginpanel', {
            url: "/panel/plugin/:key",
            controller: "PluginpanelCtrl",
            templateUrl: "views/pluginpanel.html",
            params: {
               refreshXML: false,
               managePermissions: false
            }
         })
         .state('apikeys', {
            url: '/panel/apikeys',
            templateUrl: 'views/apikeys.html',
            controller: 'ApiKeysCtrl'
         })
         .state('notifications', {
            url: '/panel/notifications',
            templateUrl: 'views/notifications.html',
            controller: 'NotificationsCtrl'
         })
         .state('signup', {
            url: "/signup",
            templateUrl: "views/signup.html",
            controller: "SignupCtrl"
         })
         .state('signin', {
            url: "/signin",
            templateUrl: "views/signin.html",
            controller: "SigninCtrl",
            controllerAs: "ctrl"
         })
         .state('validatemail', {
            url: "/validatemail/:token",
            controller: "ValidatemailCtrl"
         })
         .state('home', {
            url: "/",
            templateUrl: "views/home.html",
            controller: 'HomeCtrl'
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
         .state('search_page', {
            url: '/search/:val/:page',
            templateUrl: "views/plugin_list.html",
            controller: "SearchCtrl"
         })
         .state('version', {
            url: '/version/:version/plugins',
            templateUrl: "views/plugin_list.html",
            controller: "VersionCtrl"
         })
         .state('tags', {
            url: '/tags',
            templateUrl: "views/tags.html",
            controller: "TagsCtrl"
         })
         .state('tags_page', {
            url: '/tags/:page',
            templateUrl: "views/tags.html",
            controller: "TagsCtrl"
         })
         .state('tag', {
            url: '/tag/:key',
            templateUrl: "views/plugin_list.html",
            controller: "TagCtrl"
         })
         .state('tag_page', {
            url: '/tag/:key/:page',
            templateUrl: "views/plugin_list.html",
            controller: "TagCtrl"
         })
         .state('authors', {
            url: '/authors',
            templateUrl: 'views/authors.html',
            controller: 'AuthorsCtrl'
         })
         .state('authors_page', {
            url: '/authors/:page',
            templateUrl: 'views/authors.html',
            controller: 'AuthorsCtrl'
         })
         .state('author', {
            url: '/author/:id',
            templateUrl: 'views/author.html',
            controller: 'AuthorCtrl'
         })
         .state('author_plugins', {
            url: '/author/:id/plugins',
            templateUrl: "views/plugin_list.html",
            controller: "AuthorPluginsCtrl"
         })
         .state('author_plugins_page', {
            url: '/author/:id/plugins/:page',
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
         })
         .state('finishactivateaccount', {
            url: '/finishactivateaccount',
            templateUrl: 'views/finishactivateaccount.html',
            controller: 'FinishActivateAccountCtrl'
         })
         .state('developerguides', {
            url: '/developer/guides',
            templateUrl: 'docs/oauth2.html',
            controller: 'DeveloperGuidesCtrl'
         })
         .state('resetpassword', {
            url: '/resetpassword/:token',
            templateUrl: 'views/reset_password.html',
            controller: 'ResetpasswordCtrl'
         });
   });
