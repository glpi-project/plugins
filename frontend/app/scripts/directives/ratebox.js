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

        // Creating five icon elements to display stars
        var stars = [];
        for (var i = 0 ; i < 5 ; i++) {
          var icon = angular.element('<i>');
          stars.push(icon)
          element.append(icon);
        }

        // Modify the DOM with the current stars
        var displayStars = function() {
          var classes = controller.getClassesFromNote(scope.currentNote);
          for (var i = 0 ; i < stars.length ; i++) {
           stars[i].attr('class', classes[i]);
          }
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
        // returns fontawesome class for a full star
        this.getFullStar = function() {
          return 'fa fa-star';
        };
        // returns fontawesome class for half a star
        this.getHalfStar = function() {
          return 'fa fa-star-half-o';
        };
        // returns fontawesome class for an empty star
        this.getEmptyStar = function() {
          return 'fa fa-star-o';
        };
        // returns an array of dom elements
        // which are the stars of the current
        // note
        this.getClassesFromNote = function(note) {
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
