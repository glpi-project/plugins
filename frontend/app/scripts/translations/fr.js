'use strict';

angular
  .module('frontendApp')
  .config(function($translateProvider) {
    $translateProvider
      .translations('fr', {
        ON: "sur",
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
        PLUGIN_CONTRIBUTED_TO: "à contribué/travaillé sur ces plugins",
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
        GLPI_PLUGIN_CREATORS: "Créateurs de plugins GLPI"
      });
  });