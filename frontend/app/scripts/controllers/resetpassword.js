'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:ResetpasswordCtrl
 * @description
 * # ResetpasswordCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('ResetpasswordCtrl', function ($scope, $stateParams, $state, API_URL,
                                             $http, Toaster) {
    if (!$stateParams.token ||
        $stateParams.token.length !== 40) {
           $state.go('featured');
    }

    $scope.passwordResetForm = {
       "password": "",
       "passwordRepeat": ""
    };

    $scope.resetPassword = function() {
       if ($scope.passwordResetForm.password !== $scope.passwordResetForm.passwordRepeat) {
          Toaster.make("You should provide password and its identical repetition");
          return;
       }

       $http({
          method: 'PUT',
          url: API_URL + '/user/password',
          data: {
             token: $stateParams.token,
             password: $scope.passwordResetForm.password 
          }
       }).then(function() {
          $state.go('featured');
          Toaster.make("You successfully changed your password, please login :)");
       });
    };
  });
