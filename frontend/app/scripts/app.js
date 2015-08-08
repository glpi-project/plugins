'use strict';

/**
 * @ngdoc overview
 * @name frontendApp
 * @description
 * # frontendApp
 *
 * Main module of the application.
 */
angular
  .module('frontendApp', [
    'angular-loading-bar',
    'ngAnimate',
    'ui.router',
    'ngMaterial',
    'vcRecaptcha',
    'hljs',
    'ngSanitize',
    'pascalprecht.translate'
  ])

  // Constant to set API url
  .constant('API_URL', API_URL)
  .constant('RECAPTCHA_PUBLIC_KEY', RECAPTCHA_PUBLIC_KEY)

  // Determining current language
  .run(function() {
    var langs = ['en', 'fr'];

    if (localStorage.getItem('lang') === null) {
      var lang = navigator.language.split('-')[0];
      if (langs.indexOf(lang) > 0) {
        localStorage.setItem('lang', lang);
      } else {
        localStorage.setItem('lang', 'en');
      }
    }
  })

  // 'fr' locale for moment.js
  // this should move somewhere
  // else
  .run(function() {
    moment.locale('fr', {
      months : "janvier_février_mars_avril_mai_juin_juillet_août_septembre_octobre_novembre_décembre".split("_"),
      monthsShort : "janv._févr._mars_avr._mai_juin_juil._août_sept._oct._nov._déc.".split("_"),
      weekdays : "dimanche_lundi_mardi_mercredi_jeudi_vendredi_samedi".split("_"),
      weekdaysShort : "dim._lun._mar._mer._jeu._ven._sam.".split("_"),
      weekdaysMin : "Di_Lu_Ma_Me_Je_Ve_Sa".split("_"),
      longDateFormat : {
          LT : "HH:mm",
          LTS : "HH:mm:ss",
          L : "DD/MM/YYYY",
          LL : "D MMMM YYYY",
          LLL : "D MMMM YYYY LT",
          LLLL : "dddd D MMMM YYYY LT"
      },
      calendar : {
          sameDay: "[Aujourd'hui à] LT",
          nextDay: '[Demain à] LT',
          nextWeek: 'dddd [à] LT',
          lastDay: '[Hier à] LT',
          lastWeek: 'dddd [dernier à] LT',
          sameElse: 'L'
      },
      relativeTime : {
          future : "dans %s",
          past : "il y a %s",
          s : "quelques secondes",
          m : "une minute",
          mm : "%d minutes",
          h : "une heure",
          hh : "%d heures",
          d : "un jour",
          dd : "%d jours",
          M : "un mois",
          MM : "%d mois",
          y : "une année",
          yy : "%d années"
      },
      ordinalParse : /\d{1,2}(er|ème)/,
      ordinal : function (number) {
          return number + (number === 1 ? 'er' : 'ème');
      },
      meridiemParse: /PD|MD/,
      isPM: function (input) {
          return input.charAt(0) === 'M';
      },
      // in case the meridiem units are not separated around 12, then implement
      // this function (look at locale/id.js for an example)
      // meridiemHour : function (hour, meridiem) {
      //     return /* 0-23 hour, given meridiem token and hour 1-12 */
      // },
      meridiem : function (hours, minutes, isLower) {
          return hours < 12 ? 'PD' : 'MD';
      },
      week : {
          dow : 1, // Monday is the first day of the week.
          doy : 4  // The week that contains Jan 4th is the first week of the year.
      }
    });
    moment.locale(localStorage.getItem('lang'));
  })

  .config(function($translateProvider) {
    $translateProvider
      .translations('en', {
        SLOGAN: "Extend your GLPI with plugins",
        TRENDING: "Trending",
        TRENDING_SUB: "Often downloaded this month",
        NEW: "New",
        NEW_SUB: "Most recent in the catalog",
        POPULAR: "Popular",
        POPULAR_SUB: "With the most unique installs",
        UPDATED: "Updated",
        UPDATED_SUB: "Recently updated plugins",
        TAGS: "Tags",
        TAGS_SUB: "With the highest number of plugin",
        AUTHORS: "Authors",
        AUTHORS_SUB: "With the highest number of contributions",
        NAV_BROWSE: "Browse",
        NAV_SEARCH: "Search",
        NAV_SUBMIT_A_PLUGIN: "Submit a plugin",
        NAV_CONTACT: "Contact",
        SEARCHBAR_PLACEHOLDER: "Search",
        VERSION: "Version",
        COMPATIBLE_WITH: "Compatible with",
        HOMEPAGE: "Homepage",
        ADDED: "Added",
        UPDATED: "Updated",
        BY: "By"
      })
      .translations('fr', {
        SLOGAN: "Etendez GLPI avec les plugins",
        TRENDING: "Tendances",
        TRENDING_SUB: "Beaucoup téléchargés ce mois-ci",
        NEW: "Nouveaux",
        NEW_SUB: "Les plus récents",
        POPULAR: "Populaires",
        POPULAR_SUB: "Les mieux notés",
        UPDATED: "Mis à jour",
        UPDATED_SUB: "Derniers mis à jours",
        TAGS: "Tags",
        TAGS_SUB: "Aux plugins les plus nombreux",
        AUTHORS: "Auteurs",
        AUTHORS_SUB: "Plus gros contributeurs",
        NAV_BROWSE: "Naviguer",
        NAV_SEARCH: "Rechercher",
        NAV_SUBMIT_A_PLUGIN: "Ajouter votre plugin",
        NAV_CONTACT: "Contactez-nous",
        SEARCHBAR_PLACEHOLDER: "Recherche",
        VERSION: "Version",
        COMPATIBLE_WITH: "Compatible avec",
        HOMEPAGE: "Site internet",
        ADDED: "Ajouté",
        UPDATED: "Mis à jour",
        BY: "Par"
      });
    $translateProvider.determinePreferredLanguage(function() {
      return localStorage.getItem('lang');
    });
  })

  .config(function($httpProvider) {
    $httpProvider.defaults.headers.common['X-Lang'] = localStorage.getItem('lang');
  })

  // Associating empty arrays to
  // placeholders in $rootScope
  // that are going to be used
  // by home.js
  .run(function($rootScope) {
      $rootScope.trending = [];
      $rootScope.new = [];
      $rootScope.popular = [];
      $rootScope.updated = [];
      $rootScope.tags = [];
      $rootScope.authors = [];
  })

  // Configuration of states in router
  .config(function($stateProvider, $urlRouterProvider, $mdThemingProvider) {

     // Define colors of application, from material angular
     $mdThemingProvider.theme('default')
       .primaryPalette('red', { 'default': '200' })
       .accentPalette('orange');

     // For any unmatched url, redirect to /
     $urlRouterProvider.otherwise("/");

     $stateProvider
       .state('home', {
         url: "/",
         templateUrl: "views/home.html",
         controller: 'HomeCtrl'
       })
       .state('plugin', {
         url: '/plugin/:key',
         templateUrl: 'views/plugin.html',
         controller: 'PluginCtrl'
       })
       .state('search', {
         url: '/search/:val',
         templateUrl: "views/plugin_list.html",
         controller: "SearchCtrl"
       })
       .state('tag', {
        url: '/tag/:key',
        templateUrl: "views/plugin_list.html",
        controller: "TagCtrl"
       })
       .state('author', {
        url: '/author/:id',
        templateUrl: 'views/author.html',
        controller: 'AuthorCtrl'
       })
       .state('author_plugins', {
        url: '/author/:id/plugin',
        templateUrl: "views/plugin_list.html",
        controller: "AuthorPluginsCtrl"
       })
       .state('submit', {
       	 url: '/submit',
       	 templateUrl: "views/submit.html",
       	 controller: "SubmitCtrl"
       })
       .state('contact', {
       	 url: '/contact',
       	 templateUrl: 'views/contact.html',
       	 controller: 'ContactCtrl'
       });
   });
