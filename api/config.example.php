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
	"default_number_of_models_per_page" => 15,
	"api_url" => "http://external/path/to/api",
	"oauth" => [ // Uncomment the following lines
	             // to add client ID's for each
					 // provider
		// "github" => [
		// 	"clientId" => "",
		// 	"clientSecret" => "",
		// 	"redirectUri" => ""
		// ],
		// "google" => [
		// 	"clientId" => "",
		// 	"clientSecret" => "",
		// 	"redirectUri" => ""
		// ],
		// "facebook" => [
		// 	"clientId" => "",
		// 	"clientSecret" => "",
		// 	"redirectUri" => ""
		// ],
		// "twitter" => [
		// 	"clientId" => "",
		// 	"clientSecret" => "",
		// 	"redirectUri" => ""
		// ],
	]
];