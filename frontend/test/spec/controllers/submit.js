'use strict';

describe('Controller: SubmitCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var SubmitCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    SubmitCtrl = $controller('SubmitCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(SubmitCtrl.awesomeThings.length).toBe(3);
  });
});
