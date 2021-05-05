'use strict';

/**
 * @ngdoc service
 * @name glpiPluginDirectoryMasterApp.PluginVersions
 * @description
 * # PluginVersions
 * Service in the glpiPluginDirectoryMasterApp.
 */
angular.module('frontendApp')
   .provider('PluginVersions', function () {
      var PluginVersions = function() {};
      
      PluginVersions.prototype.sort = function(versions) {
         return versions.sort(function(a, b) {
            return b.num.localeCompare(a.num, undefined, {
               numeric: true,
               sensitivity: 'base'
            });
         });
      };

      this.$get = function () {
         return new PluginVersions();
      };
   });
