'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SubmitCtrl
 * @description
 * # SubmitCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('SubmitCtrl', function(API_URL, RECAPTCHA_PUBLIC_KEY, $scope, $http, vcRecaptchaService, $mdToast, $timeout, $state) {
      $scope.key = RECAPTCHA_PUBLIC_KEY;
      $scope.response = null;
      $scope.widgetId = null;

      $scope.setResponse = function(response) {
         $scope.response = response;
      };
      $scope.setWidgetId = function(widgetId) {
         $scope.widgetId = widgetId;
      };
      $scope.cbExpiration = function() {
         $mdToast.show($mdToast.simple()
            .capsule(true)
            .content('Captcha expired, please select "I\'m not a robot" again')
            .position('top'));
         $scope.response = null;
      };

      $scope.submit = function() {
         $http({
            method: 'POST',
            url: API_URL + '/plugin',
            data: {
               plugin_url: $scope.url,
               recaptcha_response: $scope.response
            }
         })
         .success(function(data) {
            if (data.success) {
               var toast = $mdToast.simple()
                  .capsule(true)
                  .content('Thanks for your time ! We are going to verify the plugin you have submitted.')
                  .position('top');
               toast._options.parent =  angular.element(document.getElementById('submit_form'));
               $mdToast.show(toast);

               $timeout(function() {
                  $state.go('featured');
               }, 3800);

            } else {
               var toast = $mdToast.simple()
                  .capsule(true)
                  .content("Error: " + data.error)
                  .position('top right');
               toast._options.parent =  angular.element(document.getElementById('submit_form'));
               $mdToast.show(toast);
               vcRecaptchaService.reload($scope.widgetId);
            }
         });
      };
   });