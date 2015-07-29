'use strict';

/**
 * @ngdoc directive
 * @name frontendApp.directive:rateBox
 * @description
 * # rateBox
 */
angular.module('frontendApp')
  .directive('rateBox', function () {
    return {
      restrict: 'E',
      link: function postLink(scope, element, attrs, controller) {
        if (typeof(scope.currentNote) === 'undefined')
          scope.currentNote = 0;

        var stars = controller.getStarsFromNote(scope.currentNote);

        for (var i = 0 ; i < stars.length ; i++) {
          element.append(stars[i]);
        }
      },
      scope: {
        currentNote: "=currentNote"
      },
      controller: function($scope) {
        this.getFullStar = function() {
          return angular.element('<i class="fa fa-star">');
        };

        this.getHalfStar = function() {
          return angular.element('<i class="fa fa-star-half-o">');
        };

        this.getEmptyStar = function() {
          return angular.element('<i class="fa fa-star-o">');
        };

        this.getStarsFromNote = function(note) {
          if (note > 5 || note < 0)
            note = 0;
          var stars = [];
          var lastIsHalf = note % 1 > 0;

          console.log(note, lastIsHalf);
          note = Math.floor(note);

          for (var i = 0 ; i < note ; i++) {
            stars.push(this.getFullStar());
          }

          if (lastIsHalf) {
            stars.push(this.getHalfStar());
            i = i + 0.5;
          }

          if (5 - i >= 1) {
            var missingStars = Math.floor(5 - i);
            for (var i = 0 ; i < missingStars ; i++) {
              stars.push(this.getEmptyStar());
            }
          }

          return stars;
        };
      }
    };
  });
