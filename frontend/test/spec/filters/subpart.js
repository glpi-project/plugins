'use strict';

describe('Filter: subpart', function () {

  // load the filter's module
  beforeEach(module('frontendApp'));

  // initialize a new instance of the filter before each test
  var subpart;
  beforeEach(inject(function ($filter) {
    subpart = $filter('subpart');
  }));

  it('should return the input prefixed with "subpart filter:"', function () {
    var text = 'angularjs';
    expect(subpart(text)).toBe('subpart filter: ' + text);
  });

});
