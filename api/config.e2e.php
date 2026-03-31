<?php

/**
 * E2E test configuration.
 * Reads DB credentials from the same TEST_DB_* env vars as the functional config.
 */

$config = [
    'db_settings' => [
        'driver'    => 'mysql',
        'host'      => getenv('TEST_DB_HOST') ?: 'localhost',
        'database'  => getenv('TEST_DB_NAME') ?: 'glpi_plugins_e2e',
        'username'  => getenv('TEST_DB_USER') ?: 'glpi',
        'password'  => getenv('TEST_DB_PASS') ?: 'glpi',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'    => '',
        'strict'    => false,
    ],
    'log_queries'                            => false,
    'default_number_of_models_per_page'      => 15,
    'recaptcha_secret'                       => 'test_recaptcha_secret',
    'client_url'                             => 'http://localhost:4200',
    'api_url'                                => 'http://localhost:4200/api',
    'plugin_max_consecutive_xml_fetch_fails' => 4,
    'glpi_plugin_directory_user_agent'       => 'GlpiPluginDirectory/e2e',
    'msg_alerts' => [
        'transport'      => 'mail',
        'local_admins'   => ['admin@example.com' => 'Admin'],
        'from'           => ['noreply@example.com' => 'GLPi Plugins'],
        'subject_prefix' => '[E2E]',
    ],
    'oauth' => [
        'github' => ['clientId' => 'test_id', 'clientSecret' => 'test_secret'],
    ],
];
