'use strict';

describe('Directive: eatClickIf', function () {

  // load the directive's module
  beforeEach(module('frontendApp'));

  var element,
    scope;

  beforeEach(inject(function ($rootScope) {
    scope = $rootScope.$new();
  }));

  it('should make hidden element visible', inject(function ($compile) {
    element = angular.element('<eat-click-if></eat-click-if>');
    element = $compile(element)(scope);
    expect(element.text()).toBe('this is the eatClickIf directive');
  }));
});
