'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SubmitCtrl
 * @description
 * # SubmitCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('SubmitCtrl', function (RECAPTCHA_PUBLIC_KEY, $scope, $http, vcRecaptchaService, $mdToast, $timeout, $state) {
    $scope.key = RECAPTCHA_PUBLIC_KEY;
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
            url: API_URL + '/plugin',
            data: {
                plugin_url: $scope.url,
                recaptcha_response: $scope.response
            }
        })
        .success(function(data) {
            if (data.success) {
                $mdToast.show($mdToast.simple()
                                      .capsule(true)
                                      .content('Thanks for your time, we are going to verify the plugin you have submitted.'));
                $timeout(function() {
                    $state.go('home');
                },3800);

            } else {
                $mdToast.show($mdToast.simple().content('Something went wrong with Recaptcha'));
                vcRecaptchaService.reload($scope.widgetId);
            }
        });
    };
  });
