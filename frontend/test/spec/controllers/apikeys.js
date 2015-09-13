'use strict';

describe('Controller: ApikeysCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var ApikeysCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    ApikeysCtrl = $controller('ApikeysCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(ApikeysCtrl.awesomeThings.length).toBe(3);
  });
});
