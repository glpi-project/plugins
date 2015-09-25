'use strict';

describe('Service: FormValidator', function () {

  // instantiate service
  var FormValidator,
    init = function () {
      inject(function (_FormValidator_) {
        FormValidator = _FormValidator_;
      });
    };

  // load the service's module
  beforeEach(module('frontendApp'));

  it('should do something', function () {
    init();

    expect(!!FormValidator).toBe(true);
  });

  it('should be configurable', function () {
    module(function (FormValidatorProvider) {
      FormValidatorProvider.setSalutation('Lorem ipsum');
    });

    init();

    expect(FormValidator.greet()).toEqual('Lorem ipsum');
  });

});
