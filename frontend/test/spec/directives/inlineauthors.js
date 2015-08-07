'use strict';

describe('Directive: inlineAuthors', function () {

  // load the directive's module
  beforeEach(module('frontendApp'));

  var element,
    scope;

  beforeEach(inject(function ($rootScope) {
    scope = $rootScope.$new();
  }));

  it('should make hidden element visible', inject(function ($compile) {
    element = angular.element('<h4 inline-authors="{}"></h4>');
    element = $compile(element)(scope);
    expect(element.text()).toBe('this is the inlineauthors directive');
  }));
});
