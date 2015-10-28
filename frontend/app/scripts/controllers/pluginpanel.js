'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PluginpanelCtrl
 * @description
 * Note: the Pluginpanel controller exposes
 *       a feature named "Author Panel",
 *       please don't be confused.
 *
 */
angular.module('frontendApp')
  .controller('PluginpanelCtrl', function (API_URL, $scope, $rootScope, $state,
                                           $stateParams, $http, Toaster, $mdDialog) {
      // Redirects to /featured if not authed
      if (!$rootScope.authed) {
         $state.go('featured');
      }

      $scope.rights = {};

      // Note this is not the strict plugin object
      // but a special object which is only for
      // the author panel. the plugin will be at
      // $scope.plugin.card after $http has .then()'ed.
      $scope.plugin = {};

      var readRights = function(plugin, user) {
         for (var n in plugin.card.permissions) {
            if (plugin.card.permissions[n].id == user.id) {
               var pivot = plugin.card.permissions[n].pivot;
               return {
                  admin: pivot.admin,
                  allowed_change_xml_url: pivot.allowed_change_xml_url,
                  allowed_notifications: pivot.allowed_notifications,
                  allowed_refresh_xml: pivot.allowed_refresh_xml
               };
            }
         }
      };

      /**
       * Fetching card of current user
       */
      $http({
         method: 'GET',
         url: API_URL + '/user'
      }).then(function(resp) {
         $scope.user = resp.data;
         $scope.author_id = resp.data.author_id;
         // Fetching current plugin infos for the
         // author panel
         $http({
            method: 'GET',
            url: API_URL + '/panel/plugin/'+$stateParams.key
         }).then(function(resp) {
            $scope.plugin = resp.data;
            if ($stateParams.managePermissions) {
               $scope.showUserPermissionsDialog();
            }
            $scope.rights = readRights($scope.plugin, $scope.user);
         }, function(resp) {
            if (resp.data.error == 'LACK_PERMISSION') {
               Toaster.make('You lack the permission to view this plugin panel.');
               $state.go('panel');
            }
         });
      });


      /**
       * Update the plugin settings on user click
       */
      $scope.updateSettings = function() {
         $http({
            method: 'POST',
            url: API_URL + '/panel/plugin/'+$stateParams.key,
            data: {
               xml_url: $scope.plugin.card.xml_url
            }
         }).then(function(resp) {
            Toaster.make('You plugin has been updated.');
         }, function(resp) {
            Toaster.make(resp.data.error);
         });
      };

      var pluginPanelScope = $scope;
      function UserPermissionsDialogController($scope, Auth, $state, $timeout, $q, Toaster) {
         $scope.plugin = pluginPanelScope.plugin.card;
         $scope.permissions = [];
         $scope.search_text = '';
         $scope.current_hints =  [];

         var getPermissions = function() {         
            $http({
               url: API_URL + '/plugin/'+$scope.plugin.key+'/permissions',
               method: 'GET'
            }).then(function(resp) {
               $scope.permissions = resp.data;
            });
         }
         getPermissions();

         $scope.querySearch = function (search_string) {
            if (search_string == '' ||
                typeof(search_string) === 'undefined') {
               return [];
            }

            var resolver = $q.defer();

            $http({
               url: API_URL + '/user/search',
               method: 'POST',
               data: {
                  search: $scope.search_text
               },
            }).then(function(resp) {
               resolver.resolve(resp.data);
            });

            return resolver.promise;
         }

         $scope.addUserRight = function() {
            $http({
               method: 'POST',
               url: API_URL + '/plugin/'+$scope.plugin.key+'/permissions',
               data: {
                  username: $scope.selected_item.username
               }
            }).then(function() {
               getPermissions();
               $scope.search_text = '';
            }, function(resp) {
               if (/^RIGHT_ALREADY_EXIST/.exec(resp.data.error)) {
                  Toaster.make('There is already a right for that user and plugin, please modify it', 'user-permissions-form');
               }
               if (/^RIGHT_DOESNT_EXIST/.exec(resp.data.error)) {
                  Toaster.make('The right which you try to delete doesn\'t exist', 'user-permissions-form');
               }
               $scope.search_text = '';
            })
         };

         $scope.deleteUserRight = function(username) {
            $mdDialog.show(
               $mdDialog.confirm()
                        .title('Deleting permission line')
                        .content("Please confirm your choice to delete this permission line for "+username+' on "'+$scope.plugin.key+'"')
                        .ok('Delete permission line')
                        .cancel('I changed my mind')
            ).then(function() {
               $http({
                  method: 'DELETE',
                  url: API_URL + '/plugin/'+$scope.plugin.key+'/permissions/'+username
               }).then(function() {
                  showUserPermissionsDialog();
               });
            }, function() {
               showUserPermissionsDialog();
            });
         };

         $scope.toggleRight = function(user, right) {
            var set = (user.pivot[right])?false:true;
            $http({
               method: 'PATCH',
               url: API_URL + '/plugin/'+$scope.plugin.key+'/permissions/'+user.username,
               data: {
                  right: right,
                  set: set
               }
            }).then(function() {
               user.pivot[right] = set;
            });
         };

         $scope.close = function() {
            $mdDialog.hide();
         }
      }
      var showUserPermissionsDialog = function(ev) {
         if (!$scope.plugin.card) {
            return;
         }
         $mdDialog.show({
            controller: UserPermissionsDialogController,
            templateUrl: 'views/pluginuserpermissions.html',
            parent: angular.element(document.body),
            targetEvent: ev,
            clickOutsideToClose: true
         });
      };
      $scope.showUserPermissionsDialog = showUserPermissionsDialog;
  });
