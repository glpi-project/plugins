'use strict';

describe('Controller: AuthorsCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var AuthorsCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    AuthorsCtrl = $controller('AuthorsCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(AuthorsCtrl.awesomeThings.length).toBe(3);
  });
});
