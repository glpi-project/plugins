'use strict';

describe('Controller: TagCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var TagCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    TagCtrl = $controller('TagCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(TagCtrl.awesomeThings.length).toBe(3);
  });
});
