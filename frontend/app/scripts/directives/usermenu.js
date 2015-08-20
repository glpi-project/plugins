'use strict';

/**
 * @ngdoc directive
 * @name frontendApp.directive:userMenu
 * @description
 * # userMenu
 */
angular.module('frontendApp')
  .directive('userMenu', function () {
    return {
      template: '<md-menu md-offset="0 92px">'+
                  '<md-button ng-click="ctrl.openMenu($mdOpenMenu, $event)">'+
                     '<i class="fa fa-user"></i>'+
                     '<span>Nelson</span>'+
                  '</md-button>'+
                  '<md-menu-content width="4">'+
                     '<md-menu-item>'+
                        '<md-button ui-sref="panel">User Panel</md-button>'+
                     '</md-menu-item>'+
                     '<md-menu-item>'+
                        '<md-button>Disconnect</md-button>'+
                     '</md-menu-item>'+
                  '</md-menu-content>'+
                 '</md-menu>',
      restrict: 'A',
      link: function postLink(scope, element, attrs) {
         element.addClass('user-menu');
      },
      controller: function ($scope) {
         this.openMenu = function($mdOpenMenu, ev) {
            $mdOpenMenu(ev);
          };
      },
      controllerAs: 'ctrl'
    };
  });
