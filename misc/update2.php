<?php
/**
 * update.php
 *
 * This script should be fired by a crontab
 * to update the database according to all
 * the url and checksums.
 */

require 'api/vendor/autoload.php';

// configuration parameters
require 'api/config.php';

use \Illuminate\Database\Capsule\Manager as DB;
use \API\Model\Plugin;
use \API\Model\PluginDescription;

// Connecting to MySQL
\API\Core\DB::initCapsule();

$plugins = Plugin::get();

// Going to compare checksums
// for each of these plugins

foreach($plugins as $id => $plugin) {
    // Defaults not to update
    $update = false;
    // fetching via http
    $xml = file_get_contents($plugin->xml_url);
    $crc = md5($xml);
    if ($plugin->xml_crc != $crc ||
        $plugin->name == NULL) {
        $update = true; // if we got
        // missing name or changing
        // crc, then we're going to
        // update that one
    }
    // Now loading OO-style with simplemxl
    $xml = simplexml_load_string($xml);

    $plugin->logo_url = $xml->logo;
    $plugin->name = $xml->name;
    $plugin->key = $xml->key;
    $plugin->homepage_url = $xml->homepage;
    $plugin->download_url = $xml->download;
    $plugin->issues_url = $xml->issues;
    $plugin->readme_url  = $xml->readme;
    $plugin->license = $xml->license;

    //$plugin->descriptions()->delete();
    
    foreach ($xml->description->children() as $type => $descs) {
        if (in_array($type, ['short','long'])) {
            foreach($descs->children() as $lang => $content) {
                var_dump('1:'.$type);
                var_dump('2:'.$lang);  
                var_dump('3:'.(string)$content);              
            }
            // $description = new PluginDescription;
            // $plugin[$type.'_description'] = $v
        }
        // foreach($v as $v2) {
        //     var_dump((string)$v2->fr);
        // }
    }

    // update crc with new one
    $plugin->xml_crc = $crc;
}