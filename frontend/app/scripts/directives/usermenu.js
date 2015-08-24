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
                     '<span>{{(user) ? user :  \'My Account\'}}</span>'+
                  '</md-button>'+
                  '<md-menu-content width="4">'+
                     '<md-menu-item ng-show="authed">'+
                        '<md-button ui-sref="panel">User Panel</md-button>'+
                     '</md-menu-item>'+
                     '<md-menu-item ng-show="authed">'+
                        '<md-button>Disconnect</md-button>'+
                     '</md-menu-item>'+
                     '<md-menu-item ng-hide="authed">'+
                        '<md-button>Sign-In</md-button>'+
                     '</md-menu-item>'+
                     '<md-menu-item ng-hide="authed">'+
                        '<md-button ui-sref="signup">Sign-Up</md-button>'+
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
      scope: {
        authed: "=authed"
      },
      controllerAs: 'ctrl'
    };
  });
