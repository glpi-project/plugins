'use strict';

/**
 * @ngdoc service
 * @name frontendApp.auth
 * @description
 * # auth
 * Provider in the frontendApp.
 */
angular.module('frontendApp')
  .config(function($cookiesProvider) {
    $cookiesProvider.defaults = {
      domain: document.domain,
      path: '/'
    };
  })

  .provider('Auth', function ($httpProvider, $authProvider, $injector, API_URL,
                              GITHUB_CLIENT_ID, $provide) {
    var authManager, rootScope, mdToast, timeout, http, cookies, $window;
    var AuthManager = function() {};

    /**
     * This methods is used to make a real login attempt
     * (auth attempt) via glpi-plugins account
     */
    AuthManager.prototype.loginAttempt = function(options) {
         var self = this, param = {};
         var now = moment();

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
         param.scope = 'plugins plugins:search plugin:card plugin:star plugin:submit plugin:download tags tag authors author version message user user:externalaccounts user:apps';

         if (!options.anonymous) {
            param.grant_type = "password";
            param.username = options.login;
            param.password = options.password;
            var auth = true;
         } else {
            param.grant_type = "client_credentials";
            var auth = false;
         }

         var call =  http({
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
         });
         call.then(function(resp) {
            var data = resp.data;
            self.setToken(data.access_token, now.unix() + data.expires_in, (options.anonymous ? null : data.refresh_token), auth);
         });
         return call;
    };

    AuthManager.prototype.linkAccount = function(service, callback) {
      var redirect_uri = API_URL + '/oauth/associate/';
      var authorization_endpoint, scope;
      var now = moment();

      if (service == 'github') {
         redirect_uri += 'github';
         authorization_endpoint = 'https://github.com/login/oauth/authorize';
         scope = 'user user:email';
      }

      /**
       * If we are actually authenticated, we include the
       * the local access_token in the redirect_uri
       * so then, the oauth authorize callback
       * will know about authenticated user.
       * we need to use the state token one day.
       */
      if (localStorage.getItem('authed') &&
          localStorage.getItem('access_token')) {
         var access_token = localStorage.getItem('access_token');
         cookies.put('access_token', access_token, {
            path: '/'
         });
      }

      var url = authorization_endpoint + '?' + jQuery.param({
         client_id: GITHUB_CLIENT_ID,
         scope: scope
      });

      var authorizationRequestWindow = window.open(url, 'Associate your external account', {
         width: 400,
         height: 400,
         left: $window.screenX + (($window.outerWidth - 400) / 2),
         top: $window.screenY + (($window.outerHeight - 400) / 2.5)
       });

      var i = 0;
      var self = this;
      var pollPopupForToken = setInterval(function() {
         if (i == 300) {
            clearInterval(pollPopupForToken);
         }
         i++;

         try {
            var location = authorizationRequestWindow.location.href;
            if (location.split(API_URL).length > 1) {
               var evl = authorizationRequestWindow.addEventListener('message', function(e) {
                  authorizationRequestWindow.removeEventListener('message', evl);
                  var data = JSON.parse(e.data);
                  if (!data.error) {
                     self.setToken(data.access_token, now.unix() + data.access_token_expires_in, data.refresh_token, true);
                  } else {
                     // Showing a toast
                     var toast = mdToast.simple()
                         .capsule(true)
                         .content(data.error)
                         .position('top');
                     toast._options.parent = angular.element('body');
                     mdToast.show(toast);
                  }
                  authorizationRequestWindow.close();
               });
               clearInterval(pollPopupForToken);
            }
         }
         catch (e) {}
      }, 750);
    };

    /**
     * This method sets the current token
     */
    AuthManager.prototype.setToken = function(t, expires_at, refresh_token, auth) {
      // if bad token provided we don't move
      if (typeof t != 'string' || t.length != 40) {
         return;
      }

      // Bet we want to authenticate by default
      if (typeof(auth) === 'undefined') {
         var auth = true;
      }

      // Going to set the token anyway
      localStorage.setItem('access_token', t);
      if (refresh_token != null) {
        localStorage.setItem('refresh_token', refresh_token);
      }
      localStorage.setItem('access_token_expires_at', expires_at);
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
      localStorage.removeItem('refresh_token');
      localStorage.removeItem('access_token_expires_at');
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

    AuthManager.prototype.refreshToken = function(callback) {
      var now = moment();
      var promise = http({
        method: "POST",
        url: API_URL + '/oauth/authorize',
        data: jQuery.param({
          client_id: 'webapp',
          grant_type: 'refresh_token',
          refresh_token: localStorage.getItem('refresh_token'),
          scope: 'plugins plugins:search plugin:card plugin:star plugin:submit plugin:download tags tag authors author version message user user:externalaccounts user:apps'
        }),
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        }
      })
      promise.success(function(data) {
        if (data) {
          localStorage.setItem('access_token', data.access_token);
          localStorage.setItem('refresh_token', data.refresh_token);
          localStorage.setItem('access_token_expires_at', now.unix() + data.expires_in);
          $httpProvider.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('access_token');
        }
      });
      return promise;
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
      timeout = $injector.get('$timeout');
      http = $injector.get('$http');
      $window = $injector.get('$window');
      cookies = $injector.get('$cookies');

      // We'll get this AuthManager instance
      authManager = new AuthManager()
      return authManager;
    };

    // $provide.factory('AccessTokenHttpInterceptor', function($q) {
    //   var refreshAttempt = false;

    //   return {
    //     "responseError": function(response) {
    //       if (moment.unix(localStorage.getItem('access_token_expires_at')) - moment() < 0) {
    //         if (!refreshAttempt) {
    //           refreshAttempt = authManager.refreshToken()
    //                                       .success(function() {
    //                                         refreshAttempt = false;
    //                                       });
    //         }
    //         return refreshAttempt.then(function(refreshTokenResponse) {
    //           var response_config = jQuery.extend({}, response.config);
    //           response_config.headers.Authorization = 'Bearer ' + refreshTokenResponse.data.access_token;
    //           return http(response_config);
    //         });
    //       }
    //     }
    //   }
    // });

    // $httpProvider.interceptors.push('AccessTokenHttpInterceptor');
  })

  .config(function($httpProvider) {
    // if an access_token is set, we use it to provide
    // authorization token in headers, being it an anonymous
    // one or being it a real one
    if (localStorage.getItem('access_token') !== null) {
      $httpProvider.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('access_token');
    }

    // $httpProvider.interceptors.push('AccessTokenHttpInterceptor');
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
    }
  });
