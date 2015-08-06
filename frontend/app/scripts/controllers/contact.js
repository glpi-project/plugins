'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:ContactCtrl
 * @description
 * # ContactCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('ContactCtrl', function (API_URL, RECAPTCHA_PUBLIC_KEY, $scope, vcRecaptchaService, $http, $mdToast, $timeout, $state) {
    $scope.key = RECAPTCHA_PUBLIC_KEY
    $scope.response = null;
    $scope.widgetId = null;

    $scope.setResponse = function (response) {
        console.info('Response available');
        $scope.response = response;
    };
    $scope.setWidgetId = function (widgetId) {
        console.info('Created widget ID: %s', widgetId);
        $scope.widgetId = widgetId;
    };
    $scope.cbExpiration = function() {
        console.info('Captcha expired. Resetting response object');
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
                $mdToast.show($mdToast.simple()
                                      .capsule(true)
                                      .content('Thanks for your message ! Be certain we will love reading it'));
                $timeout(function() {
                    $state.go('home');
                },3800);
            } else {
                $mdToast.show($mdToast.simple()
                                      .capsule(true)
                                      .content('Your message was rejected. Please try again !'));
                vcRecaptchaService.reload($scope.widgetId);
            }
        });
    };
  });
