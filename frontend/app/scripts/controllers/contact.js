'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:ContactCtrl
 * @description
 * # ContactCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
   .controller('ContactCtrl', function(API_URL, RECAPTCHA_PUBLIC_KEY, $scope, vcRecaptchaService, $http, $mdToast, $timeout, $state) {
      $scope.key = RECAPTCHA_PUBLIC_KEY
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
            url: API_URL + '/message',
            data: {
               contact: $scope.contact,
               recaptcha_response: $scope.response
            }
         })
         .success(function(data) {
            if (data.success) {
               var toast = $mdToast.simple()
                      .capsule(true)
                      .content('Thanks for your message ! Be certain we will love reading it')
                      .position('top');
               toast._options.parent = angular.element(document.getElementById('contact_form'));
               $mdToast.show(toast);
               
               $timeout(function() {
                  $state.go('featured');
               }, 3800);
            } else {
               $mdToast.show($mdToast.simple()
                  .capsule(true)
                  .content('Your message was rejected. Please try again !'));
               vcRecaptchaService.reload($scope.widgetId);
            }
         });
      };
   });