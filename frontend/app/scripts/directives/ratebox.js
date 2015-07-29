'use strict';

/**
 * @ngdoc directive
 * @name frontendApp.directive:rateBox
 * @description
 * # rateBox
 */
angular.module('frontendApp')

  // The rateBox directive shows a
  // "star rate" widget which allow
  // users to rate a plugin
  .directive('rateBox', function () {
    return {
      restrict: 'E',
      link: function postLink(scope, element, attrs, controller) {
        // Defaults the current note to 0
        if (typeof(scope.currentNote) === 'undefined')
          scope.currentNote = 0;

        // Modify the DOM with the current stars
        var displayStars = function() {
          element.html('');
          var stars = controller.getStarsFromNote(scope.currentNote);
          stars.forEach(function(star) {
            element.append(star);
          });
        };

        // Watch for future modifications of the note
        scope.$watch('currentNote', function() {
          displayStars();
        });
        // Create stars for current note
        displayStars();
      },

      scope: {
        currentNote: "=currentNote"
      },
      controller: function($scope) {
        // returns a full star
        this.getFullStar = function() {
          return angular.element('<i class="fa fa-star">');
        };
        // returns half a star
        this.getHalfStar = function() {
          return angular.element('<i class="fa fa-star-half-o">');
        };
        // returns an empty star
        this.getEmptyStar = function() {
          return angular.element('<i class="fa fa-star-o">');
        };
        // returns an array of dom elements
        // which are the stars of the current
        // note
        this.getStarsFromNote = function(note) {
          if (note > 5 || note < 0)
            note = 0;
          var stars = [];
          var lastIsHalf = note % 1 > 0;

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
