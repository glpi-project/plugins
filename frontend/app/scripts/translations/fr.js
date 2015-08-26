'use strict';

angular
   .module('frontendApp')
   .config(function($translateProvider) {
      $translateProvider
         .translations('fr', {
            ON: "sur",
            SLOGAN: "Etendez GLPI avec les plugins",
            TRENDING: "Tendances",
            TRENDING_SUB: "Plugins populaires le mois dernier",
            NEW: "Nouveaux",
            NEW_SUB: "Les plus récents",
            POPULAR: "Populaires",
            POPULAR_SUB: "Les mieux notés",
            UPDATED: "Mis à jour",
            UPDATED_SUB: "Derniers plugins mis à jours",
            TAGS: "Tags",
            TAGS_SUB: "Avec le plus de plugins ",
            AUTHORS: "Auteurs",
            AUTHORS_SUB: "Avec le plus grand nombre de contributions",
            NAV_FEATURED: "À la une",
            NAV_ALLPLUGINS: "Tous les plugins",
            NAV_SEARCH: "Rechercher",
            NAV_SUBMIT_A_PLUGIN: "Ajouter votre plugin",
            NAV_CONTACT: "Contactez-nous",
            SEARCHBAR_PLACEHOLDER: "Recherche",
            VERSION: "Version",
            COMPATIBLE_WITH: "Compatible avec",
            HOMEPAGE: "Site internet",
            ADDED: "Ajouté",
            BY: "Par",
            CONTRIBUTED_TO: "à contribué à",
            PLUGIN_CONTRIBUTED_TO: "à contribué/travaillé sur ces plugins",
            TAGGED_WITH: "taggé sur",
            PLUGIN_TAGGED_WITH: "Plugins taggés avec",
            QUESTION_SUGGESTION_PROBLEM: "Une question, suggestion, ou un problème ?",
            FIRSTNAME: "Prénom",
            LASTNAME: "Nom",
            EMAIL: "Email",
            SUBJECT: "Sujet",
            YOUR_MESSAGE: "Votre message",
            SUBMIT: "Envoyer",
            SUBMIT_YOUR_PLUGIN: "Ajouter votre plugin!",
            SUB_SUBMIT_YOUR_PLUGIN: "Veuillez fournir une URL vers le fichier xml décrivant votre plugin",
            GLPI_PLUGIN_CREATORS: "Créateurs de plugins GLPI",
            PAGINATION_RESULTS: "Résultats",
            PAGINATION_TO: "à",
            PAGINATION_ON: "sur un total de",
            PAGINATION_ELEMENTS: "elements"
         });
   });