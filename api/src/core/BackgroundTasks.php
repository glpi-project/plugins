<?php

namespace API\Core;

use API\Exception\InvalidXML;
use API\Model\AccessToken;
use API\Model\Author;
use API\Model\Plugin;
use API\Model\PluginDescription;
use API\Model\PluginLang;
use API\Model\PluginScreenshot;
use API\Model\PluginVersion;
use API\Model\RefreshToken;
use API\Model\Session;
use API\Model\Tag;
use GuzzleHttp\Client as GuzzleHttpClient;
use HtmlSanitizer\Sanitizer;
use Laminas\Uri\Uri;

class BackgroundTasks {
   public $currentXml;
   public $currentPluginState;
   private $silentMode;
   private $throwsExceptions;
   private $pluginMaxConsecutiveXmlFetchFails;

   /**
    * Limit used for paginated processes.
    * @var integer
    */
   const PAGE_LIMIT = 1000;

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
            if (in_array('alert_plugin_team_on_xml_state_change', $tasks)) {
               $subtasks[] = 'alert_plugin_team_on_xml_state_change';
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
      $page      = 0;
      $limit     = self::PAGE_LIMIT;
      $n_deleted = 0;

      $this->outputStr("Analyzing ". AccessToken::count() ." access tokens...\n");

      do {
         $accessTokens = AccessToken::with('session')
            ->skip($page * $limit - $n_deleted)
            ->take($limit)
            ->get();

         foreach ($accessTokens as $accessToken) {
            if (in_array('delete_AT_if_expired', $tasks)) {
               if ($this->deleteAccessTokenIfExpired($accessToken)) {
                  $n_deleted++;
               }
            }
         }

         $page++;
      } while ($accessTokens->count() > 0);

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
      $page      = 0;
      $limit     = self::PAGE_LIMIT;
      $n_deleted = 0;

      $this->outputStr("Analyzing ". RefreshToken::count() ." refresh tokens...\n");

      do {
         $refreshTokens = RefreshToken::with('accessToken')
            ->skip($page * $limit - $n_deleted)
            ->take($limit)
            ->get();

         foreach ($refreshTokens as $refreshTokens) {
            if (in_array('delete_lonely_RT', $tasks)) {
               if ($this->deleteLonelyRefreshToken($refreshTokens)) {
                  $n_deleted++;
               }
            }
         }

         $page++;
      } while ($refreshTokens->count() > 0);

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
      $page      = 0;
      $limit     = self::PAGE_LIMIT;
      $n_deleted = 0;

      $this->outputStr("Analyzing ". Session::count() ." sessions...\n");

      do {
         $sessions = Session::with('accessToken')
            ->skip($page * $limit - $n_deleted)
            ->take($limit)
            ->get();

         foreach ($sessions as $session) {
            if (in_array('delete_lonely_session', $tasks)) {
               if ($this->deleteLonelySession($session)) {
                  $n_deleted++;
               }
            }
         }

         $page++;
      } while ($sessions->count() > 0);

      if (in_array('delete_lonely_session', $tasks) && $n_deleted > 0) {
         $this->outputStr(" deleted ".$n_deleted." lonely sessions.");
      }

      $this->outputStr("\n\n");
   }

   // Tasks for Plugins

   /**
    * Task : updatePlugin()
    *
    * Note: This function does direct output,
    * in fact it builds the log string
    * that concerns the update of a
    * plugin.
    */
   private function updatePlugin($plugin, $index = null, $length = null, $subtasks) {
      // Displaying index / length
      $this->outputStr('Plugin (' . $index . '/' . $length . ') (id #'. $plugin->id . '): ');

      $update = false;

      // This can be used to detect the state
      // in some way (think about it)
      $this->currentXml = null;
      $this->currentPluginState = null;

      // fetching via http
      $unableToFetch = false;
      $httpClient = new GuzzleHttpClient();
      try {
         $pluginXmlRequest = $httpClient->get($plugin->xml_url, [
            "headers" => [
               "User-Agent" => Tool::getConfig()['glpi_plugin_directory_user_agent']
            ]
         ]);
      } catch (\GuzzleHttp\Exception\RequestException $e) {
         $unableToFetch = true;
      } finally {
         if ($unableToFetch ||
             (!$unableToFetch && $pluginXmlRequest->getStatusCode() != 200)) {
            if ($this->pluginMaxConsecutiveXmlFetchFails) {
               $fetchFailCount = $plugin->incrementXmlFetchFailCount();
               if ($fetchFailCount == $this->pluginMaxConsecutiveXmlFetchFails) {
                  $this->triggerPluginXmlStateChange(
                     $plugin,
                     'bad_xml_url',
                     true,
                     in_array('alert_plugin_team_on_xml_state_change', $subtasks)
                  );
                  $plugin->resetXmlFetchFailCount();
               }
            } else {
               $this->triggerPluginXmlStateChange(
                  $plugin,
                  'bad_xml_url',
                  true,
                  in_array('alert_plugin_team_on_xml_state_change', $subtasks)
               );
            }
            $this->outputStr($plugin->xml_url."\" Cannot get XML file via HTTP, Skipping.\n");
            if ($this->throwsExceptions) {
               throw new InvalidXML('url', $plugin->xml_url);
            }
            return false;
         } else {
            $plugin->resetXmlFetchFailCount();
         }
      }

      $xml = $pluginXmlRequest->getBody();
      $this->currentXml = (string)$xml;

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
         $this->outputStr("\"" . $plugin->name . "\" Already up-to-date, Skipping.\n");
         $this->triggerPluginXmlStateChange(
            $plugin,
            'passing',
            true,
            in_array('alert_plugin_team_on_xml_state_change', $subtasks)
         );
         return false;
      }

      try {
         $xml = new ValidableXMLPluginDescription($xml);
         $xml->validate();
      }
      catch (\API\Exception\InvalidXML $e) {
         $_unreadable = '';
         if (isset($xml->contents) &&
             $xml->contents->name &&
             sizeof($xml->contents->name->children()) < 1 &&
             strlen((string)$xml->contents->name) < 80) {
            $_unreadable .= '"'.(string)$xml->contents->key . '" ';
         } elseif ($plugin->name) {
            $_unreadable .= '"'.$plugin->name.'" ';
         }
         $this->triggerPluginXmlStateChange(
            $plugin,
            'xml_error',
            true,
            in_array('alert_plugin_team_on_xml_state_change', $subtasks)
         );
         $_unreadable .= "Unreadable/Non validable XML, error: ".$e->getRepresentation()." Skipping.\n";
         $this->outputStr($_unreadable);
         if ($this->throwsExceptions) {
            throw $e;
         }
         return false;
      }

      $xml = $xml->contents;
      $xml_name = $this->sanitizeText($xml->name);

      if (!$plugin->name) {
         $this->outputStr("first time update, found name \"".$xml_name."\"...");
         if (Plugin::where('name', '=', $xml_name)->first()) {
            $this->outputStr(" already exists. skipping.");
            // this would be amazing to alert the administrators
            // of that. new Mailer; ?
            return false;
         }
         $firstTimeUpdate = true;
      }
      else {
         if ($plugin->name != $xml_name) {
            $this->outputStr(" requested name change to \"".$xml_name."\" ...");
            if (Plugin::where('name', '=', $xml_name)->first()) {
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
      $this->triggerPluginXmlStateChange(
         $plugin,
         'passing',
         true,
         in_array('alert_plugin_team_on_xml_state_change', $subtasks)
      );

      // Updating basic infos
      $plugin->logo_url = $this->sanitizeUrl($xml->logo);
      $plugin->name = $xml_name;
      $plugin->key =  $this->sanitizeText($xml->key);
      $plugin->homepage_url = $this->sanitizeUrl($xml->homepage);
      $plugin->download_url = $this->sanitizeUrl($xml->download);
      $plugin->issues_url = $this->sanitizeUrl($xml->issues);
      $plugin->readme_url  = $this->sanitizeUrl($xml->readme);
      $plugin->license = $this->sanitizeText($xml->license);

      // reading descriptions,
      // mapping type=>lang relation to lang=>type
      $descriptions = [];
      foreach ($xml->description->children() as $type => $descs) {
         if (in_array($type, ['short','long'])) {
            foreach($descs->children() as $_lang => $content) {
               $descriptions[$_lang][$type] = $this->sanitizeHtml((string)$content);
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
         $author = $this->sanitizeText((string)$author);
         $_clean_authors = Author::fixKnownDuplicates($author);
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
            $version->num = $this->sanitizeText(trim((string)$_version->num));
            $version->compatibility = $this->sanitizeText(trim((string)$compat));
            $version->download_url = $this->sanitizeUrl(trim((string)$_version->download_url));
            $version->plugin_id = $plugin->id;
            $version->save();
         }
      }

      // Refreshing screenshots
      if (isset($xml->screenshots)) {
         $plugin->screenshots()->delete();
         foreach ($xml->screenshots->children() as $url) {
            $screenshot = new PluginScreenshot;
            $screenshot->url = $this->sanitizeUrl((string)$url);
            $screenshot->plugin_id = $plugin->id;
            $screenshot->save();
         }
      }

      // Reassociating plugin to tags
      $plugin->tags()->detach();
      foreach($xml->tags->children() as $lang => $tags) {
         foreach($tags->children() as $_tag) {
            $_tag = $this->sanitizeText((string)$_tag);
            $_tag = substr($_tag, 0, 25); // Tag length is limited to 25 chars in DB
            $found = Tag::where('tag', '=', $_tag)
                        ->where('lang', '=', $lang)
                        ->first();
            if (sizeof($found) < 1) {
               $tag = new Tag;
               $tag->tag = $_tag;
               $tag->lang = $lang;
               $tag->key = Tool::getUrlSlug($_tag);
               $tag->save();
            }
            else $tag = $found;

            $tag->plugins()->attach($plugin);
         }
      }

      // Reassociating plugin to langs
      $plugin->langs()->detach();
      foreach ($xml->langs->children() as $lang) {
         $lang = $this->sanitizeText((string)$lang);

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
         $mailer->sendMail('plugin_updated.html',
                           [$user->email => $user->username],
                           'Plugin update "'.$plugin->name.'"',
                           ['plugin' => $plugin,
                           'user'   => $user,
                           'client_url' => Tool::getConfig()['client_url']]);
      }
   }

   /**
    * This method's goal is to change the xml_state of
    * a specific plugin.
    * It's behaviour depends of
    *  + the current state of the plugin
    *  + the list of admins or notified people
    *
    * In fact, this function returns void and
    * has no action if the state hasn't changed.
    */
   private function triggerPluginXmlStateChange($plugin, $xml_state, $save = true, $mail = true) {
      if (!in_array($xml_state, ['passing', 'bad_xml_url', 'xml_error'])) {
         return;
      }
      $this->currentPluginState = $xml_state;
      if ($plugin->xml_state != $xml_state) {
         $plugin->xml_state = $xml_state;
         if ($save) {
            $plugin->save();
         }
         if (in_array($xml_state, ['bad_xml_url', 'xml_error']) && $mail) {
            $this->alertAdminsOfXMLErrors($plugin);
         }
      }
   }

   private function alertAdminsOfXMLErrors($plugin) {
      $errors = [];

      if ($plugin->xml_state == 'bad_xml_url') {
         $errors[] = [
            'reason' => 'url',
            'url' => $plugin->xml_url
         ];
      }
      elseif ($plugin->xml_state == 'xml_error') {
         // Reevaluating Errors with previous plain-text xml,
         // using the collectMode of ValidableXMLPluginDescription
         $xml = new ValidableXMLPluginDescription($this->currentXml, true);
         $xml->validate();
         foreach ($xml->errors as $_error) {
            $error = [];
            $error['reason'] = $_error->getInfo('reason');
            switch ($error['reason']) {
               case 'parse':
                  $error['line'] = $_error->getInfo('line');
                  $error['errstring'] = $_error->getInfo('errstring');
               case 'field':
                  $error['field'] = $_error->getInfo('field');
                  $error['errstring'] = $_error->getInfo('errstring');
            }
            $errors[] = $error;
         }
      }
      else return;

      $permissions = $plugin->permissions;
      foreach ($permissions as $user) {
         if ($user->pivot->admin ||
             $user->pivot->allowed_notifications) {
            $mailer = new Mailer;
            $mailer->sendMail('xml_error.html',
                              [$user->email],
                              '"' . $plugin->key . '"' . ' Plugin\'s XML has turned invalid',
                              [
                                 'errors' => $errors,
                                 'plugin' => $plugin,
                                 'user' => $user
                              ]);
         }
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

   /**
    * Sanitize a text (remove HTML tags).
    *
    * @param string $text
    *
    * @return string
    */
   private function sanitizeText(string $text): string {
      $sanitizer = Sanitizer::create([]);
      $sanitized = $sanitizer->sanitize($text);
      return html_entity_decode($sanitized, ENT_QUOTES); // Decode quotes as result is not supposed to be a HTML code
   }

   /**
    * Sanitize a HTML string (remove unauthorized HTML tags).
    *
    * @param string $html
    *
    * @return string
    */
   private function sanitizeHtml(string $html): string {
      $sanitizer = Sanitizer::create(['extensions' => ['basic']]);
      return $sanitizer->sanitize($html);
   }

   /**
    * Sanitize an URL
    *
    * @param string $url
    *
    * @return string|null
    */
   private function sanitizeUrl(string $url): ?string {
      try {
         $uri = new Uri($url);
      } catch (\Laminas\Uri\Exception\InvalidArgumentException $e) {
         return null;
      }
      return $uri->isValid() && $uri->isAbsolute() && in_array($uri->getScheme(), ['http', 'https'])
         ? $uri->__toString()
         : null;
   }

   public function __construct($options = []) {
      // checking boolean presence of options
      // each of those line groups

      // has the 'silent' option
      if (isset($options['silent']) &&
          gettype($options['silent']) === 'boolean' &&
          $options['silent']) {
         $this->silentMode = true;
      }

      // has the 'throwsExceptions' option
      if (isset($options['throwsExceptions']) &&
          gettype($options['throwsExceptions']) === 'boolean' &&
          $options['throwsExceptions']) {
         $this->throwsExceptions = true;
      }

      // has the
      // plugin_max_consecutive_xml_fetch_fails
      // option
      if (isset($options['plugin_max_consecutive_xml_fetch_fails']) &&
          gettype($options['plugin_max_consecutive_xml_fetch_fails']) &&
          $options['plugin_max_consecutive_xml_fetch_fails']) {
         $this->pluginMaxConsecutiveXmlFetchFails = $options['plugin_max_consecutive_xml_fetch_fails'];
      }
   }
}
