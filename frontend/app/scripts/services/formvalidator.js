'use strict';

/**
 * @ngdoc service
 * @name frontendApp.FormValidator
 * @description
 * # FormValidator
 * Provider in the frontendApp.
 */
angular.module('frontendApp')
   .provider('FormValidator', function () {
      var FormValidator = function() {};

      FormValidator.prototype.getValidator = function(name) {
         if (this.validators[name]) {
            return this.validators[name];
         } else {
            throw new Error('Code tried to fetch unexisting validator');
         }
      };

      FormValidator.prototype.noError = function(errors) {
         for (var index in errors) {
            if (errors[index]) {
               return false;
            }
         }
         return true;
      };

      /**
       * Once a payload is filled, if the payload is filled
       * only with values that changed (compared
       * to the model) that function is used to understand
       * that nothing changed. This is useful to prevent
       * the concerned useless HTTP PUT to the server
       */
      FormValidator.prototype.payloadEmpty = function(payload) {
         // inspired from
         // http://stackoverflow.com/questions/5223/length-of-a-javascript-object-that-is-associative-array
         var size = 0, key;
         for (key in payload) {
            if (payload.hasOwnProperty(key)) size++;
         }
         return size == 0;
      };

      FormValidator.prototype.validators = {
         "username": function(username, required) {
            var errors = {
               tooshort: false,
               toolong: false,
               badcharacters: false,
               required: false
            };

            if (required && username.length == 0)  {
               errors.required = true;
            }
            else if (!required && username.length == 0) {
               return errors;
            }

            if (username.length < 4) {
               errors.tooshort = true;
            }
            if (username.length > 28) {
               errors.toolong = true;
            }
           if (!/^[a-zA-Z0-9]+$/.exec(username)) {
               errors.badcharacters = true;
            }
            return errors;
         },
         "website": function(website) {
            var errors = {
               invalid: false
            };

            if (website.length == 0) {
               return errors;
            }

            var urlRegex = '^(?:(?:http|https|ftp)://)(?:\\S+(?::\\S*)?@)?(?:(?:(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}(?:\\.(?:[0-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))|(?:(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)(?:\\.(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)*(?:\\.(?:[a-z\\u00a1-\\uffff]{2,})))|localhost)(?::\\d{2,5})?(?:(/|\\?|#)[^\\s]*)?$';
            var url = new RegExp(urlRegex, 'i');
            errors.invalid = !(website.length < 2083 && url.test(website));

            return errors;
         },
         "email": function(email, required) {
            var errors = {
               tooshort: false,
               toolong: false,
               invalid: false,
               required: false
            };

            if (required && email.length == 0)  {
               errors.required = true;
            }

            if (!/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i.exec(email)) {
               errors.invalid = true;
            }
            return errors;
         },
         "password": function(password, repeat, required) {
            if (typeof(repeat) === 'undefined') {
               repeat = password;
            }
            var errors = {
               tooshort: false,
               toolong: false,
               different: false,
               required: false
            };

            if (required && password.length == 0)  {
               errors.required = true;
            }
            if (password.length < 6) {
               errors.tooshort = true;
            }
            if (password.length > 26) {
               errors.toolong = true;
            }
            if (password != repeat) {
               errors.different = true;
            }
            return errors;
         },

         "realname": function(realname) {
            var errors = {
               tooshort: false,
               toolong: false
            };
            if (realname.length < 4) {
               errors.tooshort = true;
            }
            if (realname.length > 28) {
               errors.toolong = true;
            }
            return errors;
         }
      };

      this.$get = function () {
         return new FormValidator();
      };
   });
