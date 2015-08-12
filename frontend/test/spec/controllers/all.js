'use strict';

describe('Controller: AllCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var AllCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    AllCtrl = $controller('AllCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(AllCtrl.awesomeThings.length).toBe(3);
  });
});
