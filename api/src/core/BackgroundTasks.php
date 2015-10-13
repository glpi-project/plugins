<?php

namespace API\Core;

use \Illuminate\Database\Capsule\Manager as DB;
use \API\Model\Author;
use \API\Model\Plugin;
use \API\Model\PluginDescription;
use \API\Model\PluginVersion;
use \API\Model\PluginScreenshot;
use \API\Model\Tag;
use \API\Model\AccessToken;
use \API\Model\RefreshToken;
use \API\Model\Session;
use \API\Core\Tool;
use \API\Core\ValidableXMLPluginDescription;

class BackgroundTasks {
   public function __construct() {
      // Connecting to MySQL
      \API\Core\DB::initCapsule();
   }

   /**
    * Triggers the given list of tasks
    * on each plugin if task is supported
    * on plugins, and also
    * divides tasks into subtasks, and
    * passes them to the a task
    * that supports them
    */
   public function foreachPlugin($tasks) {
      $plugins = Plugin::where('active', '=', 1)
                       ->get();

      $n = 0;
      $l = sizeof($plugins);

      echo "Analyzing ".$l." plugins... \n\n";

      foreach($plugins as $num => $plugin) {
         $n++;
         if (in_array('update', $tasks)) {
            $subtasks = [];
            if (in_array('alert_watchers', $tasks)) {
               $subtasks[] = 'alert_watchers';
            }
            $this->updatePlugin($plugin, $n, $l, $subtasks);
         }
      }

      echo "\n";
   }

   /**
    * Triggers the given list of tasks
    * on each access token if task
    * is supported on access tokens
    */
   public function foreachAccessToken($tasks) {
      $accessTokens = AccessToken::get();
      echo "Analyzing ".sizeof($accessTokens)." access tokens...";

      $n_deleted = 0;

      foreach ($accessTokens as $accessToken) {
         if (in_array('delete_AT_if_perempted', $tasks)) {
            if ($this->deleteAccessTokenIfPerempted($accessToken)) {
               $n_deleted++;
            }
         }
      }

      if (in_array('delete_AT_if_perempted', $tasks) && $n_deleted > 0) {
         echo " deleted ".$n_deleted." perempted access tokens.";
      }

      echo "\n\n";
   }

   /**
    * Triggers the given list of tasks
    * on each refresh token if task
    * is supported on refresh tokens
    */
   public function foreachRefreshToken($tasks) {
      $refreshTokens = RefreshToken::get();

      echo "Analyzing ".sizeof($refreshTokens)." refresh tokens...";

      $n_deleted = 0;

      foreach ($refreshTokens as $refreshTokens) {
         if (in_array('delete_lonely_RT', $tasks)) {
            if ($this->deleteLonelyRefreshToken($refreshTokens)) {
               $n_deleted++;
            }
         }
      }

      if (in_array('delete_lonely_RT', $tasks) && $n_deleted > 0) {
         echo " deleted ".$n_deleted." lonely refresh tokens.";
      }

      echo "\n\n";
   }

   /**
    * Triggers the given list of tasks
    * on each session if task
    * is supported on sessions
    */
   public function foreachSession($tasks) {
      $sessions = Session::get();

      echo "Analyzing ".sizeof($sessions)." sessions...";

      $n_deleted = 0;

      foreach ($sessions as $session) {
         if (in_array('delete_lonely_session', $tasks)) {
            if ($this->deleteLonelySession($session)) {
               $n_deleted++;
            }
         }
      }

      if (in_array('delete_lonely_session', $tasks) && $n_deleted > 0) {
         echo " deleted ".$n_deleted." lonely sessions.";
      }

      echo "\n\n";
   }

   // Tasks for Plugins

   /**
    * Task : updatePlugin()
    *
    * This function does direct output,
    * in fact it builds the log string
    * that concerns the update of a
    * plugin.
    */
   private function updatePlugin($plugin, /*$xml, $new_crc,*/ $index = null, $length = null, $subtasks) {
      // Displaying index / length
      echo('Plugin (' . $index . '/'. $length . "): ");

      $update = false;

      // fetching via http
      $xml = @file_get_contents($plugin->xml_url);
      if (!$xml) {
         echo($plugin->xml_url."\" Cannot get XML file via HTTP, Skipping.\n");
         return false;
      }
      $crc = md5($xml); // compute crc
      if ($plugin->xml_crc != $crc ||
          $plugin->name == NULL) {
          $update = true; // if we got
         // missing name or changing
         // crc, then we're going to
         // update that one.
         // missing name means it's
         // the first time the plugin
         // is updated
      }
      else {
         echo ("\"" . $plugin->name . "\" Already up-to-date, Skipping.\n");
         return false;
      }

      $xml = new ValidableXMLPluginDescription($xml);
      try {
         $xml->validate();
      }
      catch (\API\Exception\ErrorResponse $e) {
         echo($plugin->name . "\" Unreadable/Non validable XML, Skipping.\n");
         return false;
      }

      $xml = $xml->contents;

      if (!$plugin->name) {
         echo "first time update, found name \"".$xml->name."\"...";
         if (Plugin::where('name', '=', $xml->name)->first()) {
            echo " already exists. skipping.";
            // this would be amazing to alert the administrators
            // of that. new Mailer; ?
            return false;
         }
         $firstTimeUpdate = true;
      }
      else {
         if ($plugin->name != $xml->name) {
            echo " requested name change to \"".$xml->name."\" ...";
            if (Plugin::where('name', '=', $xml->name)->first()) {
               echo " but name already exists. skipping.";
               // this would be amazing to alert the administrators
               // of that. new Mailer; ?
               return false;
            }
         }
         $firstTimeUpdate = false;
         echo "\"".$plugin->name."\"";
      }

      echo " going to be synced with xml ...";

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
         $_clean_authors = Author::fixKnownDuplicates((string)$author);
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
      $plugin->xml_crc = $crc;
      // new timestamp
      if (!isset($firstTimeUpdate)) {
         $plugin->date_updated = DB::raw('NOW()');
      }
      $plugin->save();
      echo " OK.";
      if (in_array('alert_watchers', $subtasks)) {
         $this->alertWatchers($plugin);
         echo "\n";
      } else {
         echo "\n";
      }
   }

   private function alertWatchers($plugin) {
      $client_url = Tool::getConfig()['client_url'];
      foreach ($plugin->watchers()->get() as $watch) {
         $user = $watch->user;
         $mailer = new Mailer;
         $mailer->sendMail('plugin_updated.html', Tool::getConfig()['msg_alerts']['local_admins'],
                           'Plugin update "'.$plugin->name.'"',
                           ['plugin' => $plugin,
                            'user'   => $user,
                            'client_url' => Tool::getConfig()]);
      }
   }

   // Tasks for Access Tokens

   private function deleteAccessTokenIfPerempted() {
      return true;
   }

   // Tasks for Refresh Tokens

   private function deleteLonelyRefreshToken() {
      return true;
   }

   // Tasks for Sessions

   private function deleteLonelySession() {
      return true;
   }
}