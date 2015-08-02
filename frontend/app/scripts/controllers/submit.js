'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:SubmitCtrl
 * @description
 * # SubmitCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')
  .controller('SubmitCtrl', function (RECAPTCHA_PUBLIC_KEY, $scope, $http, vcRecaptchaService) {
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
                console.log('Plugin submit');
            } else {
                console.log('Something went wrong');
                vcRecaptchaService.reload($scope.widgetId);
            }
        });
    };
  });
