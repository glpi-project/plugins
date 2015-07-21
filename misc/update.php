<?php
/**
 * update.php
 *
 * This script should be started by a crontab to
 * update the database according to all the
 * url and checksums.
 */

// Using the same dependencies as the API does
require 'api/vendor/autoload.php';
// Plus grabbing the same database credentials
require 'api/config.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection($db_settings);
$capsule->bootEloquent();
$capsule->setAsGlobal();

// Grabbing all the plugins
$plugins = Capsule::table('plugin')->get();

// For each of these plugins
$i = 1;
foreach ($plugins as $plugin) {
	$update = false;

	// Fetching file via HTTP
	$xml = file_get_contents($plugin->xml_url);

	// Comparing checksums...
	// The first-class reason to update.
	$db_checksum = $plugin->xml_crc;
	$current_checksum = md5($xml);
	if ($db_checksum != $current_checksum) {
		$update = true;
		echo 'different checksums';
	}

	// If the plugin name is not available
	// The data might be incomplete
	// this is another reason to update
	if ($plugin->name == NULL) {
		$update = true;
		echo 'never fetched plugin';
	}

	// Now Parsing... thanks to SimpleXML!
	$xml = simplexml_load_string($xml);

	if ($update) {	
		// for now, not doing any checkup on
		// specific fields, just copying xml
		// data, which is the reference data
		Capsule::table('plugin')
			       ->where('id', $plugin->id)
			       ->update([
			       		'xml_crc'      => $current_checksum,
			       		'name'         => $xml->name,
			       		'key'          => $xml->key,
			       		'homepage_url' => $xml->homepage,
			       		'download_url' => $xml->download,
			       		'issues_url'   => $xml->issues,
			       		'readme_url'   => $xml->readme,
			       		'license'      => $xml->license
			       	]);

		// Refreshing all descriptions
		Capsule::table('plugin_description')
		           ->where('plugin_id', $plugin->id)
		           ->delete(); // Deleting in-db ones ...
		foreach ($xml->description->long->children() as $lang => $string) {
			Capsule::table('plugin_description')
			           ->insert([
			           		'plugin_id' => $plugin->id,
			           		'description' => $string,
			           		'lang' => $lang
			           	]); // Inserting current ones
		}

		// Refreshing all authors
		Capsule::table('plugin_author')
		           ->where('plugin_id', $plugin->id)
		           ->delete(); // Deleting in-db ones ...
		foreach ($xml->authors->children() as $author) {
			Capsule::table('plugin_author')
			           ->insert([
			           		'plugin_id' => $plugin->id,
			           		'author' => $author,
			           	]); // Inserting current ones
		}

		echo "Imported ".$i."/".sizeof($plugins)." plugins\n";
	} else {
		echo "Passing import of plugin ".$i."/".sizeof($plugins)."\n";
	}
	$i++;
}