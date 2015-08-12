'use strict';

angular
  .module('frontendApp')
  .config(function($translateProvider) {
    $translateProvider
      .translations('es', {
        ON: "sur",
        SLOGAN: "Extender GLPI con plugins",
        TRENDING: "Tendencias",
        TRENDING_SUB: "Beaucoup téléchargés ce mois-ci",
        NEW: "Nuevos",
        NEW_SUB: "Les plus récents",
        POPULAR: "Superior",
        POPULAR_SUB: "Les mieux notés",
        UPDATED: "Actualizado",
        UPDATED_SUB: "Derniers mis à jours",
        TAGS: "Tags",
        TAGS_SUB: "Aux plugins les plus nombreux",
        AUTHORS: "Autores",
        AUTHORS_SUB: "Plus gros contributeurs",
        NAV_FEATURED: "Featured",
        NAV_ALLPLUGINS: "All plugins",
        NAV_SEARCH: "Buscar",
        NAV_SUBMIT_A_PLUGIN: "Añadir su plugin",
        NAV_CONTACT: "Contáctenos",
        SEARCHBAR_PLACEHOLDER: "Buscar",
        VERSION: "Version",
        COMPATIBLE_WITH: "Compatible con",
        HOMEPAGE: "Sitio web",
        ADDED: "Añadido",
        BY: "Par",
        PLUGIN_CONTRIBUTED_TO: "à contribué/travaillé sur ces plugins",
        PLUGIN_TAGGED_WITH: "Plugins taggés avec",
        QUESTION_SUGGESTION_PROBLEM: "Una pregunta, una sugerencia, un problema?",
        FIRSTNAME: "Prénom",
        LASTNAME: "Nom de famille",
        EMAIL: "Email",
        SUBJECT: "Sujet",
        YOUR_MESSAGE: "Votre message",
        SUBMIT: "Envoyer",
        SUBMIT_YOUR_PLUGIN: "Ajouter votre plugin!",
        SUB_SUBMIT_YOUR_PLUGIN: "Veuillez fournir une URL vers le fichier xml décrivant votre plugin"
      });
  });