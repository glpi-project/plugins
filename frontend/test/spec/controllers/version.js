'use strict';

describe('Controller: VersionCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var VersionCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    VersionCtrl = $controller('VersionCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(VersionCtrl.awesomeThings.length).toBe(3);
  });
});
