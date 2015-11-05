'use strict';

/**
 * @ngdoc service
 * @name frontendApp.exceptionRepresentationParser
 * @description
 * @author Nelson Zamith <nzamith@teclib.com>
 * # exceptionRepresentationParser
 * Provides a way to parse Exception Representation strings
 * from the glpi-plugin-directory's very specific framework
 * I developed (</quote nz>)
 */
angular.module('frontendApp')
  .provider('exceptionRepresentationParser', function () {

    var ExceptionRepresentationParser = function() {};
    ExceptionRepresentationParser.prototype.parseExceptionRepresentation = function(representation) {
      // m will store regexp matches, one by one, for the scope of the function
      // i will be an iteration counter
      var m = null, i = 0;
      // Resetting `lastIndex`es to 0 in order not to have
      // any problem
      this.exceptionRepresentationRegexp.lastIndex = 0;
      this.exceptionRepresentationParameterRegexp.lastIndex = 0;

      // Parse fails here is we're not able to recognize
      // the global syntax for the Exception Representation
      if (!(m = this.exceptionRepresentationRegexp.exec(representation))) {
         return false;
      }

      var exceptionName = m[1];
      var exceptionArguments = {};

      if (m[2] !== undefined) {
         var argumentsAsString = m[2];
         while (m = this.exceptionRepresentationParameterRegexp.exec(argumentsAsString)) {
            exceptionArguments[m[1]] = m[3] === undefined ? m[2] : m[3];
            if (m[4] !== undefined) {
               exceptionArguments[m[1]] = parseInt(exceptionArguments[m[1]]);
            }
         }
      }

      return {
         name: exceptionName,
         args: exceptionArguments
      };
   };

   /**
    * Those are the regular expressions used as backend of
    * the parser
    */
   ExceptionRepresentationParser.prototype.exceptionRepresentationRegexp = /^([A-Z_]+)(\(.*\))?$/;
   ExceptionRepresentationParser.prototype.exceptionRepresentationParameterRegexp = /([a-zA-Z]+)=([a-zA-Z]+|"([^"]*)"|([0-9]+)) *,? */g;

    // Method for instantiating
    this.$get = function () {
      return new ExceptionRepresentationParser();
    };
  });
