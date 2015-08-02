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
$plugins = Capsule::table('plugin')
                  ->where('active', '=', true)
                  ->get();

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
	}

	// If the plugin name is not available
	// The data might be incomplete
	// this is another reason to update
	if ($plugin->name == NULL) {
		$update = true;
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
			       		'logo_url'     => $xml->logo,
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

		$langs_description = [];

		// Inserting all short and long descriptions in DB
		foreach ($xml->description->short->children() as $lang => $string) {
			$langs_description[$lang]['short'] = $string;
		}
		foreach ($xml->description->long->children() as $lang => $string) {
			$langs_description[$lang]['long'] = $string;
		}

		foreach($langs_description as $lang => $desc) {
			Capsule::table('plugin_description')
			       ->insert([
			       	    'plugin_id' => $plugin->id,
			       	    'short_description' => $desc['short'],
			       	    'long_description' => $desc['long'],
			       	    'lang' => $lang
			       	]);
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

		foreach ($xml->tags->children() as $lang => $tags) {
			foreach($tags->children() as $tag) {
				// Insert tag `if not exists` !
				$t = Capsule::table('tag')
						       ->where('lang', $lang)
				               ->where('tag', $tag)
				               ->first(['id']);

				if (!sizeof($t)) {
					$t = Capsule::table('tag')
							      ->insertGetId([
							      		'tag'  => $tag,
							      		'lang' => $lang
							        ]);
				} else {
					$t = $t->id;
				}

				// Link tag to plugin if not linked
				$notLinked = Capsule::table('plugin_tags')
				                        ->where('plugin_id', $plugin->id)
				                        ->where('tag_id', $t)
				                        ->get();
				$notLinked = (sizeof($notLinked) == 0) ? true : false;

				if ($notLinked) {
					Capsule::table('plugin_tags')
					            ->insert([
					            	'tag_id' => $t,
					            	'plugin_id' => $plugin->id
					            ]);
				}
			}
		}

		foreach($xml->versions->children() as $version) {
			Capsule::table('plugin_version')
			       ->insert([
			       		'plugin_id' => $plugin->id,
			       		'num' => $version->num,
			       		'compatibility' => $version->compatibility
			       	]);
		}

		if (isset($xml->screenshots)){
			// Refreshing all authors
			Capsule::table('plugin_screenshot')
		           ->where('plugin_id', $plugin->id)
		           ->delete(); // Deleting in-db ones ...
    		foreach((array) $xml->screenshots->screenshot as $url) {
				Capsule::table('plugin_screenshot')
				       ->insert([
				       		'plugin_id' => $plugin->id,
				       		'url' => $url
				       	]);
			}
		}

		echo "Imported/Refreshed (".$i."/".sizeof($plugins).") ".$plugin->name."\n";
	} else {
		echo "Passing import of plugin (".$i."/".sizeof($plugins).") ".$plugin->name."\n";
	}
	$i++;
}