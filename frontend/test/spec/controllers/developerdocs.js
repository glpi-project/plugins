'use strict';

describe('Controller: DeveloperDocsCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var DeveloperdocsCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    DeveloperdocsCtrl = $controller('DeveloperDocsCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(DeveloperdocsCtrl.awesomeThings.length).toBe(3);
  });
});
