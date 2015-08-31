'use strict';

describe('Controller: SigninCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var SigninCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    SigninCtrl = $controller('SigninCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(SigninCtrl.awesomeThings.length).toBe(3);
  });
});
