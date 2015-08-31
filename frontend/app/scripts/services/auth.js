'use strict';

/**
 * @ngdoc service
 * @name frontendApp.auth
 * @description
 * # auth
 * Provider in the frontendApp.
 */
angular.module('frontendApp')
  .provider('Auth', function ($httpProvider, $injector) {
    var rootScope, mdToast, state, timeout;
    var AuthManager = function() {};

    AuthManager.prototype.setToken = function(t, expires_in) {
      localStorage.setItem('access_token', t);
      localStorage.setItem('access_token_expires_in', expires_in);
      $httpProvider.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('access_token');
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
    };

    AuthManager.prototype.getToken = function() {
      return localStorage.getItem('access_token');
    };

    AuthManager.prototype.destroyToken = function() {
      localStorage.removeItem('access_token');
      localStorage.removeItem('access_token_expires_in');
      delete $httpProvider.defaults.headers.common['Authorization'];
      rootScope.authed = false;
      var toast = mdToast.simple()
          .capsule(true)
          .content("You are now disconnected")
          .position('top');
      toast._options.parent =  angular.element('body');
      mdToast.show(toast);
    };

    this.$get = function ($injector) {
      rootScope = $injector.get('$rootScope');
      mdToast = $injector.get('$mdToast');
      state = $injector.get('$state');
      timeout = $injector.get('$timeout');
      return new AuthManager();
    };
  })

  .config(function($httpProvider) {
    if (localStorage.getItem('access_token') !== null) {
      $httpProvider.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('access_token');
    }
  })

  .run(function($rootScope) {
    $rootScope.authed = (localStorage.getItem('access_token') === null) ? false : true;
  });
