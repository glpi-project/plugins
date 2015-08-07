'use strict';

/**
 * @ngdoc directive
 * @name frontendApp.directive:inlineauthors
 * @description
 * # inlineauthors
 */
angular.module('frontendApp')
  .directive('inlineAuthors', function () {
    return {
      restrict: 'A',
      link: function postLink(scope, element, attrs, ctrl) {
            scope.$watch('inlineAuthors', function() {
              element.html('');
              element.append(angular.element('<span>')
                                    .text('by'));
              if (scope.inlineAuthors instanceof Array) {
                var _authors = '';
                for (var i = 0 ; i < scope.inlineAuthors.length; i++) {
                  var author_el = angular.element('<a href="#/author/'+scope.inlineAuthors[i].id+'">')
                                         .text(scope.inlineAuthors[i].name);

                  element.append(author_el);
                }
              }
            });
      },
      scope: {
        inlineAuthors: "=inlineAuthors"
      },
      controller: function($state) {
        this.openAuthor = function(id) {
          $state.go('author', {
            id: id
          })
        };
      }
    };
  });
