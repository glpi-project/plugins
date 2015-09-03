'use strict';

/**
 * @ngdoc service
 * @name frontendApp.auth
 * @description
 * # auth
 * Provider in the frontendApp.
 */
angular.module('frontendApp')
  .provider('Auth', function ($httpProvider, $injector, API_URL, WEBAPP_SECRET) {
    var rootScope, mdToast, state, timeout, http;
    var AuthManager = function() {};

    AuthManager.prototype.setToken = function(t, expires_in, auth) {
      if (typeof(auth) === 'undefined') {
         var auth = true;
      }
      localStorage.setItem('access_token', t);
      localStorage.setItem('access_token_expires_in', expires_in);
      $httpProvider.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('access_token');
      if (auth) {
         localStorage.setItem('authed', true);
         rootScope.authed = true;
         var toast = mdToast.simple()
             .capsule(true)
             .content("You are now successfully logged in")
             .position('top');
         toast._options.parent = angular.element('#signin');
         mdToast.show(toast);
         timeout(function() {
           state.go('featured');
         }, 1500);
      } else {
         timeout(function() {
           state.go('featured');
         }, 400);
      }
    };

    AuthManager.prototype.getToken = function() {
      return localStorage.getItem('access_token');
    };

    AuthManager.prototype.destroyToken = function() {
      localStorage.removeItem('access_token');
      localStorage.removeItem('access_token_expires_in');
      localStorage.removeItem('authed');
      delete $httpProvider.defaults.headers.common['Authorization'];
      rootScope.authed = false;
      var toast = mdToast.simple()
          .capsule(true)
          .content("You are now disconnected")
          .position('top');
      toast._options.parent =  angular.element('body');
      mdToast.show(toast);
      this.getAnonymousToken();
    };

    AuthManager.prototype.loginAttempt = function(options) {
         var self = this, param = {};

         if (typeof(options.anonymous) === 'undefined') {
            options.anonymous = false;
         }
         if (!options.anonymous) {
            if (typeof(options.login) != 'string' ||
                typeof(options.password) != 'string') {
               return false;
            }
         }

         param.client_id = "webapp";
         param.scope = 'plugins plugins:search plugin:card plugin:star plugin:submit plugin:download tags tag authors author version message';

         if (!options.anonymous) {
            param.grant_type = "password";
            param.username = options.login;
            param.password = options.password;
            var auth = true;
         } else {
            param.grant_type = "client_credentials";
            param.client_secret = WEBAPP_SECRET;
            var auth = false;
         }

         return http({
           method: "POST",
           url: API_URL + "/oauth/authorize",
           // Making use of jQuery.param
           // to serialize parameters
           data: jQuery.param(param),
           // Those parameters are passed via
           // an urlencoded string
           headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
           }
         }).success(function(data) {
           self.setToken(data.access_token, data.expires_in, auth);
         });

    };

    AuthManager.prototype.getAnonymousToken = function() {
      this.loginAttempt({
         anonymous: true
      });
    };

    this.$get = function ($injector) {
      rootScope = $injector.get('$rootScope');
      mdToast = $injector.get('$mdToast');
      state = $injector.get('$state');
      timeout = $injector.get('$timeout');
      http = $injector.get('$http');
      return new AuthManager();
    };
  })

  .config(function($httpProvider) {
    if (localStorage.getItem('access_token') !== null) {
      $httpProvider.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('access_token');
    }
  })

  .run(function($rootScope, Auth) {
    $rootScope.authed = (localStorage.getItem('authed') === null) ? false : true;

    if (!$rootScope.authed) {
      Auth.getAnonymousToken();
    }
  });
