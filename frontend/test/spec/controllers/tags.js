'use strict';

describe('Controller: TagsCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var TagsCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    TagsCtrl = $controller('TagsCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(TagsCtrl.awesomeThings.length).toBe(3);
  });
});
