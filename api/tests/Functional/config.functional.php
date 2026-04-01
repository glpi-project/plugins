<?php

/**
 * Functional test configuration.
 *
 * Default credentials match the Docker development database.
 * Override any value via environment variables (TEST_DB_HOST, TEST_DB_NAME,
 * TEST_DB_USER, TEST_DB_PASS) for CI or alternative local setups.
 */

$config = [
    'db_settings' => [
        'driver'    => 'mysql',
        'host'      => getenv('TEST_DB_HOST') ?: 'localhost',
        'database'  => getenv('TEST_DB_NAME') ?: 'glpi_plugins_functional_test',
        'username'  => getenv('TEST_DB_USER') ?: 'glpi',
        'password'  => getenv('TEST_DB_PASS') ?: 'glpi',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'    => '',
        'strict'    => false,
    ],
    'log_queries'                          => false,
    'default_number_of_models_per_page'    => 15,
    'recaptcha_secret'                     => 'test_recaptcha_secret',
    'client_url'                           => 'http://localhost',
    'api_url'                              => 'http://localhost/api',
    'plugin_max_consecutive_xml_fetch_fails' => 4,
    'glpi_plugin_directory_user_agent'     => 'GlpiPluginDirectory/test',
    'msg_alerts' => [
        'transport'      => 'mail',
        'local_admins'   => ['admin@example.com' => 'Admin'],
        'from'           => ['noreply@example.com' => 'GLPi Plugins Test'],
        'subject_prefix' => '[TEST]',
    ],
    'oauth' => [
        'github' => ['clientId' => 'test_id', 'clientSecret' => 'test_secret'],
    ],
];
