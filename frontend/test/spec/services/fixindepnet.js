'use strict';

describe('Service: fixIndepnet', function () {

  // load the service's module
  beforeEach(module('frontendApp'));

  // instantiate service
  var fixIndepnet;
  beforeEach(inject(function (_fixIndepnet_) {
    fixIndepnet = _fixIndepnet_;
  }));

  it('should do something', function () {
    expect(!!fixIndepnet).toBe(true);
  });

});
