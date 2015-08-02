'use strict';

/**
 * @ngdoc service
 * @name frontendApp.inlineAuthors
 * @description
 * # inlineAuthors
 * Factory in the frontendApp.
 */
angular.module('frontendApp')
  .factory('inlineAuthors', function () {
    return function(authors) {
      if (authors instanceof Array) {
        var _authors = '';
        for (var i = 0 ; i < authors.length; i++) {
          if (i > 0) {
            _authors += ', ';
          }
          _authors += authors[i].author;
        }
        return _authors;
      }
    };
  });
