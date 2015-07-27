'use strict';

describe('Filter: unsafe', function () {

  // load the filter's module
  beforeEach(module('frontendApp'));

  // initialize a new instance of the filter before each test
  var unsafe;
  beforeEach(inject(function ($filter) {
    unsafe = $filter('unsafe');
  }));

  it('should return the input prefixed with "unsafe filter:"', function () {
    var text = 'angularjs';
    expect(unsafe(text)).toBe('unsafe filter: ' + text);
  });

});
