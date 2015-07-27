'use strict';

describe('Controller: PluginCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var PluginCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    PluginCtrl = $controller('PluginCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(PluginCtrl.awesomeThings.length).toBe(3);
  });
});
