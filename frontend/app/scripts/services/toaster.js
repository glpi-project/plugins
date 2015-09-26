'use strict';

/**
 * @ngdoc service
 * @name frontendApp.toaster
 * @description
 * # toaster
 * Provider in the frontendApp.
 */
angular.module('frontendApp')
  .provider('Toaster', function () {
    var mdToast;

    function Toaster() {};
    Toaster.prototype.make = function(message, parent_id, position) {
      if (typeof position === 'undefined') position = 'top';
       var toast = mdToast.simple()
              .capsule(true)
              .content(message)
              .position(position);
       toast._options.parent = angular.element(document.getElementById(parent_id));
       mdToast.show(toast);
    };

    this.$get = function ($injector) {
      mdToast = $injector.get('$mdToast')
      return new Toaster();
    };
  });
