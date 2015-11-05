'use strict';

describe('Service: exceptionRepresentationParser', function () {

  // instantiate service
  var exceptionRepresentationParser,
    init = function () {
      inject(function (_exceptionRepresentationParser_) {
        exceptionRepresentationParser = _exceptionRepresentationParser_;
      });
    };

  // load the service's module
  beforeEach(module('frontendApp'));

  it('should do something', function () {
    init();

    expect(!!exceptionRepresentationParser).toBe(true);
  });

  it('should be configurable', function () {
    module(function (exceptionRepresentationParserProvider) {
      exceptionRepresentationParserProvider.setSalutation('Lorem ipsum');
    });

    init();

    expect(exceptionRepresentationParser.greet()).toEqual('Lorem ipsum');
  });

});
