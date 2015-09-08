'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:LinkfreshaccountCtrl
 * @description
 * # LinkfreshaccountCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('FinishActivateAccountCtrl', function (API_URL, $http, $scope) {
      $http({
         method: "GET",
         url: API_URL + '/user'
      }).success(function(data) {
         $scope.user = data;

         if ($scope.user.email === null) {
            $http({
               method: "GET",
               url: API_URL + '/oauth/available_emails'
            }).success(function(data) {
               $scope.available_emails = data;
            });
         }
      });

      $scope.submit = function() {

      };

  });
