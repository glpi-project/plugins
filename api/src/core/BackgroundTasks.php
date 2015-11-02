<?php

namespace API\Core;

use \API\Model\Author;
use \API\Model\Plugin;
use \API\Model\PluginDescription;
use \API\Model\PluginVersion;
use \API\Model\PluginScreenshot;
use \API\Model\PluginLang;
use \API\Model\Tag;
use \API\Model\AccessToken;
use \API\Model\RefreshToken;
use \API\Model\Session;
use \API\Core\Tool;
use \API\Core\ValidableXMLPluginDescription;

class BackgroundTasks {
   public $lastXml;
   private $silentMode;

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

      $this->outputStr("Analyzing ".$l." plugins... \n\n");

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

      $this->outputStr("\n");
   }

   /**
    * Tries to locate a single plugin
    * by it's key and execute the given
    * tasks on it if found.
    */
   public function wherePluginKeyIs($key, $tasks) {
      $plugin = Plugin::where('key', '=', $key)->first();
      if ($plugin) {
         if (in_array('update', $tasks)) {
            $this->updatePlugin($plugin, 1, 1, []);
         }
      } else {
         $this->outputStr('Plugin not found "'.$key.'"'."\n");
      }
   }

   public function wherePluginIdIs($id, $tasks) {
      $plugin = Plugin::where('active', '=', true)->find($id);
      if ($plugin) {
         if (in_array('update', $tasks)) {
            $this->updatePlugin($plugin, 1, 1, []);
         }
      } else {
         $this->outputStr('Plugin #'.$id.' not found '."\n");
      }
   }

   /**
    * Triggers the given list of tasks
    * on each access token if task
    * is supported on access tokens
    */
   public function foreachAccessToken($tasks) {
      $accessTokens = AccessToken::get();
      $this->outputStr("Analyzing ".sizeof($accessTokens)." access tokens...");

      $n_deleted = 0;

      foreach ($accessTokens as $accessToken) {
         if (in_array('delete_AT_if_expired', $tasks)) {
            if ($this->deleteAccessTokenIfExpired($accessToken)) {
               $n_deleted++;
            }
         }
      }

      if (in_array('delete_AT_if_expired', $tasks) && $n_deleted > 0) {
         $this->outputStr(" deleted ".$n_deleted." perempted access tokens.");
      }

      $this->outputStr("\n\n");
   }

   /**
    * Triggers the given list of tasks
    * on each refresh token if task
    * is supported on refresh tokens
    */
   public function foreachRefreshToken($tasks) {
      $refreshTokens = RefreshToken::get();

      $this->outputStr("Analyzing ".sizeof($refreshTokens)." refresh tokens...");

      $n_deleted = 0;

      foreach ($refreshTokens as $refreshTokens) {
         if (in_array('delete_lonely_RT', $tasks)) {
            if ($this->deleteLonelyRefreshToken($refreshTokens)) {
               $n_deleted++;
            }
         }
      }

      if (in_array('delete_lonely_RT', $tasks) && $n_deleted > 0) {
         $this->outputStr(" deleted ".$n_deleted." lonely refresh tokens.");
      }

      $this->outputStr("\n\n");
   }

   /**
    * Triggers the given list of tasks
    * on each session if task
    * is supported on sessions
    */
   public function foreachSession($tasks) {
      $sessions = Session::get();

      $this->outputStr("Analyzing ".sizeof($sessions)." sessions...");

      $n_deleted = 0;

      foreach ($sessions as $session) {
         if (in_array('delete_lonely_session', $tasks)) {
            if ($this->deleteLonelySession($session)) {
               $n_deleted++;
            }
         }
      }

      if (in_array('delete_lonely_session', $tasks) && $n_deleted > 0) {
         $this->outputStr(" deleted ".$n_deleted." lonely sessions.");
      }

      $this->outputStr("\n\n");
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
   private function updatePlugin($plugin, $index = null, $length = null, $subtasks) {
      // Displaying index / length
      $this->outputStr('Plugin (' . $index . '/'. $length . ') (id #'. $plugin->id . '): ');

      $update = false;

      // fetching via http
      $xml = @file_get_contents($plugin->xml_url);
      if (!$xml) {
         $plugin->xml_state = 'bad_xml_url';
         $plugin->save();
         $this->outputStr($plugin->xml_url."\" Cannot get XML file via HTTP, Skipping.\n");
         return false;
      } else {
         $this->lastXml = $xml;
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
         $plugin->xml_state = 'passing';
         $plugin->save();
         $this->outputStr("\"" . $plugin->name . "\" Already up-to-date, Skipping.\n");
         return false;
      }

      $xml = new ValidableXMLPluginDescription($xml);
      try {
         $xml->validate();
      }
      catch (\API\Exception\InvalidXML $e) {
         $_unreadable = '';
         if ($xml->contents->name &&
             sizeof($xml->contents->name->children()) < 1 &&
             strlen((string)$xml->contents->name) < 80) {
            $_unreadable .= '"'.(string)$xml->contents->key . '" ';
         } elseif ($plugin->name) {
            $_unreadable .= '"'.$plugin->name.'" ';
         }
         $plugin->xml_state = 'xml_error';
         $plugin->save();
         $_unreadable .= "Unreadable/Non validable XML, error: ".$e->getRepresentation()." Skipping.\n";
         $this->outputStr($_unreadable);
         return false;
      }

      $xml = $xml->contents;

      if (!$plugin->name) {
         $this->outputStr("first time update, found name \"".$xml->name."\"...");
         if (Plugin::where('name', '=', $xml->name)->first()) {
            $this->outputStr(" already exists. skipping.");
            // this would be amazing to alert the administrators
            // of that. new Mailer; ?
            return false;
         }
         $firstTimeUpdate = true;
      }
      else {
         if ($plugin->name != $xml->name) {
            $this->outputStr(" requested name change to \"".$xml->name."\" ...");
            if (Plugin::where('name', '=', $xml->name)->first()) {
               $this->outputStr(" but name already exists. skipping.");
               // this would be amazing to alert the administrators
               // of that. new Mailer; ?
               return false;
            }
         }
         $firstTimeUpdate = false;
         $this->outputStr("\"".$plugin->name."\"");
      }

      $this->outputStr(" going to be synced with xml ...");
      $plugin->xml_state = 'passing';

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

      // Reassociating plugin to langs
      $plugin->langs()->detach();
      foreach ($xml->langs->children() as $lang) {
         $lang = (string)$lang;

         $_lang = PluginLang::where('lang', '=', $lang)->first();
         if (!$_lang) {
            $_lang = new PluginLang;
            $_lang->lang = $lang;
            $_lang->save();
         }

         $_lang->plugins()->attach($plugin);
      }

      // new crc
      $plugin->xml_crc = $crc;
      // new updated timestamp
      $plugin->date_updated = \Illuminate\Database\Capsule\Manager::raw('NOW()');
      $plugin->save();
      $this->outputStr(" OK.");
      if (in_array('alert_watchers', $subtasks)) {
         $this->alertWatchers($plugin);
         $this->outputStr("\n");
      } else {
         $this->outputStr("\n");
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

   private function deleteAccessTokenIfExpired($accessToken) {
      if ($accessToken->isExpired()) {
         $accessToken->delete();
         return true;
      }
      return false;
   }

   // Tasks for Refresh Tokens

   private function deleteLonelyRefreshToken($refreshToken) {
      if ($refreshToken->isAlone()) {
         $refreshToken->delete();
         return true;
      }
      return false;
   }

   // Tasks for Sessions

   private function deleteLonelySession($session) {
      if ($session->isAlone()) {
         $session->delete();
         return true;
      }
      return false;
   }

   private function outputStr($str) {
      if (!$this->silentMode) {
         echo $str;
      }
   }

   public function __construct($options = []) {
      if (isset($options['silent']) &&
          gettype($options['silent']) === 'boolean' &&
          $options['silent']) {
         $this->silentMode = true;
      }
   }
}