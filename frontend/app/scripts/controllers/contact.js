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
      $scope.key = RECAPTCHA_PUBLIC_KEY;
      $scope.response = null;
      $scope.widgetId = null;
      $scope.contact = {};

      /**
       * Scope functions used by reCaptcha
       */
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

      /**
       * Scope function triggered when user
       * user posts his message
       */
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
            var toast = $mdToast.simple()
                   .capsule(true)
                   .content('Thanks for your message ! Be certain we will love reading it')
                   .position('top');
            toast._options.parent = angular.element(document.getElementById('contactForm'));
            $mdToast.show(toast);

            $timeout(function() {
               $state.go('featured');
            }, 3800);
         })
         .error(function(data) {
            var toast = $mdToast.simple()
                   .capsule(true)
                   .content('Uncomplete form or "I\'m not a robot" not checked. Please try again !')
                   .position('bottom');
            toast._options.parent = angular.element(document.getElementById('contactForm'));
            $mdToast.show(toast);
            vcRecaptchaService.reload($scope.widgetId);
         });
      };

      /**
       * Validation of the form
       */
       // $scope.form_errors = {
       //   firstname: {

       //   }
       // };
   });