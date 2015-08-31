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
                     '<span>{{(authed) ? \'Me\' :  \'LOGIN\'|translate}}</span>'+
                  '</md-button>'+
                  '<md-menu-content width="4">'+
                     '<md-menu-item ng-show="authed">'+
                        '<md-button ui-sref="panel">{{\'USER_PANEL\'|translate}}</md-button>'+
                     '</md-menu-item>'+
                     '<md-menu-item ng-show="authed">'+
                        '<md-button ng-click="ctrl.disconnect()">{{\'DISCONNECT\'|translate}}</md-button>'+
                     '</md-menu-item>'+
                     '<md-menu-item ng-hide="authed">'+
                        '<md-button ui-sref="signin">{{\'SIGNIN\'|translate}}</md-button>'+
                     '</md-menu-item>'+
                     '<md-menu-item ng-hide="authed">'+
                        '<md-button ui-sref="signup">{{\'SIGNUP\'|translate}}</md-button>'+
                     '</md-menu-item>'+
                  '</md-menu-content>'+
                 '</md-menu>',
      restrict: 'A',
      link: function postLink(scope, element, attrs) {
         element.addClass('user-menu');
      },
      controller: function ($scope, Auth) {
         this.openMenu = function($mdOpenMenu, ev) {
            $mdOpenMenu(ev);
          };

          this.disconnect = function() {
            Auth.destroyToken();
          };
      },
      controllerAs: 'ctrl',
      scope: {
        authed: "=authed"
      }
    };
  });
