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
	"recaptcha_secret" => '--your recaptcha key here--',
	"msg_alerts" => [
		"recipients" => [
			"Fullname <mail@domain>"
		],
		"subject_prefix" => "[GLPI PLUGINS] "
	],
	"default_number_of_models_per_page" => 15
];