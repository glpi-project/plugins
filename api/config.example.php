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
      "transport" => "mail", // mail, smtp, or sendmail
      // "smtp_server" => "smtp.server.com",
      // "smtp_transport_mode" => "tls",
      // "port" => 587,
      // "username" => "mailer@glpi-plugins.com",
      // "password" => "mailerprivatesmtppassword",
      "local_admins" => ["person@email.com" => 'Person Full Name'], /* Used for special alerts to a admins */
      "from" => ['mailer@glpi-plugins.com' => 'GLPi Plugins'],
      "subject_prefix" => "[GLPI PLUGINS] "
   ],
	"default_number_of_models_per_page" => 15,
   "client_url" => "http://external/path/to/glpi/client",
	"api_url" => "http://external/path/to/api",
	"oauth" => [ // Uncomment the following lines
	             // to add client ID's for each
					 // provider
		"github" => [
			"clientId" => "--your github client id here--",
			"clientSecret" => "--your github client secret here--"
		]
	]
];