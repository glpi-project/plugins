'use strict';

/**
 * @ngdoc function
 * @name frontendApp.controller:PluginCtrl
 * @description
 * # PluginCtrl
 * Controller of the frontendApp
 */
angular.module('frontendApp')

  .controller('PluginCtrl', function (API_URL, $scope, $http, $stateParams, $sce) {
  	$scope.plugin = {
  		authors: {}
  	};

  	$scope.inlineAuthors = function(authors) {
  		var _authors = '';
  		for (var i = 0 ; i < authors.length; i++) {
  			if (i > 0) {
  				_authors += ', ';
  			}
  			_authors += authors[i].author;
  		}
  		return _authors;
  	};

    $http({
    	method: 'GET',
    	url: API_URL + '/plugin/' + $stateParams.id
    })
    .success(function(data, status, headers, config) {
    	$scope.plugin = data;
    });
  });
