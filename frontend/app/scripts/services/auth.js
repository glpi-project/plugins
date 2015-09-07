'use strict';

/**
 * @ngdoc service
 * @name frontendApp.auth
 * @description
 * # auth
 * Provider in the frontendApp.
 */
angular.module('frontendApp')
  .provider('Auth', function ($httpProvider, $authProvider, $injector, API_URL, WEBAPP_SECRET) {
    var rootScope, mdToast, state, timeout, http;
    var AuthManager = function() {};

    AuthManager.prototype.addAccessTokenToRedirectUris = function(access_token) {
      var addAccessToken = function(url, accessToken) {
        if (url.split('%3F').length > 1) {
          url = url.split('%3F')[0];
        }
        url += '%3Faccess_token%3D' + accessToken;
        return url;
      };

      // Github
      var githubConfig = $authProvider.github();
      $authProvider.github({
        redirectUri: addAccessToken(githubConfig.redirectUri, access_token)
      });
    };

    AuthManager.prototype.removeAccessTokenFromRedirectUris = function() {
      var removeAccesstoken = function(url) {
        if (url.split('%3F').length > 1) {
          url = url.split('%3F')[0];
        }
        return url;
      };

      // Github
      var githubConfig = $authProvider.github();
      $authProvider.github({
        redirectUri: removeAccesstoken(githubConfig.redirectUri)
      });
    };

    /**
     * This method sets the current token
     */
    AuthManager.prototype.setToken = function(t, expires_in, auth) {
      // Bet we want to authenticate by default
      if (typeof(auth) === 'undefined') {
         var auth = true;
      }

      // Going to set the token anyway
      localStorage.setItem('access_token', t);
      localStorage.setItem('access_token_expires_in', expires_in);
      $httpProvider.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('access_token');

      if (auth) {
         // if real auth (with credentials) is requested,
         // we also set 'authed' in the localStorage
         localStorage.setItem('authed', true);
         // and authed in the $rootScope
         rootScope.authed = true;

         // Showing a toast
         var toast = mdToast.simple()
             .capsule(true)
             .content("You are now successfully logged in")
             .position('top');
         toast._options.parent = angular.element('#signin');
         mdToast.show(toast);

         this.addAccessTokenToRedirectUris(t);
         timeout(function() {
           state.go('featured');
         }, 1500);
      } else {
        state.go('featured', {}, {
          reload: true
        });
      }
    };

    /**
     * This methods returns the current token
     * or null (localStorage behaviour)
     */
    AuthManager.prototype.getToken = function() {
      return localStorage.getItem('access_token');
    };

    /**
     * This methods destroys the current token
     */
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
      this.removeAccessTokenFromRedirectUris();
    };

    /**
     * This methods is used to make a real login attempt
     * (auth attempt) via glpi-plugins account
     */
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

    /**
     * This method is used to request an anonymous token
     * for immediate usage of the API via the webapp
     */
    AuthManager.prototype.getAnonymousToken = function() {
      this.loginAttempt({
         anonymous: true
      });
    };

    this.$get = function ($injector) {
      // We need $injector to fetch some
      // angular components in this provider
      rootScope = $injector.get('$rootScope');
      mdToast = $injector.get('$mdToast');
      state = $injector.get('$state');
      timeout = $injector.get('$timeout');
      http = $injector.get('$http');

      // We'll get this AuthManager instance
      return new AuthManager();
    };
  })

  .config(function($httpProvider) {
    // if an access_token is set, we use it to provide
    // authorization token in headers, being it an anonymous
    // one or being it a real one
    if (localStorage.getItem('access_token') !== null) {
      $httpProvider.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('access_token');
    }
  })

  .run(function($rootScope, Auth) {
    // when the full js app loads, if we are declared as really authed
    // (user logged in with credentials or external account), we set
    // 'authed' in the rootScope
    $rootScope.authed = (localStorage.getItem('authed') === null) ? false : true;
    // at this point, if we don't have any access token,
    // we request an anonymous authorization token
    if (!localStorage.getItem('access_token')) {
      Auth.getAnonymousToken();
    } else if (localStorage.getItem('authed')) {
      Auth.addAccessTokenToRedirectUris(localStorage.getItem('access_token'));
    }
  });
