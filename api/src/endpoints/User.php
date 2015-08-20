<?php
/**
 * User
 *
 * This REST module hooks on
 * following URLs :
 *
 * 
 */


use \API\Core\Tool;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Registers new user
 */
$register = function() use ($app) {
   $body = Tool::getBody();

};

$login = function() use ($app) {

};

// HTTP REST Map
// $app->post('/user', $register);
// $app->post('/user/login', $login);