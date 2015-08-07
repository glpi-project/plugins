'use strict';

describe('Controller: AuthorPluginsCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var AuthorCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    AuthorPluginsCtrl = $controller('AuthorPluginsCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(AuthorPluginsCtrl.awesomeThings.length).toBe(3);
  });
});
