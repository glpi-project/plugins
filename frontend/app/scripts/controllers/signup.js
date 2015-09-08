'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SignupCtrl
 * @description
 * # SignupCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('SignupCtrl', function ($scope, $window, Auth) {
      $scope.authenticate = function(provider) {
         Auth.linkAccount(provider);

         // var authorization_endpoint = 'https://github.com/login/oauth/authorize';
         // var client_id = '58b0aebf84896b64ed1e';
         // var redirect_uri = "http://glpiplugindirectory/api/oauth/associate/github";

         // if (localStorage.getItem('authed') &&
         //     localStorage.getItem('access_token')) {
         //    var access_token = localStorage.getItem('access_token');
         //    var url = authorization_endpoint + '?' + jQuery.param({
         //       client_id: client_id,
         //       redirect_uri: redirect_uri + '?access_token=' + access_token
         //    });
         // } else {
         //    var url = authorization_endpoint + '?' + jQuery.param({
         //       client_id: client_id
         //    });
         // }

         // var w= window.open(url, '',{
         //    width: 400,
         //    height: 400,
         //    left: $window.screenX + (($window.outerWidth - 400) / 2),
         //    top: $window.screenY + (($window.outerHeight - 400) / 2.5)
         //  });

         // var i = 0;
         // var match = /http:\/\/glpiplugindirectory\/api\/oauth\/associate\/github/;
         // var _interval = setInterval(function() {
         //    i++;
         //    if (i == 250) {
         //       clearInterval(_interval);
         //    }
         //    try {
         //       var location = w.location.href;
         //       if (match.exec(location)) {
         //          w.addEventListener('message', function(e) {
         //             console.log(JSON.parse(e.data));
         //             //w.close();
         //          });
         //          clearInterval(_interval);
         //       }
         //    } catch (e) {}
         // }, 70);

         // $auth.authenticate(provider)
         //              .then(function() {
         //                console.log(arguments);
         //              });
      };
  });
