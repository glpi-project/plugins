'use strict';

describe('Controller: ValidatemailCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var ValidatemailCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    ValidatemailCtrl = $controller('ValidatemailCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(ValidatemailCtrl.awesomeThings.length).toBe(3);
  });
});
