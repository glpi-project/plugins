'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SignupCtrl
 * @description
 * # SignupCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('SignupCtrl', function ($scope, $auth, $window) {
      $scope.authenticate = function(provider) {
         var w= window.open('https://github.com/login/oauth/authorize?&client_id=58b0aebf84896b64ed1e', '',{
            width: 400,
            height: 400,
            left: $window.screenX + (($window.outerWidth - 400) / 2),
            top: $window.screenY + (($window.outerHeight - 400) / 2.5)
          });

         var i = 0;
         var match = /http:\/\/glpiplugindirectory\/api\/oauth\/associate\/github/;
         var _interval = setInterval(function() {
            i++;
            if (i == 250) {
               clearInterval(_interval);
            }
            try {
               var location = w.location.href;
               if (match.exec(location)) {
                  w.addEventListener('message', function(e) {
                     console.log(JSON.parse(e.data));
                     w.close();
                  });
                  clearInterval(_interval);
               }
            } catch (e) {}
         }, 70);
         // $auth.authenticate(provider)
         //              .then(function() {
         //                console.log(arguments);
         //              });
      };
  });
