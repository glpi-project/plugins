'use strict';

describe('Controller: PanelCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var PanelCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    PanelCtrl = $controller('PanelCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(PanelCtrl.awesomeThings.length).toBe(3);
  });
});
