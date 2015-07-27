'use strict';

describe('Controller: SidenavctrlCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var SidenavctrlCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    SidenavctrlCtrl = $controller('SidenavctrlCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(SidenavctrlCtrl.awesomeThings.length).toBe(3);
  });
});
