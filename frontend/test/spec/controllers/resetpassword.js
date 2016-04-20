'use strict';

describe('Controller: ResetpasswordCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var ResetpasswordCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    ResetpasswordCtrl = $controller('ResetpasswordCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(ResetpasswordCtrl.awesomeThings.length).toBe(3);
  });
});
