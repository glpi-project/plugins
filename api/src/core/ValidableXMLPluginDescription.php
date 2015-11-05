<?php

namespace API\Core;

use \API\Exception\InvalidXML;

class ValidableXMLPluginDescription {
   public $contents;
   public $collectMode;
   public $parsable = false;
   public $validated = false;
   public $errors = [];

   public $requiredFields = [
      "name",
      "key",
      "state",
      "description",
      "homepage",
      "download",
      "authors",
      "versions",
      "langs",
      "license",
      "tags"
   ];

   public function validateName() {
      if (sizeof($this->contents->name) != 1 ||
         sizeof($this->contents->name->children()) != 0) {
         $this->throwOrCollect(new InvalidXML('field', 'name', '<name> should be a singular field without children'));
      }
      return true;
   }

   public function validateKey() {
      if (sizeof($this->contents->key) != 1 ||
         sizeof($this->contents->key->children()) != 0) {
         $this->throwOrCollect(new InvalidXML('field', 'key', '<key> should be a singular field without children'));
      }
      return true;
   }

   public function validateState() {
      if (sizeof($this->contents->state) != 1 ||
         sizeof($this->contents->state->children()) != 0 ||
         !in_array((string)$this->contents->state, ['stable', 'unstable', 'beta', 'alpha']))
      {
         $this->throwOrCollect(new InvalidXML('field', 'state', "<state> should be 'stable', 'unstable', 'beta' or 'alpha'"));
      }
      return true;
   }

   public function validateLogo() {
      if (sizeof($this->contents->logo) != 1 ||
         sizeof($this->contents->logo->children()) != 0) {
         $this->throwOrCollect(new InvalidXML('field', 'logo', '<logo> should be a singular field without children'));
      }
      return true;
   }

   // @todo: avoid dry;
   public function validateDescription() {
      if (sizeof($this->contents->description) != 1 ||
         sizeof($this->contents->description->children()) != 2) {
         return $this->throwOrCollect(new InvalidXML('field', 'description', '<description> should contain <short> and <long> tags only'));
      }

      foreach ($this->contents->description->children() as $type => $langs) {
         if (!in_array($type, ['long', 'short'])) {
            return $this->throwOrCollect(new InvalidXML('field', 'description.'.$type, '<description> should contain <short> and <long> tags only'));
         }
         if(sizeof($langs->children()) < 1) {
            return $this->throwOrCollect(new InvalidXML('field', 'description.'.$type, 'each <short> and <long> should have at least one <lang> inside'));
         }
      }

      return true;
   }

   public function validateHomepage() {
      if (sizeof($this->contents->homepage) != 1 ||
         sizeof($this->contents->homepage->children()) != 0) {
         $this->throwOrCollect(new InvalidXML('field', 'homepage', '<homepage> should be a singular field without children'));
      }
      return true;
   }

   public function validateDownload() {
      if (sizeof($this->contents->download) != 1 ||
         sizeof($this->contents->download->children()) != 0) {
         $this->throwOrCollect(new InvalidXML('field', 'download', '<download> should be a singular field without children'));
      }
      return true;
   }

   public function validateIssues() {
      if (sizeof($this->contents->issues) != 1 ||
         sizeof($this->contents->issues->children()) != 0) {
         $this->throwOrCollect(new InvalidXML('field', 'issues', '<issues> should be a singular field without children'));
      }
      return true;
   }

   public function validateReadme() {
      if (sizeof($this->contents->readme) != 1 ||
         sizeof($this->contents->readme->children()) != 0) {
         $this->throwOrCollect(new InvalidXML('field', 'readme', '<readme> should be a singular field without children'));
      }
      return true;
   }

   public function validateAuthors() {
      if (sizeof($this->contents->authors) != 1 ||
         sizeof($this->contents->authors->children()) < 1) {
         $this->throwOrCollect(new InvalidXML('field', 'authors', '<authors> should contain at least one <author>'));
      }

      foreach ($this->contents->authors->children() as $tag => $author) {
         if ($tag != 'author') {
            $this->throwOrCollect(new InvalidXML('field', 'authors.'.$tag, '<authors> should contain only <author> tags'));
         }
         if (sizeof($author->children()) != 0) {
            $this->throwOrCollect(new InvalidXML('field', 'authors.author', '<author> should be a singular field without children'));
         }
      }

      return true;
   }

   public function validateVersions() {
      if (sizeof($this->contents->versions) != 1 ||
         sizeof($this->contents->versions->children()) < 1) {
         $this->throwOrCollect(new InvalidXML('field', 'versions', '<versions> should contain at least one <version>'));
      }

      foreach ($this->contents->versions->children() as $version) {
         foreach ($version->children() as $prop => $val) {
            if (!in_array($prop, ['num', 'compatibility'])) {
               $this->throwOrCollect(new InvalidXML('field', 'versions.'.$prop, '<version> should contain only <num> and <compatibility>'));
            }
         }
      }
      return true;
   }

   public function validateLangs() {
      if (sizeof($this->contents->langs) != 1 ||
         sizeof($this->contents->langs->children()) < 1) {
         $this->throwOrCollect(new InvalidXML('field', 'langs', '<langs> should contain at least one <lang>'));
      }

      foreach ($this->contents->langs->children() as $tag => $lang) {
         if ($tag != 'lang') {
            $this->throwOrCollect(new InvalidXML('field', 'langs'.$tag, '<langs> should contain only <lang> tags'));
         }

         if (sizeof($lang->children()) != 0) {
            $this->throwOrCollect(new InvalidXML('field', 'langs.lang', '<lang> should be a singular field without children'));
         }

         if (strlen((string)$lang) > 5) {
            $this->throwOrCollect(new InvalidXML('field', 'langs.lang', '<lang> shouldnt exceed 5 characters'));
         }
      }
      return true;
   }

   public function validateLicense() {
      if (sizeof($this->contents->license) != 1 ||
         sizeof($this->contents->license->children()) != 0) {
         $this->throwOrCollect(new InvalidXML('field', 'license', '<license> should be a singular field without children'));
      }
      return true;
   }

   public function validateTags() {
      if (sizeof($this->contents->tags) != 1 ||
         sizeof($this->contents->tags->children()) < 1) {
         $this->throwOrCollect(new InvalidXML('field', 'tags', '<tags> should contain at least one <[lang]>'));
      }

      foreach ($this->contents->tags->children() as $lang => $tags) {
         foreach ($tags->children() as $prop => $tag) {
            if ($prop != 'tag') {
               $this->throwOrCollect(new InvalidXML('field', 'tags.'.$lang, '<[lang]> should contain only <tag> tags'));
            }
            if (sizeof($tag->children()) != 0) {
               $this->throwOrCollect(new InvalidXML('field', 'tags.'.$lang.'.tag', '<tag> should be a singular field without children'));
            }
         }
      }
      return true;
   }

   public function validateScreenshots() {
      foreach ($this->contents->screenshots->children() as $tag => $screenshot) {
         if ($tag != 'screenshot') {
            $this->throwOrCollect(new InvalidXML('field', 'screenshots.'.$tag, '<screenshots> should contain only <screenshot> tags'));
         }
         if (sizeof($screenshot->children()) != 0) {
            $this->throwOrCollect(new InvalidXML('field', 'screenshots.screenshot', '<screenshot> should be a singular field without children'));
         }
      }
      return true;
   }

   public function __construct($contents, $collectMode = false) {
      $this->collectMode = $collectMode;
      libxml_use_internal_errors(true);
      $this->contents = @simplexml_load_string($contents);
      if ($this->contents) {
         $this->parsable = true;
      } else {
         $this->parsable = false;
         $error = libxml_get_errors()[0];
         $this->throwOrCollect(new InvalidXML('parse', $error->line, trim($error->message)));
      }
   }

   public function allFieldsOK() {
      foreach ($this->contents->children() as $tag => $node) {
         $methodName = 'validate'.strtoupper($tag[0]).substr($tag,1);
         if (method_exists($this, $methodName)) {
            call_user_func([$this, $methodName]);
         }
      }
      if (sizeof($this->errors) > 0) {
         return false;
      }
      return true;
   }

   public function hasAllRequiredFields() {
      foreach($this->requiredFields as $field) {
         if (sizeof($this->contents->$field) < 1) {
            $this->throwOrCollect(new InvalidXML('field', $field, "missing mandatory <".$field.">"));
            return false;
         }
      }
      return true;
   }

   public function throwOrCollect(InvalidXML $exception) {
      if ($this->collectMode) {
         $this->errors[] = $exception;
      } else {
         throw $exception;
      }
   }

   public function validate() {
      return ($this->parsable &&
              $this->allFieldsOK() &&
              $this->hasAllRequiredFields());
   }
}