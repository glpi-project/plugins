'use strict';

describe('Service: toaster', function () {

  // instantiate service
  var toaster,
    init = function () {
      inject(function (_toaster_) {
        toaster = _toaster_;
      });
    };

  // load the service's module
  beforeEach(module('frontendApp'));

  it('should do something', function () {
    init();

    expect(!!toaster).toBe(true);
  });

  it('should be configurable', function () {
    module(function (toasterProvider) {
      toasterProvider.setSalutation('Lorem ipsum');
    });

    init();

    expect(toaster.greet()).toEqual('Lorem ipsum');
  });

});
