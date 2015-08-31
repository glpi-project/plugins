'use strict';

describe('Service: auth', function () {

  // instantiate service
  var auth,
    init = function () {
      inject(function (_auth_) {
        auth = _auth_;
      });
    };

  // load the service's module
  beforeEach(module('frontendApp'));

  it('should do something', function () {
    init();

    expect(!!auth).toBe(true);
  });

  it('should be configurable', function () {
    module(function (authProvider) {
      authProvider.setSalutation('Lorem ipsum');
    });

    init();

    expect(auth.greet()).toEqual('Lorem ipsum');
  });

});
