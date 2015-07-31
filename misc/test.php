<?php

$xml = file_get_contents('https://forge.indepnet.net/svn/room/room.xml?format=raw');
$xml = simplexml_load_string($xml);

foreach($xml->versions->children() as $version) {
	var_dump($version);
	Capsule::table('plugin_version')
	       ->insert([
	       		'plugin_id' => $plugin->id,
	       		'num' => $version->num,
	       		'compatibility' => $version->compatibility
	       	]);
}