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
            } else return true;
         }
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
         "password": function(password, repeat) {
            if (typeof(repeat) === 'undefined') {
               repeat = password;
            }
            var errors = {
               tooshort: false,
               toolong: false,
               different: false
            };

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
