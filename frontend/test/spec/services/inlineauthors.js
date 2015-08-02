'use strict';

describe('Service: inlineAuthors', function () {

  // load the service's module
  beforeEach(module('frontendApp'));

  // instantiate service
  var inlineAuthors;
  beforeEach(inject(function (_inlineAuthors_) {
    inlineAuthors = _inlineAuthors_;
  }));

  it('should do something', function () {
    expect(!!inlineAuthors).toBe(true);
  });

});
