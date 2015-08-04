<?php

$db_settings = [
	'driver'    =>  'mysql',
	'host'      =>  '',
	'database'  =>  '',
	'username'  =>  '',
	'password'  =>  '',
	'charset'   =>  'utf8',
	'collation' =>  'utf8_general_ci',
	'prefix'    =>  ''
];

$log_queries = false;

$recaptcha_secret = '6LcnrwoTAAAAAEARsd1XMadhLthIibXeNZf4EeUZ';

$msg_alerts = [
	"recipients" => [
		"Walid Nouh <wnouh@teclib.com>",
		"Alexandre Delaunay <adelaunay@teclib.com>",
		"Nelson Zamith <nzamith@teclib.com>"
	],
	"subject_prefix" => "[GLPI PLUGINS : MSG] "
];