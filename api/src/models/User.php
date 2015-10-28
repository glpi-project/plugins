<?php

namespace API\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class User extends Model {
   protected $table = 'user';
   protected $visible = ['id', 'active', 'email', 'username',
                         'realname', 'location', 'website',
                         'author_id', 'gravatar', 'pivot'];


   // Relations

   // The plugin author associated with this account
   public function author() {
      return $this->belongsTo('\API\Model\Author');
   }
   // External social accounts the user has
   public function externalAccounts() {
      return $this->hasMany('\API\Model\UserExternalAccount');
   }
   // API Keys the user has
   public function apps() {
      return $this->hasMany('\API\Model\App');
   }
   // Plugins the user watch (@todo; remove the PluginWatch class,
   //                                keep a straight  belongsToMany)
   public function watchs() {
      return $this->hasMany('\API\Model\PluginWatch');
   }
   // Sessions the user have
   public function sessions() {
      return $this->hasMany('\API\Model\Session', 'owner_id');
   }
   // Plugins user has right onto
   public function pluginPermissions() {
      return $this->belongsToMany('\API\Model\Plugin', 'plugin_permission', 'user_id')
                  ->withPivot('admin', 'allowed_refresh_xml', 'allowed_change_xml_url', 'allowed_notifications');
   }

   // Setters

   /**
    * Hash and set the given password for
    * the current model
    */
   public function setPassword($password) {
      $this->password = password_hash($password, PASSWORD_BCRYPT);
   }

   /**
    * Compares the hash of given $password
    * too the one we have in database for
    * current model,
    * sends true if the correct password is given
    */
   public function assertPasswordIs($password) {
      return password_verify($password, $this->password);
   }

   // Validation functions

   /**
    * Returns true if the given $password respects
    * our specifications
    */
   public static function isValidPassword($password) {
      if (gettype($password) == 'string' &&
          strlen($password) >= 6 && strlen($password) <= 26) {
         return true;
      }
      return false;
   }

   public static function isValidWebsite($website) {
    if (gettype($website) == 'string' &&
        filter_var($website, FILTER_VALIDATE_URL)) {
      return true;
    }
    return false;
   }

   /**
    * Returns true if the given $realname respects
    * our specification
    */
   public static function isValidRealname($realname) {
      if (gettype($realname) == 'string' &&
          strlen($realname) >= 4 &&
          strlen($realname) <= 28) {
         return true;
      }
      return false;
   }
}