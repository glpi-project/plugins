'use strict';

describe('Controller: LinkfreshaccountCtrl', function () {

  // load the controller's module
  beforeEach(module('frontendApp'));

  var LinkfreshaccountCtrl,
    scope;

  // Initialize the controller and a mock scope
  beforeEach(inject(function ($controller, $rootScope) {
    scope = $rootScope.$new();
    LinkfreshaccountCtrl = $controller('LinkfreshaccountCtrl', {
      $scope: scope
      // place here mocked dependencies
    });
  }));

  it('should attach a list of awesomeThings to the scope', function () {
    expect(LinkfreshaccountCtrl.awesomeThings.length).toBe(3);
  });
});
