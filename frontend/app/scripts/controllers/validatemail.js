'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:ValidatemailCtrl
 * @description
 * # ValidatemailCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('ValidatemailCtrl', function (API_URL, $http, $stateParams, Auth, $state) {
      $http({
         method: 'GET',
         url: API_URL + '/user/validatemail/'+$stateParams.token
      }).then(function(resp) {
         Auth.setToken(resp.data.access_token, resp.data.expires_in, resp.data.refresh_token, true);
         $state.go('panel');
      });
   });
