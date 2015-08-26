<?php
/**
 * update.php
 *
 * This script should be fired by a crontab
 * to update the database according to all
 * the url and checksums.
 */

require __DIR__.'/../api/vendor/autoload.php';


use \Illuminate\Database\Capsule\Manager as DB;
use \API\Model\Author;
use \API\Model\Plugin;
use \API\Model\PluginDescription;
use \API\Model\PluginVersion;
use \API\Model\PluginScreenshot;
use \API\Model\Tag;
use \API\Core\Tool;
use \API\Core\ValidableXMLPluginDescription;

class DatabaseUpdater {
   public function __construct() {
      // Connecting to MySQL
      \API\Core\DB::initCapsule();
   }

   public function verifyAndUpdatePlugins() {
      $plugins = Plugin::where('active', '=', 1)
                   ->get();

      // Going to compare checksums
      // for each of these plugins
      $n = 0;
      foreach($plugins as $num => $plugin) {
         $n++;
         // Defaults not to update
         $update = false;
         // fetching via http
         $xml = @file_get_contents($plugin->xml_url);
         if (!$xml) {
            echo('Plugin (' . $n . '/'. sizeof($plugins) ."): \"".$plugin->name."\" Cannot get XML file via HTTP, Skipping.\n");
            continue;
         }
         $crc = md5($xml); // compute crc
         if ($plugin->xml_crc != $crc ||
            $plugin->name == NULL) {
            $update = true; // if we got
            // missing name or changing
            // crc, then we're going to
            // update that one
         }
         else {
            echo('Plugin (' . $n . '/'. sizeof($plugins) ."): \"".$plugin->name."\" Already updated, Skipping.\n");
            continue;
         }

         // loading XML OO-style with simplemxl
         $xml = new ValidableXMLPluginDescription($xml);
         if (!$xml->isValid()) {
            echo('Plugin (' . $n . '/'. sizeof($plugins) ."): \"".$plugin->name."\" Unreadable/Non validable XML, Skipping.\n");
            echo("Errors: \n");
            foreach ($xml->errors as $error)
               echo (" - ".$error."\n");
            continue;
         }
         $xml = $xml->contents;

         echo('Plugin (' . $n . '/'. sizeof($plugins) .'): Updating ... ');
         $this->updatePlugin($plugin, $xml, $crc);
      }
   }

   public function updatePlugin($plugin, $xml, $new_crc) {
      if (strlen($plugin->name) == 0) {
         echo "first time update, found name \"".$xml->name."\"...";
         $firstTimeUpdate = true;
      }
      else {
         if ($plugin->name != $xml->name)
            echo "\"".$plugin->name."\" going to become \"".$xml->name."\" ...";
         else
            echo "\"".$xml->name."\" going to be updated ...";
      }
      // Updating basic infos
      $plugin->logo_url = $xml->logo;
      $plugin->name = $xml->name;
      $plugin->key = $xml->key;
      $plugin->homepage_url = $xml->homepage;
      $plugin->download_url = $xml->download;
      $plugin->issues_url = $xml->issues;
      $plugin->readme_url  = $xml->readme;
      $plugin->license = $xml->license;

      // reading descriptions,
      // mapping type=>lang relation to lang=>type
      $descriptions = [];
      foreach ($xml->description->children() as $type => $descs) {
         if (in_array($type, ['short','long'])) {
            foreach($descs->children() as $_lang => $content) {
               $descriptions[$_lang][$type] = (string)$content;          
            }
         }
      }

      // Delete current descriptions
      $plugin->descriptions()->delete();
      // Refreshing descriptions
      foreach($descriptions as $lang => $_type) {
         $description = new PluginDescription;
         $description->lang = $lang;
         foreach($_type as $type => $html) {
            $description[$type.'_description'] = $html;
         }
         $description->plugin_id = $plugin->id;
         $description->save();
      }

      // Refreshing authors
      $plugin->authors()->detach();
      $clean_authors = [];
      foreach($xml->authors->children() as $author) {
         $_clean_authors = $this->fixParseAuthors((string)$author);
         foreach ($_clean_authors as $author) {
            $clean_authors[] = $author;
         }
      }
      foreach ($clean_authors as $_author) {
         $found = Author::where('name', '=', $_author)->first();
         if (sizeof($found) < 1) {
            $author = new Author;
            $author->name = $_author;
            $author->save();
         }
         else {
            $author = $found;
         }

         if (!$plugin->authors->find($author->id)) {
            $plugin->authors()->attach($author);
         }
      }

      // Refreshing versions
      $plugin->versions()->delete();
      foreach($xml->versions->children() as $_version) {
         foreach ($_version->compatibility as $compat) {
            $version = new PluginVersion;
            $version->num = trim((string)$_version->num);
            $version->compatibility = trim((string)$compat);
            $version->plugin_id = $plugin->id;
            $version->save();
         }
      }

      // Refreshing screenshots
      if (isset($xml->screenshots)) {
         $plugin->screenshots()->delete();
         foreach ($xml->screenshots->children() as $url) {
            $screenshot = new PluginScreenshot;
            $screenshot->url = (string)$url;
            $screenshot->plugin_id = $plugin->id;
            $screenshot->save();
         }
      }

      // Reassociating plugin to tags
      $plugin->tags()->detach();
      foreach($xml->tags->children() as $lang => $tags) {
         foreach($tags->children() as $_tag) {
            $found = Tag::where('tag', '=', (string)$_tag)
                        ->where('lang', '=', $lang)
                        ->first();
            if (sizeof($found) < 1) {
               $tag = new Tag;
               $tag->tag = (string)$_tag;
               $tag->lang = $lang;
               $tag->key = Tool::getUrlSlug((string)$_tag);
               $tag->save();
            }
            else $tag = $found;

            $tag->plugins()->attach($plugin);
         }
      }

      // new crc
      $plugin->xml_crc = $new_crc;
      // new timestamp
      if (!isset($firstTimeUpdate)) {
         $plugin->date_updated = DB::raw('NOW()');
      }
      $plugin->save();
      echo " OK\n";
   }

   /*
    * fixParseAuthors()
    *
    * This function is very specific,
    * it aims to provide a fix to current
    * state of things in xml files.
    *
    * Currently, some authors are duplicates,
    * and spelled differently depending on
    * plugins, this functions aims to ensure
    * correct detection of EACH author.
    *
    * This function shouldn't be here and might
    * dissapear someday.
    */
   private $fpa_separators = [',', '/'];
   private $fpa_duplicates = [
      [
         "names" => ['Xavier Caillaud / Infotel',
                  'Xavier CAILLAUD'],
         "ends"  => 'Xavier Caillaud'
      ],
      [
         "names" => ['Nelly LASSON',
                  'Nelly MAHU-LASSON'],
         "ends"  => 'Nelly Mahu-Lasson'
      ],
      [
         "names" => ['David DURIEUX'],
         "ends"  => 'David Durieux'
      ],
      [
         "names" => ['Olivier DURGEAU'],
         "ends"  => 'Olivier Durgeau'
      ],
      [
         "names" => ['Yohan BOUSSION'],
         "ends"  => 'Yohan Boussion'
      ],
      [
         "names" => ['Philippe GODOT'],
         "ends"  => 'Philippe Godot'
      ],
      [
         "names" => ['Cyril ZORMAN'],
         "ends"  => 'Cyril Zorman'
      ],
      [
         "names" => ['Maxime BONILLO'],
         "ends"  => 'Maxime Bonillo'
      ],
      [
         "names" => ['Philippe THOIREY'],
         "ends"  => 'Philippe Thoirey'
      ]
   ];
   public function fixParseAuthors($author_string) {
      $detectedAuthors = [];
      // empty string
      if ($author_string == '') {
         return $detectedAuthors;
      }
      // detecting known duplicates
      foreach($this->fpa_duplicates as $known_duplicate) {
         foreach ($known_duplicate['names'] as $known_name) {
               if (preg_match('/'.preg_quote($known_name, '/').'/', $author_string)) {
                  $author_string = preg_replace('/'.preg_quote($known_name, '/').'/',
                                         $known_duplicate['ends'],
                                         $author_string);
               }
         }
      }

      // detecting inline multiple authors
      foreach($this->fpa_separators as $separator) {
         $found_authors = explode($separator, $author_string);
         if (sizeof($found_authors) > 1) {
            foreach ($found_authors as $author) {
               $detectedAuthors[] = trim($author);
            }
            break;
         }
      }

      if (sizeof($detectedAuthors) == 0) {
         return [trim($author_string)];
      } else {
         return $detectedAuthors;
      }
   }
}

$db_updater = new DatabaseUpdater;
$db_updater->verifyAndUpdatePlugins();
