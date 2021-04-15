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
                     '<i ng-hide="authed" class="fa fa-user"></i>'+
                     '<img ng-show="authed" ng-src="//www.gravatar.com/avatar/{{gravatar}}?s=50" class="avatar" />'+
                     '<span>{{(authed) ? username :  \'LOGIN\'|translate}}</span>'+
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
         scope.$watch();
      },
      controller: function ($rootScope, $scope, Auth, API_URL, $http, $state) {
         this.openMenu = function($mdOpenMenu, ev) {
            $mdOpenMenu(ev);
          };

          this.disconnect = function() {
            Auth.destroyToken();
          };

          $rootScope.$watch('authed', function(a) {
            if (a) {
              $http({
                method: 'GET',
                url: API_URL + '/user'
              }).success(function(data) {
                if (data) {
                   $scope.username = data.username;
                   if (data.gravatar) {
                     $scope.gravatar = data.gravatar;
                   }
                   if ($state.current.name == 'signin' ||
                       $state.current.name == 'signup') {
                     if (data.active == 0) {
                        return $state.go('finishactivateaccount')
                     }
                     $state.go('featured');
                   }
                }
              });
            } else {
               delete $scope.gravatar;
            }
          });
      },
      controllerAs: 'ctrl',
      scope: {
        authed: "=authed"
      }
    };
  });
