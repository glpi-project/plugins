<?php
/**
 * update.php
 *
 * This script should be started a crontab to
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
foreach ($plugins as $plugin) {
	// Fetching file via HTTP
	$xml = file_get_contents($plugin->xml_url);
	// Parsing... thanks to SimpleXML!
	$xml = simplexml_load_string($xml);
	var_dump($xml);

	// The useful logic here would be :
	//  - Check if there are missing mandatory
	//    fields
	//  OR
	//  - Check if CRC has changed
	// In both cases
	//  => Refresh all the infos


	// ----------------------------------
	// Breaking at first element for now
	// for the sake of writing this script
	break;
}