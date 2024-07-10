'use strict';

/**
 * @ngdoc service
 * @name glpiPluginDirectoryMasterApp.fixIndepnet
 * @description
 * # fixIndepnet
 * Service in the glpiPluginDirectoryMasterApp.
 */
(function() {
   /**
    * Will replace the URL pointing to Indepnet
    * to URL's pointing to glpi-project.org
    */
   var fixIndepnet = function () {};
   fixIndepnet.prototype.fix = function(data) {
      var matchIndepnet = /https:\/\/forge\.indepnet\.net/;
      var fixField = function(value) {
         return value.replace('https://forge.indepnet.net', 'https://forge.glpi-project.org');
      };
      var fields = ['logo_url', 'readme_url', 'download_url', 'homepage_url', 'changelog_url'];

      for (var n in fields) {
         if (data[fields[n]] &&
         matchIndepnet.exec(data[fields[n]])) {
            data[fields[n]] = fixField(data[fields[n]]);
         }
      }
   };
   angular.module('frontendApp')
      .service('fixIndepnet', fixIndepnet);
})();
