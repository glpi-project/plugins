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

foreach($plugins as $num => $plugin) {
    // Defaults not to update
    $update = false;
    // fetching via http
    $xml = file_get_contents($plugin->xml_url);
    $crc = md5($xml); // compute crc
    if ($plugin->xml_crc != $crc ||
        $plugin->name == NULL) {
        $update = true; // if we got
        // missing name or changing
        // crc, then we're going to
        // update that one
    }
    // Now loading OO-style with simplemxl
    $xml = simplexml_load_string($xml);

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
    //$plugin->descriptions()->delete();
    // Refreshing them
    foreach($descriptions as $lang => $_type) {
        $description = new PluginDescription;
        $description->lang = $lang;
        foreach($_type as $type => $html) {
            $description[$type.'_description'] = $html;
        }
        //$plugin->descriptions()->save($description);
    }

    // Delete current authors
    //$plugin->authors()->delete();
    foreach($xml->authors->children() as $author) {
        var_dump((string)$author);
    }

    // Now going to think about making
    // the datamodel evolve
    // and also handle corruption in some
    // xml files.
    // for the datamodel, i'd need a
    // join table between authors and plugins

    $plugin->xml_crc = $crc;
}