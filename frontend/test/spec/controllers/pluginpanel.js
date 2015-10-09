'use strict';

describe('Controller: PluginpanelCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var PluginpanelCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    PluginpanelCtrl = $controller('PluginpanelCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(PluginpanelCtrl.awesomeThings.length).toBe(3);
  });
});
