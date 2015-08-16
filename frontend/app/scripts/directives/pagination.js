'use strict';

/**
 * @ngdoc directive
 * @name frontendApp.directive:pagination
 * @description
 * # pagination
 */
angular.module('frontendApp')
   .provider('PaginatedCollection', function() {
      var PaginatedCollection = function() {
         this.page = 0;
         this.currentPage = [];
         this.pages = [];
         this.count = 0;
      };
      PaginatedCollection.prototype.modelsPerPage = 15;
      PaginatedCollection.prototype.from = 0;
      PaginatedCollection.prototype.to = 14;

      PaginatedCollection.prototype.setRequest = function(template) {
         this.request = template;         
      };

      PaginatedCollection.prototype.contentRangeRegexp = /^([0-9]+)-([0-9]+)\/([0-9]+)$/;
      PaginatedCollection.prototype.parseContentRange = function(contentRange) {
         var matches = this.contentRangeRegexp.exec(contentRange);
         return {
            from: matches[1],
            to: matches[2],
            length: matches[3]
         };
      };

      PaginatedCollection.prototype.setPage = function(page) {
         this.page = page;
         var from = this.page * this.modelsPerPage;
         var to = from + this.modelsPerPage - 1;
         var self = this;
         this.request(from, to)
             .success(function(data, status, headers) {
               var contentRange = self.parseContentRange(headers()['content-range']);
               var pagesQuantity = Math.ceil(contentRange.length / self.modelsPerPage);
               self.count = contentRange.length;
               self.pages = [];
               for (var i = 0 ; i < pagesQuantity ; i++) {
                  self.pages.push({
                     index: i
                  });
               }
               self.currentPage = data;
             });
      };

      this.$get = function() {
         return {
            getInstance: function() {
               return new PaginatedCollection();
            }
         };
      };
   })
   .directive('pagination', function () {
    return {
      template: '<div>'+
                  '<md-button ng-show="collection.count > collection.modelsPerPage" ng-class="(collection.page == page.index)?\'active\':\'\'" ng-repeat="page in collection.pages" ng-click="collection.setPage(page.index);ctrl.changeUrl(page.index)"><p>{{page.index}}</p></md-button>' +
                '</div>',
      restrict: 'E',
      link: function postLink(scope, element, attrs) {
         
      },
      scope: {
         "collection": "=collection"
      },
      controller: function($stateParams, $state) {
         this.changeUrl = function(page) {
            $stateParams.page = page;
            if (! /_page$/.exec($state.current.name)) {
               var state = $state.current.name + '_page';
            } else {
               var state = $state.current.name;
            }
            $state.go(state,$stateParams,{
               notify:false,
               reload:false,
               location:'replace',
               inherit:true
            });
         }
      },
      controllerAs: 'ctrl'
    };
   });
