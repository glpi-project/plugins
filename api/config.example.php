<?php

$config = [
	"db_settings" => [
		'driver'    =>  'mysql',
		'host'      =>  'localhost', 
		'database'  =>  '',
		'username'  =>  '',
		'password'  =>  '',
		'charset'   =>  'utf8',
		'collation' =>  'utf8_general_ci',
		'prefix'    =>  ''
	],
	"log_queries" => true,
	"recaptcha_secret" => '6LcnrwoTAAAAAEARsd1XMadhLthIibXeNZf4EeUZ',
	"msg_alerts" => [
		"recipients" => [
			"Walid Nouh <wnouh@teclib.com>",
			"Alexandre Delaunay <adelaunay@teclib.com>",
			"Nelson Zamith <nzamith@teclib.com>"
		],
		"subject_prefix" => "[GLPI PLUGINS : MSG] "
	],
	"default_max_number_of_resources" => 15
];